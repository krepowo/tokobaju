<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JasaPengiriman;
use App\Models\TarifPengiriman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Kelola jasa pengiriman + tarif per pulau (mockup Figma 3.2.2 dan 3.2.3).
 * Tanpa perubahan skema: daftar pulau mengikuti nilai `provinsi.pulau`.
 */
class JasaPengirimanController extends Controller
{
    /** Pulau resmi yang wajib punya tarif untuk setiap jasa. */
    public const DAFTAR_PULAU = [
        'Jawa',
        'Sumatera',
        'Kalimantan',
        'Sulawesi',
        'Bali & Nusa Tenggara',
        'Maluku & Papua',
    ];

    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('cari', ''));

        $daftarJasa = JasaPengiriman::query()
            ->with(['tarif' => fn ($query) => $query->orderBy('pulau_tujuan')])
            ->withCount('tarif')
            ->when($cari !== '', fn ($query) => $query->where('nama_jasa', 'like', "%{$cari}%"))
            ->orderBy('nama_jasa')
            ->paginate(10)
            ->withQueryString();

        return view('admin.jasa.index', [
            'daftarJasa' => $daftarJasa,
            'cari' => $cari,
            'daftarPulau' => self::DAFTAR_PULAU,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_jasa' => ['required', 'string', 'max:50', 'unique:jasa_pengiriman,nama_jasa'],
            'tarif' => ['required', 'array'],
            'tarif.*' => ['nullable', 'numeric', 'min:1'],
        ], [
            'nama_jasa.required' => 'Nama jasa pengiriman wajib diisi.',
            'nama_jasa.max' => 'Nama jasa maksimal 50 karakter.',
            'nama_jasa.unique' => 'Jasa pengiriman tersebut sudah ada.',
            'tarif.required' => 'Isi tarif minimal satu pulau.',
            'tarif.*.numeric' => 'Tarif harus berupa angka.',
            'tarif.*.min' => 'Tarif minimal Rp 1.',
        ]);

        $terisi = self::tarifTerisi($data['tarif'] ?? []);

        if ($terisi === []) {
            return back()->withInput()->withErrors(['tarif' => 'Isi tarif minimal satu pulau.']);
        }

        DB::transaction(function () use ($data, $terisi) {
            $jasa = JasaPengiriman::create([
                'id_penjual' => auth()->id(),
                'nama_jasa' => $data['nama_jasa'],
            ]);

            foreach ($terisi as $pulau => $nilai) {
                TarifPengiriman::create([
                    'id_jasa' => $jasa->id_jasa,
                    'pulau_tujuan' => $pulau,
                    'tarif' => $nilai,
                ]);
            }
        });

        return redirect()
            ->route('admin.jasa.index')
            ->with('success', 'Jasa "'.$data['nama_jasa'].'" ditambahkan dengan '.count($terisi).' pulau terdaftar.');
    }

    public function update(Request $request, JasaPengiriman $jasa): RedirectResponse
    {
        $data = $request->validate([
            'nama_jasa' => [
                'required', 'string', 'max:50',
                Rule::unique('jasa_pengiriman', 'nama_jasa')->ignore($jasa->id_jasa, 'id_jasa'),
            ],
        ], [
            'nama_jasa.required' => 'Nama jasa pengiriman wajib diisi.',
            'nama_jasa.unique' => 'Jasa pengiriman tersebut sudah ada.',
        ]);

        $jasa->update($data);

        return redirect()
            ->route('admin.jasa.index')
            ->with('success', 'Jasa pengiriman berhasil diubah menjadi "'.$data['nama_jasa'].'".');
    }

    public function destroy(JasaPengiriman $jasa): RedirectResponse
    {
        $nama = $jasa->nama_jasa;

        // FK pesanan.id_jasa bersifat RESTRICT: jasa yang sudah dipakai tidak bisa dihapus.
        $dipakai = DB::table('pesanan')->where('id_jasa', $jasa->id_jasa)->count();

        if ($dipakai > 0) {
            return redirect()
                ->route('admin.jasa.index')
                ->with('error', 'Jasa "'.$nama.'" tidak dapat dihapus karena dipakai oleh '.$dipakai.' pesanan.');
        }

        $jumlahTarif = $jasa->tarif()->count();

        // Tarif ikut terhapus lewat cascade.
        $jasa->delete();

        return redirect()
            ->route('admin.jasa.index')
            ->with('success', 'Jasa "'.$nama.'" dihapus beserta '.$jumlahTarif.' tarifnya.');
    }

    public function updateTarif(Request $request, JasaPengiriman $jasa): RedirectResponse
    {
        $data = $request->validate([
            'tarif' => ['required', 'array'],
            'tarif.*' => ['nullable', 'numeric', 'min:1'],
        ], [
            'tarif.required' => 'Isi tarif minimal satu pulau.',
            'tarif.*.numeric' => 'Tarif harus berupa angka.',
            'tarif.*.min' => 'Tarif minimal Rp 1.',
        ]);

        $terisi = self::tarifTerisi($data['tarif'] ?? []);

        if ($terisi === []) {
            return back()->withInput()->withErrors(['tarif' => 'Minimal satu pulau harus punya tarif.']);
        }

        DB::transaction(function () use ($jasa, $terisi) {
            foreach (self::DAFTAR_PULAU as $pulau) {
                if (isset($terisi[$pulau])) {
                    TarifPengiriman::updateOrCreate(
                        ['id_jasa' => $jasa->id_jasa, 'pulau_tujuan' => $pulau],
                        ['tarif' => $terisi[$pulau]],
                    );
                } else {
                    // Pulau yang dikosongkan berarti tidak lagi dilayani.
                    TarifPengiriman::where('id_jasa', $jasa->id_jasa)
                        ->where('pulau_tujuan', $pulau)
                        ->delete();
                }
            }

            // Bersihkan tarif pulau di luar daftar resmi (sisa data lama).
            TarifPengiriman::where('id_jasa', $jasa->id_jasa)
                ->whereNotIn('pulau_tujuan', self::DAFTAR_PULAU)
                ->delete();
        });

        return redirect()
            ->route('admin.jasa.index')
            ->with('success', 'Tarif "'.$jasa->nama_jasa.'" disimpan untuk '.count($terisi).' pulau.');
    }

    /**
     * Ambil hanya pulau yang tarifnya diisi.
     *
     * @param  array<string, mixed>  $tarif
     * @return array<string, float>
     */
    private static function tarifTerisi(array $tarif): array
    {
        $hasil = [];

        foreach (self::DAFTAR_PULAU as $pulau) {
            $nilai = $tarif[$pulau] ?? null;

            if ($nilai === null || $nilai === '') {
                continue;
            }

            if ((float) $nilai <= 0) {
                continue;
            }

            $hasil[$pulau] = (float) $nilai;
        }

        return $hasil;
    }
}
