<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Rule extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "input_port" => $this->input_port,
            "output_port" => $this->output_port,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
