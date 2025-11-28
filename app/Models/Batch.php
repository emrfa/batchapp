<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Batch extends Model
{
    protected $table = 'batch';
    protected $primaryKey = 'idBatch';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'batchTime' => 'datetime',
        'mixTime' => 'float',
        'unloadTime' => 'float',
    ];

    public function details()
{
    return $this->hasMany(BatchDetail::class, 'idBatch', 'idBatch');
}

    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'recipeCode', 'recipeCode');
    }
    
}
