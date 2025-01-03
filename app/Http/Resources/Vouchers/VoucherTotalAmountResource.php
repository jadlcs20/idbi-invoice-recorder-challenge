<?php

namespace App\Http\Resources\Vouchers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherTotalAmountResource extends JsonResource
{
    public $resource;

    public function toArray(Request $request): array
    {
        return [
            'currency' => $this->resource->currency,
            'total' => $this->resource->total
        ];
    }
}
