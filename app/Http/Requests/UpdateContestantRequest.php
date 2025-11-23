<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContestantRequest extends FormRequest
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
        $pageantId = $this->route('pageantId');
        $contestantId = $this->route('contestantId');

        return [
            'name' => ['required', 'string', 'max:255'],
            'number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('contestants')->where(function ($query) use ($pageantId) {
                    return $query->where('pageant_id', $pageantId)
                        ->where('gender', $this->input('gender'))
                        ->where('is_pair', false);
                })->ignore($contestantId),
            ],
            'gender' => ['required', 'string', Rule::in(['male', 'female'])],
            'origin' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:1', 'max:150'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'metadata' => ['nullable', 'array'],
            'active' => ['nullable', 'boolean'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer', 'exists:contestant_images,id'],
            'primary_image_id' => ['nullable', 'integer', 'exists:contestant_images,id'],
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
            'name.required' => 'The contestant name is required.',
            'name.max' => 'The contestant name must not exceed 255 characters.',
            'number.required' => 'The contestant number is required.',
            'number.integer' => 'The contestant number must be a valid integer.',
            'number.min' => 'The contestant number must be at least 1.',
            'number.unique' => 'This contestant number is already taken for the selected gender in this pageant.',
            'gender.required' => 'Please select a gender for the contestant.',
            'gender.in' => 'The selected gender is invalid.',
            'age.min' => 'The age must be at least 1.',
            'age.max' => 'The age must not exceed 150.',
            'bio.max' => 'The bio must not exceed 5000 characters.',
            'images.max' => 'You can upload a maximum of 10 images.',
            'images.*.mimes' => 'Images must be in JPEG, PNG, JPG, or WebP format.',
            'images.*.max' => 'Each image must not exceed 10MB.',
            'remove_image_ids.*.exists' => 'One or more image IDs are invalid.',
            'primary_image_id.exists' => 'The selected primary image is invalid.',
        ];
    }
}
