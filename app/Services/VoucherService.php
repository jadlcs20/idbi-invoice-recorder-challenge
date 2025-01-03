<?php

namespace App\Services;

use App\Events\Vouchers\VouchersCreated;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use SimpleXMLElement;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    public function getVouchers(int $page, int $paginate, array $filters, $user): LengthAwarePaginator
    {
        $query = Voucher::with(['lines', 'user'])
                        ->where('user_id', $user->id);

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }

        $filterMappings = [
            'serie' => 'serie',
            'number' => 'number',
            'type' => 'type',
            'currency' => 'currency',
        ];

        foreach ($filterMappings as $filterKey => $dbField) {
            if (isset($filters[$filterKey])) {
                $query->where($dbField, $filters[$filterKey]);
            }
        }

        return $query->paginate($paginate, ['*'], 'page', $page);
    }



    /**
     * @param string[] $xmlContents
     * @param User $user
     * @return Voucher[]
     */
    public function storeVouchersFromXmlContents(array $xmlContents, User $user): array
    {
        $successfulVouchers = [];
        $failedVouchers = [];

        foreach ($xmlContents as $xmlContent) {
            try {
                $voucher = $this->storeVoucherFromXmlContent($xmlContent, $user);
                $successfulVouchers[] = $voucher;
            } catch (\Exception $e) {
                $failedVouchers[] = [
                    'xml_content' => $xmlContent,
                    'error_message' => $e->getMessage(),
                ];
            }
        }

        VouchersCreated::dispatch($successfulVouchers, $failedVouchers, $user);

        return $successfulVouchers;
    }


    public function storeVoucherFromXmlContent(string $xmlContent, User $user): Voucher
    {
        $xml = new SimpleXMLElement($xmlContent);

        // Extract existing fields
        $issuerName = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name')[0];
        $issuerDocumentType = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
        $issuerDocumentNumber = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

        $receiverName = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName')[0];
        $receiverDocumentType = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
        $receiverDocumentNumber = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

        $totalAmount = (string) $xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount')[0];

        // Extract the series and number
        $fullInvoiceId = (string) $xml->xpath('//cbc:ID')[0];
        $series = substr($fullInvoiceId, 0, 4);
        $number = substr($fullInvoiceId, 5); // Surpass character "-"

        // Extract the document type
        $documentType = (string) $xml->xpath('//cbc:InvoiceTypeCode')[0];

        // Extract the currency
        $currency = (string) $xml->xpath('//cbc:DocumentCurrencyCode')[0];

        $voucher = new Voucher([
            'issuer_name' => $issuerName,
            'issuer_document_type' => $issuerDocumentType,
            'issuer_document_number' => $issuerDocumentNumber,
            'receiver_name' => $receiverName,
            'receiver_document_type' => $receiverDocumentType,
            'receiver_document_number' => $receiverDocumentNumber,
            'total_amount' => $totalAmount,
            'xml_content' => $xmlContent,
            'series' => $series,
            'number' => $number,
            'document_type' => $documentType,
            'currency' => $currency,
            'user_id' => $user->id,
        ]);
        $voucher->save();

        foreach ($xml->xpath('//cac:InvoiceLine') as $invoiceLine) {
            $name = (string) $invoiceLine->xpath('cac:Item/cbc:Description')[0];
            $quantity = (float) $invoiceLine->xpath('cbc:InvoicedQuantity')[0];
            $unitPrice = (float) $invoiceLine->xpath('cac:Price/cbc:PriceAmount')[0];

            $voucherLine = new VoucherLine([
                'name' => $name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'voucher_id' => $voucher->id,
            ]);

            $voucherLine->save();
        }

        return $voucher;
    }

    public function regularizeVouchers(): void
    {
        $vouchers = Voucher::whereNull('series')
            ->orWhereNull('number')
            ->orWhereNull('document_type')
            ->orWhereNull('currency')
            ->get();

        foreach ($vouchers as $voucher) {
            $this->regularizeVoucher($voucher);
        }
    }
    public function regularizeVoucher($voucher): void
    {
        $xmlContent = $voucher->xml_content;
        $xml = new SimpleXMLElement($xmlContent);

        $fullInvoiceId = (string) $xml->xpath('//cbc:ID')[0];
        $series = substr($fullInvoiceId, 0, 4);
        $number = substr($fullInvoiceId, 5); // Surpass character "-"

        $documentType = (string) $xml->xpath('//cbc:InvoiceTypeCode')[0];
        $currency = (string) $xml->xpath('//cbc:DocumentCurrencyCode')[0];

        $voucher->update([
            'series' => $series,
            'number' => $number,
            'document_type' => $documentType,
            'currency' => $currency,
        ]);
    }

    public function getTotalAmountVouchers($user)
    {    
        return DB::table('vouchers')
            ->selectRaw( 'currency, SUM(total_amount) as total')
            ->where('user_id', $user->id)
            ->groupBy('currency') 
            ->get();
    }

    public function deleteVoucher($voucherId, $user): array
    {
        $voucher = Voucher::where('id', $voucherId)->where('user_id', $user->id)->first();

        if (!$voucher) {
            return [
                'message' => "Voucher not found or unauthorized",
                'status' => 404
            ];
        }
        $voucher->delete();

        return [
            'message' => "Voucher deleted successfully",
            'status' => 200
        ];
    }
}
