<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Requests\Vouchers\GetVouchersRequest;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Http\Resources\Vouchers\VoucherTotalAmountResource;
use App\Services\VoucherService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GetVouchersTotalAmountHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(): AnonymousResourceCollection
    {
        $user = auth()->user();
        $totalAmountData = $this->voucherService->getTotalAmountVouchers($user);

        return VoucherTotalAmountResource::collection($totalAmountData);
    }
}
