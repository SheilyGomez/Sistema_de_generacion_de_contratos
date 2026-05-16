<?php

namespace App\Http\Controllers\Api;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\StoreGenerationRequest;
use App\Http\Requests\Lawyer\StoreLawyerRequest;
use App\Http\Resources\ContractGenerationResource;
use App\Models\ContractGeneration;
use App\Models\LawyerRequest;
use App\Models\User;
use App\Enums\LawyerRequestStatus;
use App\Enums\Role;
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

        $fileName = 'contract_' . now()->format('Ymd_His') . '.pdf';
        $filePath = "contracts/generations/{$fileName}";

        $pdf = Pdf::loadHTML($result['contract']);
        $pdf->setPaper('A4', 'portrait');
        Storage::put($filePath, $pdf->output());

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
        $user = $request->user();
        $isOwner = $contractGeneration->user_id === $user->id;
        $isLawyer = $user->role?->value === 'abogado' &&
            LawyerRequest::where('contract_generation_id', $contractGeneration->id)
                ->where('lawyer_id', $user->id)
                ->exists();

        abort_if(!$isOwner && !$isLawyer, 403);

        abort_if(
            !$contractGeneration->generated_file_path ||
            !Storage::exists($contractGeneration->generated_file_path),
            404
        );

        return response()->download(
            Storage::path($contractGeneration->generated_file_path)
        );
    }

    public function requestReview(StoreLawyerRequest $request, ContractGeneration $contractGeneration): JsonResponse
    {
        $user = $request->user();

        abort_if($contractGeneration->user_id !== $user->id, 403);

        $lawyer = User::where('id', $request->lawyer_id)
            ->where('role', Role::Abogado)
            ->firstOrFail();

        $price = (float) config('services.lawyer_review_price', 50.00);

        $pendingTotal = (float) LawyerRequest::where('freelancer_id', $user->id)
            ->where('status', LawyerRequestStatus::Pending)
            ->sum('price');

        $available = (float) $user->balance - $pendingTotal;
        abort_if($available < $price, 400, 'Fondos insuficientes. Tienes otras peticiones pendientes que comprometen tu saldo.');

        $lawyerRequest = LawyerRequest::create([
            'contract_generation_id' => $contractGeneration->id,
            'freelancer_id' => $user->id,
            'lawyer_id' => $lawyer->id,
            'freelancer_comment' => $request->freelancer_comment,
            'price' => $price,
        ]);

        return response()->json([
            'message' => 'Petición enviada al abogado.',
            'lawyer_request' => new \App\Http\Resources\LawyerRequestResource($lawyerRequest),
        ], 201);
    }

    public function myLawyerRequests(Request $request): JsonResponse
    {
        $requests = LawyerRequest::where('freelancer_id', $request->user()->id)
            ->with(['contractGeneration', 'lawyer'])
            ->latest()
            ->paginate(20);

        return response()->json($requests);
    }
}
