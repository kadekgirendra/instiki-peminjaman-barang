<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUserRequest extends FormRequest
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
    // Halaman ini sekarang cuma dipakai untuk edit (reset password/data user),
    // jadi $userId selalu ada. Role tidak divalidasi di sini karena field
    // role sudah tidak ada di form — semua user yang dikelola di halaman
    // ini otomatis berrole 'user'.
    $userId = $this->route('user')?->id;

    return [
      'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($userId)],
      'name'     => ['required', 'string', 'max:255'],
      'nim_nidn' => ['required', 'string', 'max:20', Rule::unique('users', 'nim_nidn')->ignore($userId)],
      'password' => ['nullable', 'string', 'min:8', 'confirmed'],
    ];
  }

  public function messages(): array
  {
    return [
      'username.unique'    => 'Username sudah dipakai, silakan pilih yang lain.',
      'nim_nidn.unique'    => 'NIM/NIDN ini sudah terdaftar.',
      'password.confirmed' => 'Konfirmasi password tidak sesuai.',
    ];
  }
}
