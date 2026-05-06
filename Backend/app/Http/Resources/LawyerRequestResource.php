<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LawyerRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contract_generation_id' => $this->contract_generation_id,
            'freelancer' => new UserResource($this->whenLoaded('freelancer')),
            'lawyer' => new UserResource($this->whenLoaded('lawyer')),
            'freelancer_comment' => $this->freelancer_comment,
            'lawyer_comment' => $this->lawyer_comment,
            'corrected_file_name' => $this->corrected_file_path
                ? basename($this->corrected_file_path)
                : null,
            'status' => $this->status,
            'price' => $this->price,
            'contract_generation' => new ContractGenerationResource(
                $this->whenLoaded('contractGeneration')
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
