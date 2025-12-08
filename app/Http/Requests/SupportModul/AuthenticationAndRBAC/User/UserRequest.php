<?php

namespace App\Http\Requests\SupportModul\AuthenticationAndRBAC\User;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->route('id')) {
            $user = \App\Models\Support\Modul\AuthenticationAndRBAC\User::find($this->route('id'));
            if (!$user) {
                abort(404, 'User tidak ditemukan');
            }
        }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->route('id'),
            'role' => [
                'required',
                'exists:roles,name',
                function ($attribute, $value, $fail) {
                    if ($value === 'super-admin') {
                        $fail('Role super-admin tidak dapat ditetapkan melalui API.');
                    }
                },
            ],
        ];

        if ($this->isMethod('post')) {
            $rules['password'] = [
                'required',
                'confirmed',
                \Illuminate\Validation\Rules\Password::min(8)
                    ->max(20)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ];
        } else {
            $rules['password'] = [
                'nullable',
                'confirmed',
                \Illuminate\Validation\Rules\Password::min(8)
                    ->max(20)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'role.required' => 'Role wajib dipilih.',
            'role.exists' => 'Role yang dipilih tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.max' => 'Password maksimal 20 karakter.',
            'password.mixed' => 'Password harus mengandung huruf besar dan kecil.',
            'password.letters' => 'Password harus mengandung huruf.',
            'password.numbers' => 'Password harus mengandung angka.',
            'password.symbols' => 'Password harus mengandung simbol.',
            'password.uncompromised' => 'Password telah bocor dalam kebocoran data. Silakan pilih password yang berbeda.',
        ];
    }
}
