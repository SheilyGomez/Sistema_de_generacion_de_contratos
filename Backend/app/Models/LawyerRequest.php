<?php

namespace App\Models;

use App\Enums\LawyerRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LawyerRequest extends Model
{
    protected $fillable = [
        'contract_generation_id',
        'freelancer_id',
        'lawyer_id',
        'freelancer_comment',
        'lawyer_comment',
        'corrected_file_path',
        'status',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'status' => LawyerRequestStatus::class,
            'price' => 'decimal:2',
        ];
    }

    public function contractGeneration(): BelongsTo
    {
        return $this->belongsTo(ContractGeneration::class);
    }

    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }
}
