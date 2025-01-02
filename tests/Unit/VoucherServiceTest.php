<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Voucher;
use App\Models\User;
use App\Services\VoucherService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VoucherServiceTest extends TestCase
{
    use RefreshDatabase;

    private VoucherService $voucherService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->voucherService = app(VoucherService::class);
    }

    public function test_get_vouchers_returns_paginated_data()
    {
        User::factory()->create();
        Voucher::factory()->count(10)->create();

        $result = $this->voucherService->getVouchers(1, 5);

        $this->assertCount(5, $result->items());
        $this->assertTrue($result->hasMorePages());
    }

    public function test_store_voucher_from_xml_content_saves_correctly()
    {
        $user = User::factory()->create();
        $xmlContent = file_get_contents(base_path('tests/Fixtures/F002-3625.xml')); // Usa un archivo de prueba.

        $voucher = $this->voucherService->storeVoucherFromXmlContent($xmlContent, $user);

        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher->id,
            'user_id' => $user->id,
            'xml_content' => $xmlContent,
        ]);

        $this->assertCount(2, $voucher->lines);
    }

    public function test_regularize_voucher_updates_correctly()
    {
        $voucher = Voucher::factory()->create([
            'series' => null,
            'number' => null,
            'document_type' => null,
            'currency' => null,
            'xml_content' => file_get_contents(base_path('tests/Fixtures/F002-3625.xml')),
        ]);

        $this->voucherService->regularizeVoucher($voucher);

        $this->assertNotNull($voucher->refresh()->series);
        $this->assertNotNull($voucher->number);
        $this->assertNotNull($voucher->document_type);
        $this->assertNotNull($voucher->currency);
    }

}
