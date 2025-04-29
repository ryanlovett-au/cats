<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    public function application(): BelongsTo
    {
        return $this->BelongsTo(Application::class);
    }
}
