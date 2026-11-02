<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JasaPengiriman;
use App\Models\TarifPengiriman;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JasaController extends Controller
{
    /**
     * Pulau tujuan sesuai data referensi provinsi.
     */
    public const PULAU = ['Jawa', 'Sumatera', 'Kalimantan', 'Sulawesi', 'Bali & Nusa Tenggara', 'Maluku & Papua'];

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $jasa = JasaPengiriman::query()
            ->withCount('tarif')
            ->when($q !== '', fn ($sql) => $sql->where('nama_jasa', 'like', '%'.$q.'%'))
            ->orderBy('nama_jasa')
            ->paginate(10)
            ->withQueryString();

        return view('admin.jasa.index', compact('jasa', 'q'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_jasa' => ['required', 'string', 'max:50'],
        ], [
            'nama_jasa.required' => 'Nama jasa pengiriman wajib diisi.',
        ]);

        $data['id_penjual'] = $request->user()->id_user;
        JasaPengiriman::create($data);

        return back()->with('success', 'Jasa pengiriman berhasil ditambahkan.');
    }

    public function update(Request $request, JasaPengiriman $jasa): RedirectResponse
    {
        $data = $request->validate([
            'nama_jasa' => ['required', 'string', 'max:50'],
        ], [
            'nama_jasa.required' => 'Nama jasa pengiriman wajib diisi.',
        ]);

        $jasa->update($data);

        return back()->with('success', 'Jasa pengiriman berhasil diperbarui.');
    }

    public function destroy(JasaPengiriman $jasa): RedirectResponse
    {
        if ($jasa->pesanan()->exists()) {
            return back()->withErrors(['jasa' => 'Jasa tidak dapat dihapus karena sudah dipakai pada pesanan.']);
        }

        $jasa->delete();

        return back()->with('success', 'Jasa pengiriman berhasil dihapus.');
    }

    public function tarif(JasaPengiriman $jasa): View
    {
        $tarif = $jasa->tarif->keyBy('pulau_tujuan');

        return view('admin.jasa.tarif', [
            'jasa' => $jasa,
            'pulau' => self::PULAU,
            'tarif' => $tarif,
        ]);
    }

    public function simpanTarif(Request $request, JasaPengiriman $jasa): RedirectResponse
    {
        $data = $request->validate([
            'tarif' => ['required', 'array'],
            'tarif.*' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
        ], [
            'tarif.*.numeric' => 'Tarif harus berupa angka.',
            'tarif.*.min' => 'Tarif tidak boleh negatif.',
        ]);

        foreach (self::PULAU as $pulau) {
            $nilai = $data['tarif'][$pulau] ?? null;

            if ($nilai === null || $nilai === '') {
                TarifPengiriman::where('id_jasa', $jasa->id_jasa)
                    ->where('pulau_tujuan', $pulau)
                    ->delete();

                continue;
            }

            TarifPengiriman::updateOrCreate(
                ['id_jasa' => $jasa->id_jasa, 'pulau_tujuan' => $pulau],
                ['tarif' => $nilai],
            );
        }

        return redirect()->route('admin.jasa.index')
            ->with('success', 'Tarif pengiriman '.$jasa->nama_jasa.' berhasil disimpan.');
    }
}
