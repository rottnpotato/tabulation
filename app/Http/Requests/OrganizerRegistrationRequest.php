<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizerRegistrationRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'unique:users', 'alpha_dash'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your full name.',
            'name.max' => 'Your name must not exceed 255 characters.',
            'email.required' => 'An email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered. Please use a different email or login.',
            'username.required' => 'Please choose a username.',
            'username.min' => 'Your username must be at least 3 characters.',
            'username.max' => 'Your username must not exceed 30 characters.',
            'username.unique' => 'This username is already taken. Please choose another.',
            'username.alpha_dash' => 'Your username may only contain letters, numbers, dashes, and underscores.',
            'password.required' => 'A password is required.',
            'password.min' => 'Your password must be at least 8 characters for security.',
            'password.confirmed' => 'The password confirmation does not match. Please try again.',
        ];
    }
}
