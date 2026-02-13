<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'login' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_-]+$/i', 'unique:users,login'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }
}
