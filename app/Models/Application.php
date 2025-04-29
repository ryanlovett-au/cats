<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('name');
    }
}
