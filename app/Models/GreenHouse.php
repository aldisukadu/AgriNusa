<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GreenHouse extends Model
{
    protected $table = 'green_houses';

    protected $fillable = ['nama', 'lokasi', 'keterangan'];

    public function lahans(): HasMany
    {
        return $this->hasMany(Lahan::class);
    }
}
