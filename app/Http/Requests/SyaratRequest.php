<?php

namespace App\Http\Requests;

use App\Models\Syarat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyaratRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        /** @var Syarat|null $syarat */
        $syarat = $this->route('syarat');
        $syaratId = $syarat ? $syarat->id : null;
        $profileCodes = array_keys((array) config('intelligence.layanan_profile_requirement_codes', []));

        return [
            'syarat' => ['required', 'string', 'max:150'],
            'mapping_source' => ['required', Rule::in(['manual', 'document', 'profile'])],
            'dokumen_id' => ['nullable', 'integer', 'exists:dokumens,id'],
            'kode_syarat' => [
                'nullable',
                'string',
                'max:15',
                Rule::in($profileCodes),
                Rule::unique('syarats', 'kode_syarat')->ignore($syaratId),
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $mappingSource = (string) $this->input('mapping_source');

            if ($mappingSource === 'document' && !$this->filled('dokumen_id')) {
                $validator->errors()->add('dokumen_id', 'Dokumen wajib dipilih untuk mapping dokumen.');
            }

            if ($mappingSource === 'profile' && !$this->filled('kode_syarat')) {
                $validator->errors()->add('kode_syarat', 'Kode profil wajib dipilih untuk mapping profil.');
            }
        });
    }

    public function attributes()
    {
        return [
            'syarat' => 'syarat',
            'mapping_source' => 'sumber mapping',
            'dokumen_id' => 'dokumen',
            'kode_syarat' => 'kode profil',
        ];
    }
}

