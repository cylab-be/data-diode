<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rule;

class EditRuleRequest extends FormRequest
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
        if (env("DIODE_IN", true)) {
            return [
            "input_port" => "required|integer|between:1,65535|unique:rule,input_port," . $this->rule->id,
            "output_port" => "required|integer|between:1,65535|unique:rule,output_port," . $this->rule->id
            ];
        } else {
            return [
            "input_port" => "required|integer|between:1,65535|unique:rule,input_port," . $this->rule->id,
            "output_port" => "required|integer|between:1,65535|unique:rule,output_port," . $this->rule->id,
            "destination" => "required|ip"
            ];
        }
    }
}
