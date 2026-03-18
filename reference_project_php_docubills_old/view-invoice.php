<?php
// ✅ IMPORTANT: Prevent stale "Paid" invoice view after status changes (browser/CDN cache)
header("Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
header("X-Accel-Expires: 0");

require_once 'config.php';

$shouldCelebrate = isset($_GET['celebrate']) && $_GET['celebrate'] == '1';

// Raw invoice param from URL – can be ID (388) or invoice_number (FINVOICE-1I-10)
$invoiceParam = $_GET['invoice'] ?? '';

if ($invoiceParam === '') {
    exit('❌ Missing invoice number.');
}

// Try to resolve to actual invoice_number
$invoice_number = null;

// 1) If it’s all digits, first try it as numeric ID
if (ctype_digit($invoiceParam)) {
    $stmt = $pdo->prepare("SELECT invoice_number FROM invoices WHERE id = ?");
    $stmt->execute([$invoiceParam]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['invoice_number'])) {
        $invoice_number = $row['invoice_number'];
    }
}

// 2) If we still don’t have an invoice_number, treat the param as invoice_number itself
if ($invoice_number === null) {
    $invoice_number = $invoiceParam;
}

// 3) Now load the saved HTML based on the resolved invoice_number
$html_path = __DIR__ . "/invoices/{$invoice_number}.html";
if (!file_exists($html_path)) {
    exit("❌ Invoice not found: {$invoice_number}");
}
$html_content = file_get_contents($html_path);
if (!$html_content) {
    exit('❌ Failed to read invoice content.');
}

// Fetch payment data
$stmt = $pdo->prepare("SELECT payment_method, payment_proof, status FROM invoices WHERE invoice_number = ?");
$stmt->execute([$invoice_number]);
$invoice_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (isset($invoice_data['status'])) {
    $status = strtolower($invoice_data['status']);
    $color = ($status === 'paid') ? 'green' : 'red';

    // Force override for any colored amount in the saved HTML
    $html_content = preg_replace_callback(
        '/(Grand Total|Total Amount Payable|Total Amount)\s*:\s*(.*?)(<\/div>|<\/td>)/is',
        function ($matches) use ($color) {
            $cleanAmount = strip_tags($matches[2]);
            return $matches[1] . ': <strong><span style="color:' . $color . '; font-weight: bold;">' . $cleanAmount . '</span></strong>' . $matches[3];
        },
        $html_content
    );
}


// ▶︎ Replace the old footer “Payment Method:” in the saved HTML with the new one
if (!empty($invoice_data['payment_method'])) {
    $pattern = '/<div style="margin-top: 8px; text-align: right; font-size: 12px;">\s*<strong>Payment Method:.*?<\/div>/s';
    $replacement = '<div style="margin-top: 8px; text-align: right; font-size: 12px;">'
                 . '<strong>Payment Method:</strong> '
                 . htmlspecialchars($invoice_data['payment_method'])
                 . '</div>';
    $html_content = preg_replace($pattern, $replacement, $html_content);
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
<?php

// Only show CSS watermark if the HTML doesn't already include one
if (
    strtolower($invoice_data['status'] ?? '') === 'paid'
    && strpos($html_content, 'PAID') === false
): ?>
  <style>
    .invoice-container::before {
      content: "PAID";
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-30deg);
      font-size: 120px;
      color: rgba(0, 128, 0, 0.2);
      font-weight: bold;
      pointer-events: none;
      z-index: 1000;
    }
  </style>
<?php endif; ?>
  <title>View Invoice <?= htmlspecialchars($invoice_number) ?></title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      margin: 0;
      padding: 20px;
    }
    .invoice-container {
      background: #fff;
      max-width: 900px;
      margin: auto;
      padding: 30px;
      border: 1px solid #ddd;
    }
  </style>
</head>
<body>
  <div class="invoice-container">
      <?php if ($shouldCelebrate): ?>
          <div style="text-align: center; margin-bottom: 20px; padding: 15px; background: #e6f9ec; color: #207a3c; border: 1px solid #b4e0c6; border-radius: 8px; font-size: 1.25rem; font-weight: 500;">
            🎉 Payment Successful! Thank you for your payment.
          </div>
        <?php endif; ?>
        
    <button onclick="window.print()" style="margin-bottom: 20px; padding: 8px 14px; font-size: 14px;">🖨️ Print Invoice</button>

        
    <?= $html_content ?>
        <?php if (!empty($invoice_data['payment_method'])): ?>
      <div style="margin-top: 1.5rem;">
        <strong>Payment Method:</strong> <?= htmlspecialchars($invoice_data['payment_method']) ?>
      </div>
    <?php endif; ?>
        <?php if (!empty($invoice_data['payment_proof'])): ?>
          <div style="margin-top: 1rem;">
            <strong>Proof of Payment:</strong><br>
            <?php
              $file = $invoice_data['payment_proof'];
              $ext = pathinfo($file, PATHINFO_EXTENSION);
              if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])):
            ?>
            
            <img src="uploads/payment_proofs/<?= htmlspecialchars($file) ?>" alt="Proof" style="max-width: 100%; border-radius: 8px; margin-top: 0.5rem;">
            <?php elseif (strtolower($ext) === 'pdf'): ?>
              <iframe src="uploads/payment_proofs/<?= htmlspecialchars($file) ?>" width="100%" height="450" style="border: 1px solid #ccc; margin-top: 0.5rem;"></iframe>
            <?php endif; ?>
          </div>
        <?php endif; ?>
  </div>
  
  <?php if ($shouldCelebrate): ?>
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
  <script>
    function launchConfetti() {
      window.scrollTo({ top: 0, behavior: 'smooth' }); // ✅ Move outside the confetti call
      confetti({
        particleCount: 150,
        spread: 80,
        origin: { y: 0.6 }
      });
    }

    launchConfetti();
    setTimeout(launchConfetti, 500);
    setTimeout(launchConfetti, 1000);
  </script>
<?php endif; ?>

</body>
</html>
