<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveManagedUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'admin' || ($this->isMethod('post') && $this->user()->role === 'operator');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:200', Rule::unique('users')->ignore($this->route('user'))],
            'role' => ['required', Rule::in($this->user()->role === 'admin' ? ['customer', 'operator', 'manager', 'admin', 'auditor'] : ['customer'])],
            'active' => ['required', 'boolean'],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'string', 'min:8', 'max:100', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return ['email.unique' => 'Já existe uma conta com este e-mail.', 'role.in' => 'Você não pode atribuir este perfil.', 'password.min' => 'Use pelo menos 8 caracteres.', 'password.confirmed' => 'A confirmação da senha não confere.'];
    }
}
