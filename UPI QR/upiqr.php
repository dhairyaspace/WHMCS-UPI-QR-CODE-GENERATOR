<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Configuration Settings
function upiqr_config() {
    return [
        "FriendlyName" => ["Type" => "System", "Value" => "UPI QR Payment"],
        "upi_id" => [
            "FriendlyName" => "yourname@upi",
            "Type" => "text",
            "Size" => "50",
            "Description" => "Example: yourname@upi",
        ],
    ];
}

// Payment Link Function
function upiqr_link($params) {
    $invoiceId = $params['invoiceid'];
    $amount = $params['amount'];
    $upiId = $params['upi_id'];

    $upiUrl = "upi://pay?pa={$upiId}&pn=WHMCSUPI&am={$amount}&cu=INR&tn=Invoice{$invoiceId}";

    // Create uploads folder if not exists
    $uploadDir = __DIR__ . '/uploads';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // QR image path
    $qrImagePath = $uploadDir . "/qr_invoice_{$invoiceId}.png";
    $qrImageUrl = "modules/gateways/uploads/qr_invoice_{$invoiceId}.png";

    // Generate QR Code if not exists
    if (!file_exists($qrImagePath)) {
        require_once __DIR__ . '/lib/qrlib.php';
        \QRcode::png($upiUrl, $qrImagePath, QR_ECLEVEL_H, 6);
    }

    // Output HTML to display on invoice page
    return "
        <div style='text-align:center;'>
            <h3>Scan & Pay with any UPI App</h3>
            <img src='{$qrImageUrl}' style='max-width: 250px; margin: 10px auto;'><br>
            <p><strong>Amount:</strong> ₹{$amount}</p>
            <p><strong>Pay To:</strong> {$upiId}</p>
            <p><strong>Note:</strong> Invoice {$invoiceId}</p>
            <p>Once paid, contact support with Transaction ID if not automatically updated.</p>
        </div>
    ";
}
