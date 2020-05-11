<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PortNotUsedRule;

class CreateUploaderRequest extends FormRequest
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
            "name" => [
                "required",
                "string",
                'regex:/' . env("UPLOADER_NAME_REGEX", "^[a-zA-Z0-9_]+$") . '/',
                "min:3",
                "max:255",
                "unique:uploaders",
            ],
            "port" => [
                "required",
                "integer",
                "unique:uploaders",
                "between:1025,65535",
                new PortNotUsedRule,
            ],
        ];
    }
}
