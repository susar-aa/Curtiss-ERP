<?php
$customer = $data['customer'];
$cash = floatval($data['cash']);
$bank = floatval($data['bank']);
$cheque = floatval($data['cheque']);
$totalCollected = $data['total_collected'];

// Sanitize customer phone for WhatsApp (remove spaces, symbols)
$cleanPhone = preg_replace('/[^0-9]/', '', $customer->phone);
if (substr($cleanPhone, 0, 1) === '0') {
    $cleanPhone = '94' . substr($cleanPhone, 1); // fallback to Sri Lankan country code if starts with 0
}

// Generate shared message text
$sharedMessage = "Hello " . $customer->name . ",\n\n";
$sharedMessage .= "Your delivery payment receipt from Curtiss ERP is ready:\n";
$sharedMessage .= "--------------------------------------\n";
$sharedMessage .= "• Cash Received: Rs. " . number_format($cash, 2) . "\n";
$sharedMessage .= "• Bank Transfer: Rs. " . number_format($bank, 2) . "\n";
$sharedMessage .= "• Cheques (PDC): Rs. " . number_format($cheque, 2) . "\n";
$sharedMessage .= "• Total Collected: Rs. " . number_format($totalCollected, 2) . "\n";
$sharedMessage .= "--------------------------------------\n";
$sharedMessage .= "Thank you for your valued business!";

$waUrl = "https://wa.me/" . $cleanPhone . "?text=" . urlencode($sharedMessage);
$mailUrl = "mailto:" . urlencode($customer->email ?? '') . "?subject=" . urlencode("Curtiss ERP Delivery Receipt - " . $customer->name) . "&body=" . urlencode($sharedMessage);
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($sharedMessage);
?>

<div class="card" style="text-align: center; border-top: 4px solid var(--success); padding: 30px 20px; margin-bottom: 25px;">
    <div style="font-size: 55px; margin-bottom: 15px; color: var(--success); animation: scaleUp 0.3s ease-out;">✓</div>
    <span class="badge badge-success" style="margin-bottom: 12px;">Checkout Complete</span>
    <h2 style="margin: 0 0 10px; font-size: 20px; font-weight: 800;"><?= htmlspecialchars($customer->name) ?></h2>
    <p style="margin: 0 0 20px; font-size: 14px; color: var(--text-muted);">The delivery checklist has been logged, inventory was physically deducted, and the general ledger journal has been posted.</p>

    <div style="background: var(--app-bg); border-radius: 12px; padding: 15px; text-align: left; border: 1px solid var(--border); font-size: 14px; margin-bottom: 10px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <span style="color: var(--text-muted);">Amount Collected:</span>
            <strong>Rs. <?= number_format($totalCollected, 2) ?></strong>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted);">
            <span>Cash: Rs. <?= number_format($cash, 2) ?> | Bank: Rs. <?= number_format($bank, 2) ?> | CHQ: Rs. <?= number_format($cheque, 2) ?></span>
        </div>
    </div>
</div>

<h3 style="font-size: 14px; font-weight: 800; text-transform: uppercase; margin: 25px 0 12px; color: var(--text-muted); letter-spacing: 0.5px; text-align: center;">Share Digital Receipt</h3>

<div class="card" style="padding: 20px; text-align: center; display: flex; flex-direction: column; gap: 15px;">
    <!-- WHATSAPP SHARE -->
    <a href="<?= $waUrl ?>" target="_blank" class="btn-primary" style="background: #25d366; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.2); display: flex; align-items: center; justify-content: center; gap: 8px;">
        <span style="font-size: 18px;">💬</span> Share via WhatsApp
    </a>

    <!-- EMAIL SHARE -->
    <a href="<?= $mailUrl ?>" class="btn-primary" style="background: #ea4335; box-shadow: 0 4px 12px rgba(234, 67, 53, 0.2); display: flex; align-items: center; justify-content: center; gap: 8px;">
        <span style="font-size: 18px;">✉</span> Share via Email
    </a>

    <!-- QR CODE FOR SCANNING -->
    <div style="margin-top: 15px; padding-top: 20px; border-top: 1px solid var(--border);">
        <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; color: var(--text-muted); margin-bottom: 12px;">Scan Receipt QR</div>
        <div style="display: inline-block; background: #fff; padding: 10px; border-radius: 12px; border: 1px solid var(--border);">
            <img src="<?= $qrUrl ?>" alt="Receipt QR Code" style="display: block; width: 160px; height: 160px;">
        </div>
    </div>
</div>

<div style="margin-top: 20px; margin-bottom: 40px;">
    <a href="<?= APP_URL ?>/driver" class="btn-primary" style="background: var(--primary);">
        ← Return to Driver Hub
    </a>
</div>

<style>
    @keyframes scaleUp {
        0% { transform: scale(0.5); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>
