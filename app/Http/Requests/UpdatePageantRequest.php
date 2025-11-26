<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageantRequest extends FormRequest
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
            'description' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'required_judges' => ['nullable', 'integer', 'min:0', 'max:20'],
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
            'name.required' => 'The pageant name is required.',
            'name.max' => 'The pageant name must not exceed 255 characters.',
            'description.max' => 'The description must not exceed 5000 characters.',
            'start_date.date' => 'Please provide a valid start date.',
            'start_date.after_or_equal' => 'The start date must be today or a future date.',
            'end_date.date' => 'Please provide a valid end date.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
            'venue.max' => 'The venue must not exceed 255 characters.',
            'location.max' => 'The location must not exceed 255 characters.',
            'cover_image.image' => 'The cover image must be a valid image file.',
            'cover_image.mimes' => 'The cover image must be in JPEG, PNG, JPG, or GIF format.',
            'cover_image.max' => 'The cover image must not exceed 2MB.',
            'logo.image' => 'The logo must be a valid image file.',
            'logo.mimes' => 'The logo must be in JPEG, PNG, JPG, or GIF format.',
            'logo.max' => 'The logo must not exceed 2MB.',
            'required_judges.min' => 'The required judges count cannot be negative.',
            'required_judges.max' => 'The required judges count cannot exceed 20.',
        ];
    }
}
