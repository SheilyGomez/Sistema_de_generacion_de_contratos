<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractVerificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ai_summary' => $this->ai_summary,
            'original_file_name' => basename($this->original_file_path),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
