<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PortNotUsedRule;
use App\Uploader;

/**
 * Rule used when the user stops or restarts a channel.
 */
class UploaderPortRequest extends FormRequest
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
