<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFile extends Model
{
    protected $fillable = ['user_id', 'file_id', 'name', 'path'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
