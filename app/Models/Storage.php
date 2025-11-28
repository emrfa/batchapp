<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    protected $table = 'storage';
    protected $primaryKey = 'storageCode';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}
