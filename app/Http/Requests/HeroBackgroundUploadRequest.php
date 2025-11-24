<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HeroBackgroundUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'max:5'],
            'images.*' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:4096',
                'dimensions:width=1920,height>=800,height<=810',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'Please select at least one image to upload.',
            'images.array' => 'Images must be provided as an array.',
            'images.max' => 'You can upload up to 5 images at a time.',
            'images.*.required' => 'Each file is required.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Allowed image types: jpeg, png, jpg, webp.',
            'images.*.max' => 'Each image must be 4MB or smaller.',
            'images.*.dimensions' => 'Images must be 1920px wide and 800–810px tall.',
        ];
    }
}
