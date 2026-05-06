<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractGeneration extends Model
{
    protected $fillable = [
        'user_id',
        'requirements',
        'generated_file_path',
        'ai_summary',
    ];

    protected function casts(): array
    {
        return [
            'requirements' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lawyerRequests(): HasMany
    {
        return $this->hasMany(LawyerRequest::class);
    }
}
