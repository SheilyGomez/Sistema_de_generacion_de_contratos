<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\StoreVerificationRequest;
use App\Http\Resources\ContractVerificationResource;
use App\Models\ContractVerification;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ContractVerificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $verifications = $request
            ->user()
            ->contractVerifications()
            ->latest()
            ->paginate(20);

        return response()->json($verifications);
    }

    public function show(
        Request $request,
        ContractVerification $contractVerification,
    ): ContractVerificationResource {
        abort_if($contractVerification->user_id !== $request->user()->id, 403);

        return new ContractVerificationResource($contractVerification);
    }

    public function store(
        StoreVerificationRequest $request,
        AiService $aiService,
    ): JsonResponse {
        $user = $request->user();

        $path = $request->file("contract")->store("contracts/verifications");

        $absolutePath = Storage::path($path);
        $aiSummary = $aiService->analyzeContract($absolutePath);

        $verification = ContractVerification::create([
            "user_id" => $user->id,
            "original_file_path" => $path,
            "ai_summary" => $aiSummary,
        ]);

        return response()->json(
            [
                "message" => "Contrato verificado con éxito.",
                "verification" => new ContractVerificationResource(
                    $verification,
                ),
            ],
            201,
        );
    }

    public function download(
        Request $request,
        ContractVerification $contractVerification,
    ): BinaryFileResponse {
        abort_if($contractVerification->user_id !== $request->user()->id, 403);

        abort_if(
            !Storage::exists($contractVerification->original_file_path),
            404,
        );

        return response()->download(
            Storage::path($contractVerification->original_file_path)
        );
    }
}
