<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * The class defines a rule storable in the database
 */
class Rule extends Model
{
    /**
     * The name of the table
     * @var string
     */
    protected $table = "rule";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ["input_port", "output_port", "destination"];
}
