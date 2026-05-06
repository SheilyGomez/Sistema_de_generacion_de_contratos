<?php

namespace App\Http\Controllers\Api;

use App\Enums\LawyerRequestStatus;
use App\Enums\Role;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\DepositRequest;
use App\Http\Requests\Wallet\WithdrawRequest;
use App\Http\Resources\TransactionResource;
use App\Models\LawyerRequest;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function deposit(DepositRequest $request): JsonResponse
    {
        $user = $request->user();

        $transaction = DB::transaction(function () use ($user, $request) {
            $user->increment('balance', $request->amount);

            return Transaction::create([
                'user_id' => $user->id,
                'type' => TransactionType::Deposit,
                'amount' => $request->amount,
                'description' => 'Abono a la cuenta',
            ]);
        });

        return response()->json([
            'message' => 'Abono realizado con éxito.',
            'transaction' => new TransactionResource($transaction),
            'balance' => $user->fresh()->balance,
        ], 201);
    }

    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        $user = $request->user();

        $pendingTotal = (float) LawyerRequest::where('freelancer_id', $user->id)
            ->where('status', LawyerRequestStatus::Pending)
            ->sum('price');

        $available = (float) $user->balance - $pendingTotal;

        abort_if((float) $request->amount > $available, 400, 'Fondos insuficientes. Tienes ' . number_format($pendingTotal, 2) . ' comprometidos en peticiones pendientes.');

        $transaction = DB::transaction(function () use ($user, $request) {
            $user->decrement('balance', $request->amount);

            return Transaction::create([
                'user_id' => $user->id,
                'type' => TransactionType::Withdrawal,
                'amount' => $request->amount,
                'description' => "Retiro a cuenta {$request->account_number}",
            ]);
        });

        return response()->json([
            'message' => 'Retiro realizado con éxito.',
            'transaction' => new TransactionResource($transaction),
            'balance' => $user->fresh()->balance,
        ], 201);
    }

    public function transactions(Request $request): JsonResponse
    {
        $transactions = $request->user()
            ->transactions()
            ->latest()
            ->paginate(20);

        return response()->json($transactions);
    }

    public function balance(Request $request): JsonResponse
    {
        return response()->json([
            'balance' => $request->user()->balance,
        ]);
    }
}
