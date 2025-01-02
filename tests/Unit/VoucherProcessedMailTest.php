<?php

namespace Tests\Unit;

use App\Mail\VouchersCreatedMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use stdClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VoucherProcessedMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_content_for_successful_and_failed_vouchers(): void
    {
        $successfulVoucher = new stdClass();
        $successfulVoucher->issuer_name = 'Empresa X';
        $successfulVoucher->issuer_document_type = 'RUC';
        $successfulVoucher->issuer_document_number = '12345678901';
        $successfulVoucher->receiver_name = 'Cliente Y';
        $successfulVoucher->receiver_document_type = 'DNI';
        $successfulVoucher->receiver_document_number = '98765432';
        $successfulVoucher->total_amount = '150.50';
        $successfulVoucher->series = 'F001';
        $successfulVoucher->number = '000123';
        $successfulVoucher->document_type = 'Factura';
        $successfulVoucher->currency = 'PEN';

        $failedVoucher = [
            'xml_content' => '<xml>content</xml>',
            'error_message' => 'Invalid format',
        ];

        $successfulVouchers = [$successfulVoucher];
        $failedVouchers = [$failedVoucher];

        $user = new stdClass();
        $user->name = 'Test User';

        $view = view('emails.vouchers', compact('successfulVouchers', 'failedVouchers', 'user'))->render();

        $this->assertStringContainsString('Empresa X', $view);
        $this->assertStringContainsString('Cliente Y', $view);
        $this->assertStringContainsString('Invalid format', $view);
    }
}
