<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mixer extends Model
{
    protected $table = 'mixer';
    protected $primaryKey = 'mixerCode';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}
