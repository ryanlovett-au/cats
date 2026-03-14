<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = ['name', 'path', 'sort_order'];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('name');
    }
}
