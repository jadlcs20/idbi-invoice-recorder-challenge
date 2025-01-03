<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherTotalAmountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_totals_by_currency_for_authenticated_user()
    {
        // Create a user
        $user = User::factory()->create();

        // Create vouchers for the user in different currencies
        Voucher::factory()->createMany([
            ['user_id' => $user->id, 'currency' => 'USD', 'total_amount' => 100.50],
            ['user_id' => $user->id, 'currency' => 'USD', 'total_amount' => 376.08],
            ['user_id' => $user->id, 'currency' => 'PEN', 'total_amount' => 10432.00],
            ['user_id' => $user->id, 'currency' => 'PEN', 'total_amount' => 5111.00],
        ]);

        // Make the request as the authenticated user
        $response = $this->actingAs($user)->getJson('/api/v1/vouchers/total-amounts');

        // Assert the response structure and data
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                ['currency' => 'PEN', 'total' => '15543.00'],
                ['currency' => 'USD', 'total' => '476.58'],
            ],
        ]);
    }
}
