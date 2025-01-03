<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetVouchersTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_see_vouchers(): void
    {
        $user = User::factory()->create();

        $voucher = Voucher::factory()->create([
            'user_id' => $user->id,
            'series' => 'B001',
            'currency' => 'PEN',
            'created_at' => '2023-05-15',
        ]);

        VoucherLine::factory()->create([
            'voucher_id' => $voucher->id,
            'name' => 'CONSTRUCCIÓN',
            'quantity' => 3,
            'unit_price' => 3025.42,
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/vouchers?start_date=2023-01-01&end_date=2023-12-31&page=1&paginate=10');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'issuer_name',
                    'issuer_document_type',
                    'issuer_document_number',
                    'receiver_name',
                    'receiver_document_type',
                    'receiver_document_number',
                    'total_amount',
                    'series',
                    'number',
                    'document_type',
                    'currency',
                    'user' => [
                        'id',
                        'name',
                        'last_name',
                        'email',
                    ],
                    'lines' => [
                        '*' => [
                            'id',
                            'name',
                            'quantity',
                            'unit_price',
                        ],
                    ],
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);

        $response->assertJsonFragment([
            'series' => 'B001',
            'currency' => 'PEN',
        ]);
    }

}
