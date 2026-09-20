<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PengumumanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $tipe = $this->input('tipe');

        $rules = [
            'judul' => ['required', 'string', 'max:255'],
            'tipe' => ['required', 'in:teks,gambar,keduanya'],
            'isi' => [
                $tipe === 'gambar' ? 'nullable' : 'required',
                'nullable',
                'string',
            ],
            'gambar' => [
                ($tipe === 'gambar' && ! $isUpdate) ? 'required' : 'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp,gif',
                'max:5120',
            ],
            'is_aktif' => ['nullable', 'boolean'],
            'target_role' => ['nullable', 'string', 'max:50'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul pengumuman wajib diisi.',
            'tipe.required' => 'Tipe pengumuman wajib dipilih.',
            'tipe.in' => 'Tipe pengumuman harus berupa teks, gambar, atau keduanya.',
            'isi.required' => 'Isi teks pengumuman wajib diisi untuk tipe teks atau keduanya.',
            'gambar.required' => 'File gambar wajib diunggah untuk tipe pengumuman gambar.',
            'gambar.image' => 'File yang diunggah harus berupa gambar.',
            'gambar.mimes' => 'Format gambar harus berupa jpeg, png, jpg, webp, atau gif.',
            'gambar.max' => 'Ukuran gambar maksimal adalah 5MB.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ];
    }
}
