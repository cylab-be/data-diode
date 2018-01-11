<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRuleRequest extends FormRequest
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
            "input_port" => "required|integer|unique:rule|between:1,65535",
            "output_port" => "required|integer|unique:rule|between:1,65535",
            "destination" => "required|ip"
        ];
    }
}
