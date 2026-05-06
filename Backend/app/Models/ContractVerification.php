<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractVerification extends Model
{
    protected $fillable = [
        'user_id',
        'original_file_path',
        'ai_summary',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
