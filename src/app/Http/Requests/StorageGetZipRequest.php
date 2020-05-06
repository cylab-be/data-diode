<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\GoodFolderFileNameRule;

class StorageGetZipRequest extends FormRequest
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
            "time" => [
                "required",
                "integer",                
            ],
            "name" => [
                "required",
                "string",
                new GoodFolderFileNameRule,
            ],
        ];
    }
}
