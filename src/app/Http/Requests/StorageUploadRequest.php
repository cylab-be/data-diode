<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorageUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "input_file" => [
                "required",
                "file",
                "max:1024 * 1024", // 1GB (1024 * 1024 * 1KB)
            ],
            "input_file_full_path" => [
                "required",
                "string",
            ],
        ];
    }
}
