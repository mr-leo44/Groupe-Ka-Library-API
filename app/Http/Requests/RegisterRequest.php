<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $name User's full name
 * @property string $email User's email address
 * @property string $password Strong password (min 8 chars, mixed case, numbers, symbols)
 * @property string $password_confirmation Password confirmation
 * @property int|null $city_id User's city ID (optional)
 * @property int|null $clan_id User's clan ID (optional)
 * @property string|null $device_name Device name for token (optional)
 */
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            'city_id' => 'nullable|exists:cities,id',
            'clan_id' => 'nullable|exists:clans,id',
            'device_name' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function messages(): array
    {
        return [
            'password.uncompromised' => 'Ce mot de passe est apparu dans une fuite de données. Veuillez en choisir un autre pour votre sécurité.',
            'password.mixed' => 'Le mot de passe doit contenir au moins une majuscule et une minuscule.',
            'password.letters' => 'Le mot de passe doit contenir au moins une lettre.',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'password.symbols' => 'Le mot de passe doit contenir au moins un symbole spécial.',
        ];
    }
}
