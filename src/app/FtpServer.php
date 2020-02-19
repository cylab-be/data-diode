<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * The class defines the data of the file server storable in the database
 */
class FileServer extends Model
{
    /**
     * The name of the table
     * @var string
     */
    protected $table = "file_servers";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ["pid", "name"];
}
