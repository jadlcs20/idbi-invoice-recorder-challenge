<?php

namespace App\Jobs;

use App\Services\VoucherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessVoucher implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $xmlContent;
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param string $xmlContent
     * @param \App\Models\User $user
     */
    public function __construct(string $xmlContent, $user)
    {
        $this->xmlContent = $xmlContent;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(VoucherService $voucherService)
    {
        $voucherService->storeVoucherFromXmlContent($this->xmlContent, $this->user);
    }
}
