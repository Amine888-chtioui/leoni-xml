<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportXmlRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'xml_file' => ['required', 'file', 'mimes:xml', 'max:10240'], // 10MB max
        ];
    }
    
    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'xml_file.required' => 'Please select an XML file to upload.',
            'xml_file.file' => 'The upload must be a valid file.',
            'xml_file.mimes' => 'The file must be an XML document.',
            'xml_file.max' => 'The file size cannot exceed 10MB.',
        ];
    }
}