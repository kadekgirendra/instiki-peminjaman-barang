<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50', 'unique:users,username', 'alpha_dash'],
            'name' => ['required', 'string', 'max:255'],
            'nim_nidn' => ['required', 'string', 'max:20', 'unique:users,nim_nidn'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Nama user sudah dipakai, silakan pilih yang lain.',
            'nim_nidn.unique' => 'NIM ini sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ];
    }
}
