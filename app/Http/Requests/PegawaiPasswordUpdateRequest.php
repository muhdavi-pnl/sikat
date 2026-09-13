<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class PegawaiPasswordUpdateRequest extends FormRequest
{
    protected $errorBag = 'updatePassword';

    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}

