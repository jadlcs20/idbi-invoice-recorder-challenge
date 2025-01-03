<?php

namespace App\Http\Controllers\Vouchers;

use App\Services\VoucherService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeleteVoucherHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $jsonData = $this->voucherService->deleteVoucher($request->query("voucher_id"), $user);

            return response()->json(['message' => $jsonData['message'], 'status' => $jsonData['status']]);
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
