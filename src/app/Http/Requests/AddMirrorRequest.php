<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddMirrorRequest extends FormRequest
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
            "url" => [
                "required",
                "string",
                "regex:/^https?:\/\/(www\.)?([-a-zA-Z0-9@:%._\+~#=]{1,256}\.)+[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/=]*)$/",
            ],
        ];
    }
}
