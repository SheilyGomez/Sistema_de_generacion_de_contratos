<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\StoreGenerationRequest;
use App\Http\Resources\ContractGenerationResource;
use App\Models\ContractGeneration;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ContractGenerationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $generations = $request->user()
            ->contractGenerations()
            ->latest()
            ->paginate(20);

        return response()->json($generations);
    }

    public function show(Request $request, ContractGeneration $contractGeneration): ContractGenerationResource
    {
        abort_if($contractGeneration->user_id !== $request->user()->id, 403);

        return new ContractGenerationResource($contractGeneration);
    }

    public function store(StoreGenerationRequest $request, AiService $aiService): JsonResponse
    {
        $user = $request->user();

        $requirements = $request->only([
            'service_type',
            'duration',
            'payment_amount',
            'payment_currency',
            'client_name',
            'freelancer_name',
            'start_date',
            'extra_details',
        ]);

        $result = $aiService->generateContract($requirements);

        $fileName = 'contract_' . now()->format('Ymd_His') . '.txt';
        $filePath = "contracts/generations/{$fileName}";
        Storage::put($filePath, $result['contract']);

        $generation = ContractGeneration::create([
            'user_id' => $user->id,
            'requirements' => $requirements,
            'generated_file_path' => $filePath,
            'ai_summary' => $result['summary'] ?? null,
        ]);

        return response()->json([
            'message' => 'Contrato generado con éxito.',
            'generation' => new ContractGenerationResource($generation),
        ], 201);
    }

    public function download(Request $request, ContractGeneration $contractGeneration): BinaryFileResponse
    {
        abort_if($contractGeneration->user_id !== $request->user()->id, 403);

        abort_if(
            !$contractGeneration->generated_file_path ||
            !Storage::exists($contractGeneration->generated_file_path),
            404
        );

        return response()->download(
            Storage::path($contractGeneration->generated_file_path)
        );
    }
}
