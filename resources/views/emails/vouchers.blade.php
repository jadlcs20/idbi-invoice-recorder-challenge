<!DOCTYPE html>
<html>
<head>
    <title>Comprobantes Subidos</title>
</head>
<body>
    <h1>Estimado {{ $user->name }},</h1>
    <p>Hemos procesado tus comprobantes y aquí está el resumen:</p>

    <h3>Comprobantes registrados exitosamente:</h3>
    @if (count($successfulVouchers) > 0)
        <ol>
            @foreach ($successfulVouchers as $index => $voucher)
                <li>
                    <ul>
                        <li>Nombre del Emisor: {{ $voucher->issuer_name }}</li>
                        <li>Tipo de Documento del Emisor: {{ $voucher->issuer_document_type }}</li>
                        <li>Número de Documento del Emisor: {{ $voucher->issuer_document_number }}</li>
                        <li>Nombre del Receptor: {{ $voucher->receiver_name }}</li>
                        <li>Tipo de Documento del Receptor: {{ $voucher->receiver_document_type }}</li>
                        <li>Número de Documento del Receptor: {{ $voucher->receiver_document_number }}</li>
                        <li>Monto Total: {{ $voucher->total_amount }}</li>
                        <li>Serie: {{ $voucher->series }}</li>
                        <li>Número: {{ $voucher->number }}</li>
                        <li>Tipo de Comprobante: {{ $voucher->document_type }}</li>
                        <li>Moneda: {{ $voucher->currency }}</li>
                    </ul>
                </li>
            @endforeach
        </ol>
    @else
        <p>No se registraron comprobantes exitosamente.</p>
    @endif

    <h3>Comprobantes que no se pudieron registrar:</h3>
    @if (count($failedVouchers) > 0)
        <ol>
            @foreach ($failedVouchers as $index => $failedVoucher)
                <li>
                    <strong>Comprobante {{ $index + 1 }}:</strong><br>
                    XML del Comprobante: {{ $failedVoucher['xml_content'] }}<br>
                    Razón del fallo: {{ $failedVoucher['error_message'] }}
                </li>
            @endforeach
        </ol>
    @else
        <p>No hubo errores en el registro de los comprobantes.</p>
    @endif

    <p>¡Gracias por usar nuestro servicio!</p>
</body>
</html>
