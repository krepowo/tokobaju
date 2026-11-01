<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use App\Models\UkuranStok;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KeranjangController extends Controller
{
    /**
     * Menampilkan isi keranjang belanja pengguna saat ini.
     */
    public function index(): View
    {
        $user = auth()->user();

        $items = Keranjang::with(['varianUkuran.produk.kategori'])
            ->where('id_user', $user->id_user)
            ->latest()
            ->get();

        // Hitung total harga item yang dipilih dan stok mencukupi
        $totalHarga = 0;
        $totalItemDipilih = 0;
        $semuaDipilih = $items->isNotEmpty() && $items->every(fn ($item) => $item->dipilih);

        foreach ($items as $item) {
            $stokTersedia = (int) ($item->varianUkuran?->stok ?? 0);
            $harga = (float) ($item->varianUkuran?->produk?->harga ?? 0);
            $subtotal = $harga * $item->jumlah;

            $item->subtotal = $subtotal;
            $item->stok_cukup = $stokTersedia >= $item->jumlah;

            if ($item->dipilih) {
                $totalItemDipilih++;
                $totalHarga += $subtotal;
            }
        }

        return view('keranjang.index', compact('items', 'totalHarga', 'totalItemDipilih', 'semuaDipilih'));
    }

    /**
     * Menambahkan produk ke keranjang belanja.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'id_ukuran' => ['required', 'exists:ukuran_stok,id_ukuran'],
            'jumlah' => ['nullable', 'integer', 'min:1'],
        ], [
            'id_ukuran.required' => 'Varian ukuran dan warna wajib dipilih.',
            'id_ukuran.exists' => 'Varian yang dipilih tidak valid.',
            'jumlah.min' => 'Jumlah minimal adalah 1.',
        ]);

        $user = auth()->user();
        $variant = UkuranStok::with('produk')->findOrFail($request->id_ukuran);
        $jumlahDiminta = (int) ($request->input('jumlah', 1));

        if ($variant->stok <= 0) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Stok produk ini sedang habis.'], 422);
            }
            return back()->with('error', 'Maaf, stok varian ini sedang habis.');
        }

        // Cari apakah varian ini sudah ada di keranjang
        $existing = Keranjang::where('id_user', $user->id_user)
            ->where('id_ukuran', $variant->id_ukuran)
            ->first();

        if ($existing) {
            $totalBaru = $existing->jumlah + $jumlahDiminta;
            if ($totalBaru > $variant->stok) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok tidak cukup. Sisa stok tersedia: {$variant->stok} pcs.",
                    ], 422);
                }
                return back()->with('error', "Stok tidak cukup. Sisa stok tersedia: {$variant->stok} pcs.");
            }
            $existing->jumlah = $totalBaru;
            $existing->dipilih = true;
            $existing->save();
        } else {
            if ($jumlahDiminta > $variant->stok) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok tidak cukup. Sisa stok tersedia: {$variant->stok} pcs.",
                    ], 422);
                }
                return back()->with('error', "Stok tidak cukup. Sisa stok tersedia: {$variant->stok} pcs.");
            }

            Keranjang::create([
                'id_user' => $user->id_user,
                'id_ukuran' => $variant->id_ukuran,
                'jumlah' => $jumlahDiminta,
                'dipilih' => true,
            ]);
        }

        // Jika tombol "Beli Sekarang" diklik
        if ($request->boolean('beli_langsung')) {
            return redirect()->route('keranjang.index');
        }

        if ($request->wantsJson()) {
            $cartCount = Keranjang::where('id_user', $user->id_user)->count();
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke keranjang!',
                'cart_count' => $cartCount,
            ]);
        }

        return redirect()->route('keranjang.index')->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    /**
     * Memperbarui jumlah item atau status pilihan di keranjang.
     */
    public function update(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $item = Keranjang::with('varianUkuran.produk')
            ->where('id_user', $user->id_user)
            ->where('id_keranjang', $id)
            ->firstOrFail();

        // 1. Update Jumlah
        if ($request->has('jumlah')) {
            $jumlah = max(1, (int) $request->jumlah);
            $stokTersedia = (int) $item->varianUkuran->stok;

            $item->jumlah = $jumlah;
            $item->save();

            $stokCukup = $stokTersedia >= $jumlah;

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'stok_cukup' => $stokCukup,
                    'message' => $stokCukup ? null : 'Stok tidak cukup',
                    'subtotal' => 'Rp ' . number_format($item->jumlah * $item->varianUkuran->produk->harga, 0, ',', '.'),
                    'totals' => $this->calculateTotals($user->id_user),
                ]);
            }
        }

        // 2. Update Status Dipilih (Checkbox)
        if ($request->has('dipilih')) {
            $item->dipilih = $request->boolean('dipilih');
            $item->save();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'totals' => $this->calculateTotals($user->id_user),
                ]);
            }
        }

        return back();
    }

    /**
     * Pilih / Batal Pilih semua item di keranjang.
     */
    public function toggleAll(Request $request): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $dipilih = $request->boolean('dipilih');

        Keranjang::where('id_user', $user->id_user)->update(['dipilih' => $dipilih]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'totals' => $this->calculateTotals($user->id_user),
            ]);
        }

        return back();
    }

    /**
     * Menghapus item dari keranjang belanja.
     */
    public function destroy(string $id): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        $item = Keranjang::where('id_user', $user->id_user)->where('id_keranjang', $id)->firstOrFail();
        $item->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus dari keranjang.',
                'totals' => $this->calculateTotals($user->id_user),
            ]);
        }

        return redirect()->route('keranjang.index')->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    /**
     * Helper menghitung total harga dan jumlah terpilih untuk AJAX response.
     */
    private function calculateTotals(string $userId): array
    {
        $items = Keranjang::with('varianUkuran.produk')->where('id_user', $userId)->get();
        $total = 0;
        $selectedCount = 0;

        foreach ($items as $item) {
            if ($item->dipilih) {
                $selectedCount++;
                $total += $item->jumlah * ($item->varianUkuran?->produk?->harga ?? 0);
            }
        }

        return [
            'total_formatted' => 'Rp ' . number_format($total, 0, ',', '.'),
            'total_raw' => $total,
            'selected_count' => $selectedCount,
            'total_items' => $items->count(),
            'all_selected' => $items->isNotEmpty() && $items->every(fn ($i) => $i->dipilih),
        ];
    }
}
