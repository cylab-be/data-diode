<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class UploaderResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "state" => $this->state,
            "port" => $this->port,
            "pipport" => $this->pipport,
            "aptport" => $this->aptport,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
