<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
