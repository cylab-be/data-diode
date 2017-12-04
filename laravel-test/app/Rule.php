<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $table = "rule";
    protected $fillable = ["input_port", "output_port", "destination"];
}
