<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Uploader extends Model
{
    /**
     * The name of the table
     * @var string
     */
    protected $table = "uploaders";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ["name", "state", "port"];
}
