<?php

namespace App\Http\Controllers\Vouchers;

use App\Services\VoucherService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RegularizeVoucherHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(Request $request): JsonResponse|AnonymousResourceCollection
    {
        try {
            $this->voucherService->regularizeVouchers();

            return response()->json(['message' => 'Vouchers regularized successfully.']);
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
