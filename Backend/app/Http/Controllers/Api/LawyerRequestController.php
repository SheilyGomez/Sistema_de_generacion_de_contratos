<?php

namespace App\Http\Controllers\Api;

use App\Enums\LawyerRequestStatus;
use App\Enums\Role;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Lawyer\CompleteLawyerRequest;
use App\Http\Requests\Lawyer\StoreLawyerRequest;
use App\Http\Requests\Lawyer\UploadCorrectedContract;
use App\Http\Resources\LawyerRequestResource;
use App\Models\ContractGeneration;
use App\Models\LawyerRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LawyerRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $requests = LawyerRequest::query()
            ->where('lawyer_id', $request->user()->id)
            ->with(['contractGeneration', 'freelancer'])
            ->latest()
            ->paginate(20);

        return response()->json($requests);
    }

    public function show(Request $request, LawyerRequest $lawyerRequest): LawyerRequestResource
    {
        $user = $request->user();

        abort_unless(
            $user->id === $lawyerRequest->lawyer_id || $user->id === $lawyerRequest->freelancer_id,
            403
        );

        $lawyerRequest->load(['contractGeneration', 'freelancer', 'lawyer']);

        return new LawyerRequestResource($lawyerRequest);
    }

    public function store(StoreLawyerRequest $request): JsonResponse
    {
        $user = $request->user();

        $generation = ContractGeneration::findOrFail($request->contract_generation_id);
        abort_if($generation->user_id !== $user->id, 403);

        $lawyer = User::where('id', $request->lawyer_id)
            ->where('role', Role::Abogado)
            ->firstOrFail();

        $price = (float) config('services.lawyer_review_price', 50.00);
        abort_if((float) $user->balance < $price, 400, 'Fondos insuficientes. Recarga tu cuenta para continuar.');

        $lawyerRequest = LawyerRequest::create([
            'contract_generation_id' => $generation->id,
            'freelancer_id' => $user->id,
            'lawyer_id' => $lawyer->id,
            'freelancer_comment' => $request->freelancer_comment,
            'price' => $price,
        ]);

        return response()->json([
            'message' => 'Petición enviada al abogado.',
            'lawyer_request' => new LawyerRequestResource($lawyerRequest),
        ], 201);
    }

    public function upload(
        UploadCorrectedContract $request,
        LawyerRequest $lawyerRequest,
    ): JsonResponse {
        abort_if($request->user()->id !== $lawyerRequest->lawyer_id, 403);
        abort_if($lawyerRequest->status !== LawyerRequestStatus::Pending, 400, 'Esta petición ya fue atendida.');

        $path = $request->file('corrected_contract')->store('contracts/corrected');

        $lawyerRequest->update([
            'corrected_file_path' => $path,
        ]);

        return response()->json([
            'message' => 'Contrato corregido subido con éxito.',
            'lawyer_request' => new LawyerRequestResource($lawyerRequest->refresh()),
        ]);
    }

    public function complete(
        CompleteLawyerRequest $request,
        LawyerRequest $lawyerRequest,
    ): JsonResponse {
        $user = $request->user();

        abort_if($user->id !== $lawyerRequest->lawyer_id, 403);
        abort_if($lawyerRequest->status !== LawyerRequestStatus::Pending, 400, 'Esta petición ya fue completada.');
        abort_if(!$lawyerRequest->corrected_file_path, 400, 'Debes subir el contrato corregido antes de terminar.');

        if (!Storage::exists($lawyerRequest->corrected_file_path)) {
            return response()->json(['message' => 'El archivo corregido no se encuentra.'], 400);
        }

        DB::transaction(function () use ($lawyerRequest, $request) {
            $lawyerRequest->update([
                'lawyer_comment' => $request->lawyer_comment,
                'status' => LawyerRequestStatus::Completed,
            ]);

            $freelancer = User::where('id', $lawyerRequest->freelancer_id)->lockForUpdate()->first();
            $lawyer = User::where('id', $lawyerRequest->lawyer_id)->lockForUpdate()->first();
            $price = (float) $lawyerRequest->price;

            abort_if((float) $freelancer->balance < $price, 400, 'El freelancer no tiene fondos suficientes para completar este pago.');

            $freelancer->decrement('balance', $price);

            Transaction::create([
                'user_id' => $freelancer->id,
                'type' => TransactionType::Payment,
                'amount' => $price,
                'description' => "Pago por revisión de contrato #{$lawyerRequest->id}",
            ]);

            $lawyer->increment('balance', $price);

            Transaction::create([
                'user_id' => $lawyer->id,
                'type' => TransactionType::Earning,
                'amount' => $price,
                'description' => "Ganancia por revisión de contrato #{$lawyerRequest->id}",
            ]);
        });

        return response()->json([
            'message' => 'Trabajo completado. El pago ha sido transferido.',
            'lawyer_request' => new LawyerRequestResource($lawyerRequest->refresh()->load(['contractGeneration', 'freelancer', 'lawyer'])),
        ]);
    }

    public function download(
        Request $request,
        LawyerRequest $lawyerRequest,
    ): BinaryFileResponse {
        $user = $request->user();

        abort_unless(
            $user->id === $lawyerRequest->lawyer_id || $user->id === $lawyerRequest->freelancer_id,
            403
        );

        abort_if(
            !$lawyerRequest->corrected_file_path || !Storage::exists($lawyerRequest->corrected_file_path),
            404
        );

        return response()->download(Storage::path($lawyerRequest->corrected_file_path));
    }

    public function lawyers(Request $request): JsonResponse
    {
        $lawyers = User::where('role', Role::Abogado)
            ->select('id', 'name', 'email')
            ->get();

        return response()->json($lawyers);
    }
}
