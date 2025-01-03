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
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->voucherService = app(VoucherService::class);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_store_voucher_from_xml_content_saves_correctly()
    {
        
        $xmlContent = file_get_contents(base_path('tests/Fixtures/F002-3625.xml')); // Usa un archivo de prueba.

        $voucher = $this->voucherService->storeVoucherFromXmlContent($xmlContent, $this->user);

        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher->id,
            'user_id' => $this->user->id,
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
