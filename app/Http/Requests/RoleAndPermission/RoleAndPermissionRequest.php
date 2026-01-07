<?php

namespace App\Http\Requests\RoleAndPermission;

use Illuminate\Foundation\Http\FormRequest;

class RoleAndPermissionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'roles' => 'required|array',
            'roles.*.name' => 'required|string|distinct',
            'roles.*.permissions' => 'present|array',
            'roles.*.permissions.*' => 'string|exists:permissions,name',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $roles = collect($this->input('roles'));
            if (!$roles->contains('name', 'super-admin')) {
                $validator->errors()->add('roles', 'Super admin tidak boleh dihapus.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'roles.required' => 'Daftar role wajib diisi.',
            'roles.array' => 'Format role harus berupa array.',
            'roles.*.name.required' => 'Nama role tidak boleh kosong.',
            'roles.*.name.string' => 'Nama role harus berupa teks.',
            'roles.*.name.distinct' => 'Nama role tidak boleh duplikat.',
            'roles.*.permissions.present' => 'Daftar permission untuk role wajib ada (bisa kosong).',
            'roles.*.permissions.array' => 'Format permission untuk role harus berupa array.',
            'roles.*.permissions.*.string' => 'Permission untuk role harus berupa teks.',
            'roles.*.permissions.*.exists' => 'Permission tidak ditemukan di database.',
        ];
    }
}
