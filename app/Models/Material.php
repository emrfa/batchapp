<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'material';
    protected $primaryKey = 'materialCode';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}
