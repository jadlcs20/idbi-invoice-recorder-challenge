<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherDeletionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_authenticated_user_to_delete_own_voucher()
    {
        $user = User::factory()->create();

        $voucher = Voucher::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)
                        ->deleteJson("/api/v1/vouchers?voucher_id={$voucher->id}");

        $response->assertStatus(200);
        $response->assertJson([
                'message' => 'Voucher deleted successfully',
        ]);
    }
}
