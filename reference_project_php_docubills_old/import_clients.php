<?php
require_once 'config.php';

try {
  // Get all unique client names from invoices
  $stmt = $pdo->query("SELECT DISTINCT bill_to_name FROM invoices");
  $clientsInInvoices = $stmt->fetchAll(PDO::FETCH_COLUMN);

  $added = 0;

  foreach ($clientsInInvoices as $clientName) {
    // Check if the client already exists
    $check = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE company_name = ?");
    $check->execute([$clientName]);
    $exists = $check->fetchColumn();

    if (!$exists) {
      // Insert into clients table using the correct column name
      $insert = $pdo->prepare("INSERT INTO clients (company_name) VALUES (?)");
      $insert->execute([$clientName]);
      $added++;
    }
  }

  echo "✅ Import completed. $added new clients added.";
} catch (Exception $e) {
  echo "❌ Error: " . $e->getMessage();
}
