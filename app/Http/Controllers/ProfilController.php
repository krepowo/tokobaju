<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    /**
     * Halaman profil pelanggan (F-02): data diri + alamat pengiriman utama.
     */
    public function show(Request $request): View
    {
        return view('profil.index', [
            'user' => $request->user(),
            'mode' => $request->boolean('edit') ? 'edit' : 'lihat',
            'jumlahPesanan' => $request->user()->pesanan()->count(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'nama' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id_user, 'id_user')],
            'no_telepon' => ['nullable', 'string', 'digits_between:10,15', Rule::unique('users', 'no_telepon')->ignore($user->id_user, 'id_user')],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'kota_kabupaten' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kelurahan_desa' => ['nullable', 'string', 'max:100'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'detail_alamat' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'min:8', 'max:72'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nama.min' => 'Nama lengkap minimal 3 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah dipakai akun lain.',
            'no_telepon.digits_between' => 'Nomor telepon harus terdiri dari 10 sampai 15 digit.',
            'no_telepon.unique' => 'Nomor telepon sudah dipakai akun lain.',
            'kode_pos.max' => 'Kode pos maksimal 10 karakter.',
            'detail_alamat.max' => 'Detail alamat maksimal 500 karakter.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('profil.index')
            ->with('success', 'Data profil berhasil diperbarui.');
    }
}
