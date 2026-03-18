<?php
// home.php (Public landing page)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$isLoggedIn = !empty($_SESSION['user_id']);

// REAL data from the Google Sheet
$sheetColumns = ['No', 'Country of Origin', 'No. of Pkgs', 'Type of Pkging', 'Description of Goods', 'Pack QTY', 'HS Code', 'Unit of Measure', 'Weight', 'Unit Price', 'Subtotal'];
$sheetData = [
  [1, 'USA', 12, 'Pallet', 'High-Performance GPU Servers (Model XZ-900)', 12, '847150', 'Units', '580.40', 12999.99, 155999.88],
  [2, 'Germany', 45, 'Crate', 'Automated Robotic Arms - Industrial Grade (RA-4500 Series)', 45, '842890', 'Units', '2765.00', 18450.50, 830272.50],
  [3, 'Japan', 200, 'Box', '5G Network Infrastructure Modules + Edge Computing Nodes', 200, '851762', 'Units', '1500.80', 545.75, 109150.00],
  [4, 'China', 1500, 'Container', 'IoT Smart Sensor - Multi-Protocol (LoRaWAN/NB-IoT/WiFi)', 1500, '902610', 'Units', '1100.20', 19.90, 29850.00],
  [5, 'South Korea', 75, 'Box', 'OLED Flexible Displays - Grade A Enterprise Batch', 75, '901380', 'Units', '430.00', 780.00, 58500.00],
  [6, 'France', 10, 'Pallet', 'AI-Powered Supply Chain Optimization Software Licenses (Enterprise Tier)', 10, '852380', 'Licenses', '0.00', 25000.00, 250000.00]
];

$demoClientData = [
  'company' => 'Acme Corporation',
  'contact' => 'John Smith',
  'email' => 'john@acmecorp.com',
  'phone' => '+1 (555) 123-4567'
];

// Calculate totals
$totals = [
  'Unit Price' => 0,
  'Subtotal' => 0
];

foreach ($sheetData as $row) {
  $totals['Unit Price'] += $row[9];
  $totals['Subtotal'] += $row[10];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DocuBills - World's 1st FREE Custom Invoice Generator</title>
  <meta name="description" content="Upload Excel/Google Sheets, choose your own price columns, create unlimited custom invoices, accept payments via Stripe & bank transfer — 100% FREE!" />
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --accent: #f72585;
      --accent-light: #ff8bba;
      --success: #2fbf71;
      --warning: #f8961e;
      --dark: #0f172a;
      --darker: #020617;
      --light: #f8fafc;
      --muted: #64748b;
      --border: rgba(148,163,184,.25);
      --card: #ffffff;
      --bg: #f5f7fb;
      --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
      --gradient-accent: linear-gradient(135deg, #f72585 0%, #ff8bba 100%);
      --gradient-dark: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
      --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      --shadow-lg: 0 20px 40px rgba(2, 6, 23, 0.15);
      --shadow-md: 0 10px 25px rgba(2, 6, 23, 0.1);
      --radius-xl: 24px;
      --radius-lg: 18px;
      --radius-md: 12px;
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    html { scroll-behavior: smooth; }
    
    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background: #ffffff;
      color: var(--dark);
      overflow-x: hidden;
      line-height: 1.6;
    }
    
    .container {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 24px;
    }
    
    /* Navigation */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1000;
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(148, 163, 184, 0.1);
      padding: 16px 0;
      box-shadow: 0 4px 20px rgba(2, 6, 23, 0.08);
    }
    
    .nav-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 24px;
    }
    
    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      font-weight: 800;
      font-size: 1.5rem;
      color: var(--dark);
      font-family: 'Poppins', sans-serif;
    }
    
    .logo-icon {
      width: 44px;
      height: 44px;
      border-radius: 14px;
      background: var(--gradient-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.25rem;
      box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
    }
    
    .nav-buttons {
      display: flex;
      gap: 12px;
      align-items: center;
    }
    
    .btn {
      padding: 12px 28px;
      border-radius: var(--radius-md);
      font-weight: 600;
      font-size: 0.95rem;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: var(--transition);
      cursor: pointer;
      border: none;
      font-family: 'Inter', sans-serif;
    }
    
    .btn-primary {
      background: var(--gradient-primary);
      color: white;
      box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 25px rgba(67, 97, 238, 0.4);
    }
    
    .btn-secondary {
      background: white;
      color: var(--primary);
      border: 2px solid var(--primary);
    }
    
    .btn-secondary:hover {
      background: var(--primary);
      color: white;
      transform: translateY(-2px);
    }
    
    /* Hero Section */
    .hero {
      padding: 180px 0 80px;
      position: relative;
      overflow: hidden;
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }
    
    .hero::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -20%;
      width: 800px;
      height: 800px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(67, 97, 238, 0.1) 0%, transparent 70%);
      z-index: 0;
    }
    
    .hero-content {
      max-width: 800px;
      margin: 0 auto;
      text-align: center;
      position: relative;
      z-index: 2;
    }
    
    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 12px 24px;
      background: rgba(67, 97, 238, 0.1);
      border: 1px solid rgba(67, 97, 238, 0.2);
      border-radius: 50px;
      margin-bottom: 24px;
      backdrop-filter: blur(10px);
    }
    
    .hero-badge i {
      color: var(--primary);
      font-size: 1.2rem;
    }
    
    .hero-badge span {
      font-weight: 700;
      color: var(--primary);
      font-size: 0.95rem;
    }
    
    .hero-title {
      font-size: 3.5rem;
      font-weight: 800;
      line-height: 1.1;
      margin-bottom: 24px;
      background: linear-gradient(135deg, #0f172a 0%, #4361ee 30%, #f72585 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-family: 'Poppins', sans-serif;
    }
    
    .hero-subtitle {
      font-size: 1.25rem;
      color: var(--muted);
      margin-bottom: 40px;
      max-width: 700px;
      margin-left: auto;
      margin-right: auto;
    }
    
    /* Interactive Demo Section */
    .demo-section {
      padding: 100px 0;
      background: white;
    }
    
    .section-title {
      text-align: center;
      margin-bottom: 60px;
    }
    
    .section-subtitle {
      font-size: 1rem;
      color: var(--primary);
      font-weight: 600;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 2px;
    }
    
    .section-heading {
      font-size: 2.5rem;
      font-weight: 800;
      color: var(--dark);
      margin-bottom: 16px;
      font-family: 'Poppins', sans-serif;
    }
    
    .section-desc {
      font-size: 1.1rem;
      color: var(--muted);
      max-width: 600px;
      margin: 0 auto;
    }
    
    .demo-container {
      background: white;
      border-radius: var(--radius-xl);
      padding: 0;
      box-shadow: var(--shadow-xl);
      margin-top: 40px;
      border: 1px solid rgba(148, 163, 184, 0.1);
      position: relative;
      overflow: hidden;
    }
    
    .demo-tabs {
      display: flex;
      background: #f8fafc;
      border-bottom: 2px solid rgba(148, 163, 184, 0.1);
      position: relative;
    }
    
    .demo-tab {
      flex: 1;
      padding: 20px;
      text-align: center;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      color: var(--muted);
      position: relative;
      border: none;
      background: none;
      font-family: inherit;
      font-size: 1rem;
    }
    
    .demo-tab.active {
      color: var(--primary);
      background: white;
    }
    
    .demo-tab.active::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 100%;
      height: 2px;
      background: var(--primary);
    }
    
    .demo-content {
      min-height: 500px;
      padding: 40px;
    }
    
    /* Step 1: Create Invoice Form */
    .invoice-form {
      max-width: 800px;
      margin: 0 auto;
    }
    
    .form-step {
      display: none;
      animation: fadeIn 0.5s ease;
    }
    
    .form-step.active {
      display: block;
    }
    
    .form-group {
      margin-bottom: 24px;
    }
    
    .form-label {
      display: block;
      font-weight: 600;
      margin-bottom: 8px;
      color: var(--dark);
    }
    
    .form-control {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid rgba(148, 163, 184, 0.3);
      border-radius: var(--radius-md);
      font-size: 1rem;
      transition: var(--transition);
      font-family: 'Inter', sans-serif;
    }
    
    .form-control:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }
    
    .form-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }
    
    .form-section {
      margin-bottom: 40px;
      padding-bottom: 30px;
      border-bottom: 2px solid rgba(148, 163, 184, 0.1);
    }
    
    .form-section-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .data-source-options {
      display: flex;
      gap: 20px;
      margin-bottom: 30px;
      flex-wrap: wrap;
    }
    
    .source-option {
      flex: 1;
      min-width: 200px;
    }
    
    .source-option input[type="radio"] {
      display: none;
    }
    
    .source-option label {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px;
      border: 2px solid rgba(148, 163, 184, 0.2);
      border-radius: var(--radius-lg);
      cursor: pointer;
      transition: var(--transition);
      background: white;
      text-align: center;
    }
    
    .source-option input[type="radio"]:checked + label {
      border-color: var(--primary);
      background: rgba(67, 97, 238, 0.05);
      box-shadow: 0 8px 20px rgba(67, 97, 238, 0.1);
    }
    
    .source-icon {
      width: 60px;
      height: 60px;
      border-radius: 16px;
      background: rgba(67, 97, 238, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 15px;
      font-size: 1.5rem;
      color: var(--primary);
    }
    
    .source-option h4 {
      margin-bottom: 8px;
      color: var(--dark);
    }
    
    .source-option p {
      color: var(--muted);
      font-size: 0.9rem;
    }
    
    .upload-area {
      border: 2px dashed rgba(148, 163, 184, 0.3);
      border-radius: var(--radius-lg);
      padding: 40px;
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
      margin-top: 20px;
      position: relative;
    }
    
    .upload-area.disabled {
      pointer-events: none;
      opacity: 0.6;
      filter: blur(2px);
    }
    
    .upload-area.disabled::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.9);
      z-index: 1;
      border-radius: var(--radius-lg);
    }
    
    .upload-area.disabled .cta-overlay {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 2;
      text-align: center;
      width: 100%;
    }
    
    .upload-area:hover {
      border-color: var(--primary);
      background: rgba(67, 97, 238, 0.02);
    }
    
    .upload-area i {
      font-size: 3rem;
      color: var(--primary);
      margin-bottom: 20px;
    }
    
    .upload-text {
      color: var(--muted);
      margin-bottom: 10px;
    }
    
    .upload-hint {
      font-size: 0.9rem;
      color: var(--muted);
    }
    
    /* Step 2: Price Selection */
    .price-selector {
      max-width: 800px;
      margin: 0 auto;
    }
    
    .price-option {
      padding: 20px;
      border: 2px solid rgba(148, 163, 184, 0.2);
      border-radius: var(--radius-lg);
      margin-bottom: 20px;
      cursor: pointer;
      transition: var(--transition);
      background: white;
    }
    
    .price-option.active {
      border-color: var(--primary);
      background: rgba(67, 97, 238, 0.05);
      box-shadow: 0 8px 20px rgba(67, 97, 238, 0.1);
    }
    
    .price-option-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 15px;
    }
    
    .price-option-header input[type="radio"] {
      width: 20px;
      height: 20px;
    }
    
    .price-option-header strong {
      font-size: 1.1rem;
      color: var(--dark);
    }
    
    .column-options {
      padding: 20px;
      background: rgba(0, 0, 0, 0.02);
      border-radius: var(--radius-md);
      margin-top: 15px;
    }
    
    .column-options label {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px;
      cursor: pointer;
      transition: var(--transition);
      border-radius: var(--radius-md);
    }
    
    .column-options label:hover {
      background: rgba(67, 97, 238, 0.05);
    }
    
    .column-options input[type="radio"] {
      width: 18px;
      height: 18px;
    }
    
    .columns-picker {
      margin-top: 30px;
      padding: 20px;
      background: rgba(0, 0, 0, 0.02);
      border-radius: var(--radius-lg);
    }
    
    .columns-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      margin-top: 15px;
    }
    
    .column-checkbox {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .column-checkbox input[type="checkbox"] {
      width: 18px;
      height: 18px;
    }
    
    /* Data Preview */
    .data-preview {
      background: white;
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      margin-top: 30px;
      transition: var(--transition);
    }
    
    .data-preview.hidden {
      display: none;
    }
    
    .data-preview-header {
      background: #f8fafc;
      padding: 15px;
      border-bottom: 1px solid rgba(148, 163, 184, 0.2);
      font-weight: 600;
      color: var(--dark);
    }
    
    .data-preview-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .data-preview-table th {
      background: rgba(67, 97, 238, 0.1);
      padding: 12px;
      text-align: left;
      font-weight: 600;
      color: var(--dark);
      border-bottom: 2px solid rgba(148, 163, 184, 0.2);
    }
    
    .data-preview-table td {
      padding: 12px;
      border-bottom: 1px solid rgba(148, 163, 184, 0.1);
    }
    
    .data-preview-table tr:hover {
      background: rgba(67, 97, 238, 0.03);
    }
    
    /* Demo Controls */
    .demo-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 40px;
      padding-top: 30px;
      border-top: 2px solid rgba(148, 163, 184, 0.1);
    }
    
    .demo-progress {
      display: flex;
      align-items: center;
      gap: 10px;
      color: var(--muted);
      font-weight: 600;
    }
    
    .progress-steps {
      display: flex;
      gap: 5px;
    }
    
    .progress-step {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: rgba(148, 163, 184, 0.3);
    }
    
    .progress-step.active {
      background: var(--primary);
    }
    
    /* CTA Section */
    .cta-section {
      background: var(--gradient-dark);
      border-radius: var(--radius-xl);
      padding: 80px 40px;
      text-align: center;
      color: white;
      margin-top: 80px;
      position: relative;
      overflow: hidden;
    }
    
    .cta-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at top right, rgba(67, 97, 238, 0.3) 0%, transparent 50%),
                  radial-gradient(circle at bottom left, rgba(247, 37, 133, 0.2) 0%, transparent 50%);
    }
    
    .cta-title {
      font-size: 2.5rem;
      font-weight: 800;
      margin-bottom: 20px;
      position: relative;
      z-index: 2;
      font-family: 'Poppins', sans-serif;
    }
    
    .cta-subtitle {
      font-size: 1.1rem;
      color: rgba(255, 255, 255, 0.8);
      max-width: 600px;
      margin: 0 auto 40px;
      position: relative;
      z-index: 2;
    }
    
    .cta-features {
      display: flex;
      justify-content: center;
      gap: 30px;
      margin-bottom: 40px;
      flex-wrap: wrap;
    }
    
    .cta-feature {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.95rem;
    }
    
    .cta-feature i {
      color: var(--accent-light);
    }
    
    /* Features Section */
    .features-section {
      padding: 100px 0;
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }
    
    .features-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 32px;
      margin-top: 60px;
    }
    
    .feature-card {
      background: white;
      border-radius: var(--radius-lg);
      padding: 32px;
      box-shadow: var(--shadow-md);
      border: 1px solid rgba(148, 163, 184, 0.1);
      transition: var(--transition);
    }
    
    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-lg);
    }
    
    .feature-icon {
      width: 60px;
      height: 60px;
      border-radius: 16px;
      background: rgba(67, 97, 238, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
      font-size: 1.5rem;
      color: var(--primary);
    }
    
    .feature-card h3 {
      font-size: 1.25rem;
      font-weight: 700;
      margin-bottom: 12px;
      color: var(--dark);
    }
    
    .feature-card p {
      color: var(--muted);
      line-height: 1.6;
    }
    
    /* Footer */
    .footer {
      background: var(--darker);
      color: white;
      padding: 80px 0 40px;
      margin-top: 100px;
    }
    
    .footer-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 48px;
      margin-bottom: 48px;
    }
    
    .footer-col h3 {
      font-size: 1.1rem;
      font-weight: 700;
      margin-bottom: 24px;
      color: white;
    }
    
    .footer-links {
      list-style: none;
    }
    
    .footer-links li {
      margin-bottom: 12px;
    }
    
    .footer-links a {
      color: rgba(255, 255, 255, 0.6);
      text-decoration: none;
      transition: var(--transition);
    }
    
    .footer-links a:hover {
      color: white;
      padding-left: 4px;
    }
    
    /* Responsive Design */
    @media (max-width: 1024px) {
      .features-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .footer-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    
    @media (max-width: 768px) {
      .hero-title {
        font-size: 2.5rem;
      }
      
      .section-heading {
        font-size: 2rem;
      }
      
      .features-grid,
      .footer-grid {
        grid-template-columns: 1fr;
      }
      
      .form-grid {
        grid-template-columns: 1fr;
      }
      
      .data-source-options {
        flex-direction: column;
      }
      
      .columns-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .demo-content {
        padding: 20px;
      }
    }
    
    @media (max-width: 480px) {
      .hero {
        padding: 140px 0 60px;
      }
      
      .hero-title {
        font-size: 2rem;
      }
      
      .section {
        padding: 60px 0;
      }
    }
    
    /* Animations */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .reveal {
      opacity: 0;
      transform: translateY(30px);
      transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .reveal.active {
      opacity: 1;
      transform: translateY(0);
    }
    
    /* Success State */
    .success-state {
      text-align: center;
      padding: 40px;
      animation: fadeIn 0.5s ease;
    }
    
    .success-icon {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: rgba(47, 191, 113, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      color: var(--success);
      font-size: 2rem;
    }
    
    .success-state h3 {
      font-size: 1.5rem;
      margin-bottom: 10px;
      color: var(--dark);
    }
    
    .success-state p {
      color: var(--muted);
      margin-bottom: 30px;
    }
    
    /* Blurred Invoice Preview */
    .blurred-preview {
      position: relative;
      max-width: 600px;
      margin: 30px auto;
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .blurred-preview .invoice-image {
      width: 100%;
      display: block;
      filter: blur(8px);
      -webkit-filter: blur(8px);
    }
    
    .blurred-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.7);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px;
      text-align: center;
      color: white;
    }
    
    .blurred-overlay h4 {
      font-size: 1.5rem;
      margin-bottom: 15px;
      color: white;
    }
    
    .blurred-overlay p {
      color: rgba(255, 255, 255, 0.8);
      margin-bottom: 25px;
      max-width: 500px;
    }
    
    /* Locked Feature */
    .locked-feature {
      position: relative;
    }
    
    .locked-feature::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.9);
      border-radius: var(--radius-lg);
      z-index: 1;
    }
    
    .lock-overlay {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 2;
      text-align: center;
      width: 100%;
    }
    
    .lock-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: var(--gradient-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 15px;
      color: white;
      font-size: 1.5rem;
    }
    
    /* Price Calculation Preview Updates */
    .calculation-update {
      animation: highlight 1s ease;
    }
    
    @keyframes highlight {
      0% { background-color: rgba(67, 97, 238, 0); }
      50% { background-color: rgba(67, 97, 238, 0.2); }
      100% { background-color: rgba(67, 97, 238, 0); }
    }
    
    /* Real-time Updates */
    .real-time-update {
      transition: all 0.3s ease;
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar" id="navbar">
    <div class="nav-container">
      <a href="#home" class="logo">
        <div class="logo-icon">
          <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <span>DocuBills</span>
      </a>
      
      <div class="nav-buttons">
        <?php if ($isLoggedIn): ?>
          <a href="history.php" class="btn btn-primary">
            <i class="fas fa-tachometer-alt"></i> Dashboard
          </a>
        <?php else: ?>
          <a href="login.php" class="btn btn-secondary">Login</a>
          <a href="#demo" class="btn btn-primary">Try Demo</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero" id="home">
    <div class="container">
      <div class="hero-content">
        <div class="hero-badge">
          <i class="fas fa-crown"></i>
          <span>World's 1st & Only FREE Custom Invoice Generator</span>
        </div>
        
        <h1 class="hero-title">
          Your Excel Data → Professional Invoice
          <br>
          <span style="color: var(--accent);">Choose ANY Column as Price!</span>
        </h1>
        
        <p class="hero-subtitle">
          Break free from rigid templates! Upload your Excel/Google Sheets, choose which column is the price, 
          create unlimited custom invoices, accept payments via Stripe & bank transfer, and automate everything — 
          <strong>100% FREE Forever!</strong>
        </p>
        
        <div class="hero-buttons">
          <?php if ($isLoggedIn): ?>
            <a href="create-invoice.php" class="btn btn-primary" style="padding: 16px 40px; font-size: 1.1rem;">
              <i class="fas fa-plus-circle"></i> Create New Invoice
            </a>
          <?php else: ?>
            <a href="#demo" class="btn btn-primary" style="padding: 16px 40px; font-size: 1.1rem;">
              <i class="fas fa-play-circle"></i> Try Interactive Demo
            </a>
            <a href="#demo" class="btn btn-secondary" style="padding: 16px 40px; font-size: 1.1rem;">
              <i class="fas fa-chart-line"></i> See How It Works
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Interactive Demo Section -->
  <section class="demo-section" id="demo">
    <div class="container">
      <div class="section-title">
        <div class="section-subtitle">Interactive Demo</div>
        <h2 class="section-heading">See DocuBills in Action</h2>
        <p class="section-desc">
          Experience our unique workflow. No signup required to try this demo!
        </p>
      </div>

      <div class="demo-container">
        <div class="demo-tabs">
          <button class="demo-tab active" data-step="1">
            <i class="fas fa-user"></i> Step 1: Client Info
          </button>
          <button class="demo-tab" data-step="2">
            <i class="fas fa-database"></i> Step 2: Data Source
          </button>
          <button class="demo-tab" data-step="3">
            <i class="fas fa-money-bill-wave"></i> Step 3: Price Selection
          </button>
          <button class="demo-tab" data-step="4">
            <i class="fas fa-file-invoice"></i> Preview Invoice
          </button>
        </div>
        
        <div class="demo-content">
          <!-- Step 1: Client Information -->
          <div class="form-step active" id="step1">
            <div class="invoice-form">
              <div class="form-section">
                <h3 class="form-section-title">
                  <i class="fas fa-building"></i> Bill To Information
                </h3>
                
                <div class="form-grid">
                  <div class="form-group">
                    <label class="form-label">Company Name *</label>
                    <input type="text" class="form-control" id="demoCompany" value="Acme Corporation" placeholder="Enter company name">
                  </div>
                  
                  <div class="form-group">
                    <label class="form-label">Contact Name</label>
                    <input type="text" class="form-control" id="demoContact" value="John Smith" placeholder="Contact person's name">
                  </div>
                  
                  <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" class="form-control" id="demoEmail" value="john@acmecorp.com" placeholder="Email address">
                  </div>
                  
                  <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="demoPhone" value="+1 (555) 123-4567" placeholder="Phone number">
                  </div>
                </div>
              </div>
            </div>
            
            <div class="demo-controls">
              <div class="demo-progress">
                <span>Step 1 of 4</span>
                <div class="progress-steps">
                  <span class="progress-step active"></span>
                  <span class="progress-step"></span>
                  <span class="progress-step"></span>
                  <span class="progress-step"></span>
                </div>
              </div>
              
              <button class="btn btn-primary" onclick="nextStep(2)">
                Continue to Data Source <i class="fas fa-arrow-right"></i>
              </button>
            </div>
          </div>
          
          <!-- Step 2: Data Source -->
          <div class="form-step" id="step2">
            <div class="invoice-form">
              <div class="form-section">
                <h3 class="form-section-title">
                  <i class="fas fa-database"></i> Invoice Data Source
                </h3>
                
                <p style="margin-bottom: 20px; color: var(--muted);">
                  Choose where your invoice data comes from. We support Excel files or Google Sheets.
                </p>
                
                <div class="data-source-options">
                  <div class="source-option">
                    <input type="radio" name="dataSource" id="googleSource" checked>
                    <label for="googleSource">
                      <div class="source-icon">
                        <i class="fab fa-google"></i>
                      </div>
                      <h4>Google Sheet URL</h4>
                      <p>Connect to your Google Sheets</p>
                    </label>
                  </div>
                  
                  <div class="source-option">
                    <input type="radio" name="dataSource" id="uploadSource">
                    <label for="uploadSource">
                      <div class="source-icon">
                        <i class="fas fa-file-excel"></i>
                      </div>
                      <h4>Upload Excel File</h4>
                      <p>Upload .xls or .xlsx files</p>
                    </label>
                  </div>
                </div>
                
                <div id="googleSection">
                  <div class="form-group">
                    <label class="form-label">Google Sheet URL</label>
                    <input type="url" class="form-control" value="https://docs.google.com/spreadsheets/d/1Ycvqhlvi1orFHDAzDe4RfgQh94BlIPDKzlR671vDEGo/edit?usp=sharing" placeholder="https://docs.google.com/spreadsheets/..." readonly>
                    <p style="margin-top: 8px; font-size: 0.9rem; color: var(--muted);">
                      <i class="fas fa-check-circle" style="color: var(--success);"></i> This Google Sheet is connected and ready to use
                    </p>
                  </div>
                </div>
                
                <div id="uploadSection" style="display: none;">
                  <div class="upload-area disabled" id="uploadArea">
                    <i class="fas fa-file-excel"></i>
                    <p class="upload-text">Drag & drop your Excel file here or click to browse</p>
                    <p class="upload-hint">Supports .xls and .xlsx formats</p>
                    <input type="file" id="demoFileUpload" accept=".xls,.xlsx" style="display: none;">
                    
                    <div class="cta-overlay">
                      <div class="lock-icon">
                        <i class="fas fa-lock"></i>
                      </div>
                      <h4 style="color: var(--primary); margin-bottom: 10px;">Register to Upload Excel Files</h4>
                      <p style="color: var(--muted); margin-bottom: 20px;">Sign up for free to upload your own Excel files</p>
                      <a href="register.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Sign Up Free
                      </a>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Data Preview -->
              <div class="data-preview" id="dataPreview">
                <div class="data-preview-header">
                  <i class="fas fa-table"></i> Data Preview (from Google Sheet)
                </div>
                <table class="data-preview-table">
                  <thead>
                    <tr>
                      <?php foreach ($sheetColumns as $column): ?>
                        <th><?php echo htmlspecialchars($column); ?></th>
                      <?php endforeach; ?>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($sheetData as $row): ?>
                      <tr>
                        <?php foreach ($row as $index => $value): ?>
                          <td>
                            <?php 
                            if ($index == 9 || $index == 10) {
                              echo '$' . number_format((float)$value, 2);
                            } else {
                              echo htmlspecialchars($value);
                            }
                            ?>
                          </td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            
            <div class="demo-controls">
              <div class="demo-progress">
                <span>Step 2 of 4</span>
                <div class="progress-steps">
                  <span class="progress-step active"></span>
                  <span class="progress-step active"></span>
                  <span class="progress-step"></span>
                  <span class="progress-step"></span>
                </div>
              </div>
              
              <div style="display: flex; gap: 10px;">
                <button class="btn btn-secondary" onclick="prevStep(1)">
                  <i class="fas fa-arrow-left"></i> Back
                </button>
                <button class="btn btn-primary" onclick="nextStep(3)">
                  Continue to Price Selection <i class="fas fa-arrow-right"></i>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Step 3: Price Selection -->
          <div class="form-step" id="step3">
            <div class="price-selector">
              <div class="form-section">
                <h3 class="form-section-title">
                  <i class="fas fa-money-bill-wave"></i> Configure Invoice Pricing
                </h3>
                
                <p style="margin-bottom: 20px; color: var(--muted);">
                  Choose how to calculate the total amount for your invoice.
                </p>
                
                <div class="price-option active" id="autoPriceOption">
                  <div class="price-option-header">
                    <input type="radio" name="priceMode" id="autoPrice" checked>
                    <strong>Automatic Pricing - Use a column from my data</strong>
                  </div>
                  
                  <div class="column-options">
                    <p style="margin-bottom: 15px; font-weight: 600;">Select which column contains item prices:</p>
                    <?php 
                    // Show only the specified columns
                    $priceColumns = ['Unit Price', 'Subtotal'];
                    foreach ($priceColumns as $column): 
                    ?>
                      <label>
                        <input type="radio" name="priceColumn" value="<?php echo $column; ?>" <?php echo $column === 'Subtotal' ? 'checked' : ''; ?> data-total="<?php echo $totals[$column]; ?>">
                        Column: <strong><?php echo $column; ?></strong>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </div>
                
                <div class="price-option" id="manualPriceOption">
                  <div class="price-option-header">
                    <input type="radio" name="priceMode" id="manualPrice">
                    <strong>Manual Pricing - I'll enter the total invoice amount myself</strong>
                  </div>
                  <div style="margin-top: 15px; padding: 15px; background: #fff8e6; border-left: 4px solid var(--warning); border-radius: 4px;">
                    <i class="fas fa-info-circle"></i> You'll enter the total amount on the next screen
                  </div>
                </div>
                
                <div class="columns-picker">
                  <h4 style="margin-bottom: 15px;">
                    <i class="fas fa-columns"></i> Columns to Include
                    <small style="font-weight: 400; margin-left: 10px; color: var(--muted);">(max 15)</small>
                  </h4>
                  
                  <div class="columns-grid" id="columnsGrid">
                    <?php foreach ($sheetColumns as $index => $column): ?>
                      <div class="column-checkbox">
                        <input type="checkbox" id="col_<?php echo $index; ?>" <?php echo in_array($column, ['Description of Goods', 'Unit Price', 'Subtotal']) ? 'checked' : ''; ?> data-column="<?php echo htmlspecialchars($column); ?>">
                        <label for="col_<?php echo $index; ?>"><?php echo htmlspecialchars($column); ?></label>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
              
              <!-- Price Calculation Preview -->
              <div class="data-preview">
                <div class="data-preview-header">
                  <i class="fas fa-calculator"></i> Price Calculation Preview
                </div>
                <table class="data-preview-table" id="priceCalculationTable">
                  <thead>
                    <tr>
                      <th>Description of Goods</th>
                      <th id="selectedPriceHeader">Selected Price Column</th>
                      <th>Calculated Amount</th>
                    </tr>
                  </thead>
                  <tbody id="priceCalculationBody">
                    <!-- Filled by JavaScript -->
                  </tbody>
                  <tfoot>
                    <tr style="background: rgba(67, 97, 238, 0.05); font-weight: 600;">
                      <td colspan="2" style="text-align: right;">Total Invoice Amount:</td>
                      <td id="totalAmount">$<?php echo number_format($totals['Subtotal'], 2); ?></td>
                    </tr>
                  </tfoot>
                </table>
                <div style="padding: 15px; background: rgba(47, 191, 113, 0.1); border-top: 1px solid rgba(47, 191, 113, 0.2);">
                  <p style="margin: 0; color: var(--success); font-weight: 600;">
                    <i class="fas fa-lightbulb"></i> Pro Tip: You can select ANY column as the price column when you sign up!
                  </p>
                </div>
              </div>
            </div>
            
            <div class="demo-controls">
              <div class="demo-progress">
                <span>Step 3 of 4</span>
                <div class="progress-steps">
                  <span class="progress-step active"></span>
                  <span class="progress-step active"></span>
                  <span class="progress-step active"></span>
                  <span class="progress-step"></span>
                </div>
              </div>
              
              <div style="display: flex; gap: 10px;">
                <button class="btn btn-secondary" onclick="prevStep(2)">
                  <i class="fas fa-arrow-left"></i> Back
                </button>
                <button class="btn btn-primary" onclick="showPreviewCTA()">
                  Preview Invoice <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Step 4: Preview with CTA -->
          <div class="form-step" id="step4">
            <div class="success-state">
              <div class="success-icon">
                <i class="fas fa-check"></i>
              </div>
              
              <h3>Invoice Configured Successfully!</h3>
              <p>Your invoice has been configured with real data from the Google Sheet. Now preview your professional invoice.</p>
              
              <!-- Blurred Invoice Preview with CTA -->
              <div class="blurred-preview">
                <!-- This would be your invoice image -->
                <div style="background: white; padding: 30px;">
                  <div style="display: flex; justify-content: space-between; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #eee;">
                    <div>
                      <h3 style="margin: 0 0 5px 0; color: var(--primary);">ACME CORPORATION</h3>
                      <p style="margin: 0; color: var(--muted);">INV-2023-001</p>
                    </div>
                    <div style="text-align: right;">
                      <h3 style="margin: 0 0 5px 0; color: var(--dark);">$1,420,772.38</h3>
                      <p style="margin: 0; color: var(--muted);">Total Amount</p>
                    </div>
                  </div>
                  
                  <div style="background: #f8fafc; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
                    <p style="margin: 0 0 10px 0; font-weight: 600; color: var(--dark);">Bill To:</p>
                    <p style="margin: 0; color: var(--muted);" id="finalClientInfo">Acme Corporation<br>John Smith<br>john@acmecorp.com</p>
                  </div>
                  
                  <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                    <thead>
                      <tr style="background: rgba(67, 97, 238, 0.1);">
                        <th style="padding: 12px; text-align: left;">Description of Goods</th>
                        <th style="padding: 12px; text-align: left;">Quantity</th>
                        <th style="padding: 12px; text-align: left;">Unit Price</th>
                        <th style="padding: 12px; text-align: left;">Subtotal</th>
                      </tr>
                    </thead>
                    <tbody style="filter: blur(5px);">
                      <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">High-Performance GPU Servers (Model XZ-900)</td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">12</td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">$12,999.99</td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">$155,999.88</td>
                      </tr>
                      <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">Automated Robotic Arms - Industrial Grade</td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">45</td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">$18,450.50</td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">$830,272.50</td>
                      </tr>
                      <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">5G Network Infrastructure Modules</td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">200</td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">$545.75</td>
                        <td style="padding: 12px; border-bottom: 1px solid #eee;">$109,150.00</td>
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr style="font-weight: 600;">
                        <td colspan="3" style="padding: 12px; text-align: right;">Total:</td>
                        <td style="padding: 12px;">$1,420,772.38</td>
                      </tr>
                    </tfoot>
                  </table>
                  
                  <div style="background: #f8fafc; padding: 20px; border-radius: 10px; border-left: 4px solid var(--primary);">
                    <p style="margin: 0; color: var(--muted);">
                      <i class="fas fa-info-circle"></i> This is a preview. Sign up to generate the full invoice with all details.
                    </p>
                  </div>
                </div>
                
                <div class="blurred-overlay">
                  <div class="lock-icon">
                    <i class="fas fa-lock"></i>
                  </div>
                  <h4>Ready to See Your Complete Invoice?</h4>
                  <p>Sign up now to generate and download your professional invoice with all details visible.</p>
                  
                  <div style="display: flex; gap: 15px; margin-top: 20px; flex-wrap: wrap; justify-content: center;">
                    <a href="register.php" class="btn btn-primary" style="padding: 16px 40px; font-size: 1.1rem;">
                      <i class="fas fa-rocket"></i> Sign Up Free to View
                    </a>
                    <a href="#demo" class="btn" style="background: rgba(255, 255, 255, 0.2); color: white; border: 2px solid white; padding: 16px 30px;">
                      <i class="fas fa-redo"></i> Try Demo Again
                    </a>
                  </div>
                </div>
              </div>
              
              <div style="margin-top: 40px; padding: 30px; background: rgba(67, 97, 238, 0.05); border-radius: var(--radius-lg);">
                <h4 style="margin: 0 0 15px 0; color: var(--dark);">What You Get When You Sign Up:</h4>
                <p style="margin: 0 0 25px 0; color: var(--muted);">
                  This demo shows just the beginning! With DocuBills, you can also:
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 25px;">
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <span>Upload unlimited Excel files</span>
                  </div>
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <span>Connect Google Sheets</span>
                  </div>
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <span>Choose any column as price</span>
                  </div>
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <span>Accept Stripe payments</span>
                  </div>
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <span>Create recurring invoices</span>
                  </div>
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <span>Send email reminders</span>
                  </div>
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                  <a href="register.php" class="btn btn-primary" style="padding: 16px 40px; font-size: 1.1rem;">
                    <i class="fas fa-rocket"></i> Start Free Forever
                  </a>
                  <a href="#demo" class="btn btn-secondary" onclick="restartDemo()" style="padding: 16px 30px;">
                    <i class="fas fa-redo"></i> Try Demo Again
                  </a>
                </div>
                
                <p style="margin-top: 20px; font-size: 0.9rem; color: var(--muted);">
                  <i class="fas fa-lock"></i> No credit card required • 100% Free • Unlimited invoices
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features-section">
    <div class="container">
      <div class="section-title">
        <div class="section-subtitle">Why Choose DocuBills</div>
        <h2 class="section-heading">Everything You Need, 100% Free</h2>
        <p class="section-desc">
          Unlike other invoice generators, DocuBills understands real business data.
        </p>
      </div>

      <div class="features-grid">
        <div class="feature-card reveal">
          <div class="feature-icon">
            <i class="fas fa-columns"></i>
          </div>
          <h3>Choose Your Price Column</h3>
          <p>Not limited to "Item, Name, Description, Qty". Pick ANY column from your Excel as the price column.</p>
        </div>

        <div class="feature-card reveal">
          <div class="feature-icon">
            <i class="fas fa-file-excel"></i>
          </div>
          <h3>Excel & Google Sheets</h3>
          <p>Upload your existing Excel files or connect Google Sheets. No manual data entry needed.</p>
        </div>

        <div class="feature-card reveal">
          <div class="feature-icon">
            <i class="fas fa-credit-card"></i>
          </div>
          <h3>Get Paid Faster</h3>
          <p>Accept payments via Stripe or display your bank details directly on the invoice.</p>
        </div>

        <div class="feature-card reveal">
          <div class="feature-icon">
            <i class="fas fa-sync-alt"></i>
          </div>
          <h3>Recurring Invoices</h3>
          <p>Set up automatic recurring invoices for subscriptions and regular clients.</p>
        </div>

        <div class="feature-card reveal">
          <div class="feature-icon">
            <i class="fas fa-envelope"></i>
          </div>
          <h3>Smart Reminders</h3>
          <p>Custom email reminder cadence with editable templates. Never chase payments manually.</p>
        </div>

        <div class="feature-card reveal">
          <div class="feature-icon">
            <i class="fas fa-palette"></i>
          </div>
          <h3>Custom Templates</h3>
          <p>Choose from multiple professional templates or create your own branded design.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Final CTA -->
  <section class="demo-section">
    <div class="container">
      <div class="cta-section">
        <h2 class="cta-title">Ready to Transform Your Invoicing?</h2>
        <p class="cta-subtitle">
          Join thousands of businesses that have simplified their billing with DocuBills. 
          No credit card required. No hidden fees. Free forever.
        </p>
        
        <div class="cta-features">
          <div class="cta-feature">
            <i class="fas fa-check-circle"></i>
            <span>Unlimited invoices</span>
          </div>
          <div class="cta-feature">
            <i class="fas fa-check-circle"></i>
            <span>100% Free forever</span>
          </div>
          <div class="cta-feature">
            <i class="fas fa-check-circle"></i>
            <span>Excel/Google Sheets upload</span>
          </div>
          <div class="cta-feature">
            <i class="fas fa-check-circle"></i>
            <span>Choose any price column</span>
          </div>
        </div>
        
        <div style="display: flex; gap: 20px; justify-content: center; margin-top: 40px; flex-wrap: wrap;">
          <?php if ($isLoggedIn): ?>
            <a href="create-invoice.php" class="btn" style="background: white; color: var(--dark); padding: 16px 40px; font-size: 1.1rem;">
              <i class="fas fa-plus-circle"></i> Create New Invoice
            </a>
            <a href="history.php" class="btn" style="background: rgba(255, 255, 255, 0.2); color: white; border: 2px solid white; padding: 16px 40px; font-size: 1.1rem;">
              <i class="fas fa-chart-line"></i> View Dashboard
            </a>
          <?php else: ?>
            <a href="register.php" class="btn" style="background: white; color: var(--dark); padding: 18px 48px; font-size: 1.2rem; font-weight: 700;">
              <i class="fas fa-rocket"></i> Start Free Forever
            </a>
            <a href="#demo" class="btn" style="background: rgba(255, 255, 255, 0.2); color: white; border: 2px solid white; padding: 18px 40px; font-size: 1.1rem;">
              <i class="fas fa-play-circle"></i> Try Interactive Demo
            </a>
          <?php endif; ?>
        </div>
        
        <div style="margin-top: 30px; color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;">
          <i class="fas fa-check-circle"></i> No credit card required • Unlimited invoices • Free forever
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <a href="#home" class="logo" style="color: white; margin-bottom: 20px; display: block;">
            <div class="logo-icon">
              <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <span>DocuBills</span>
          </a>
          <p style="color: rgba(255, 255, 255, 0.6); line-height: 1.6;">
            World's first and only free custom invoice generator. Your data, your rules.
          </p>
        </div>

        <div class="footer-col">
          <h3>Product</h3>
          <ul class="footer-links">
            <li><a href="#features">Features</a></li>
            <li><a href="#demo">Interactive Demo</a></li>
            <li><a href="#how-it-works">How It Works</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h3>Company</h3>
          <ul class="footer-links">
            <li><a href="#">About Us</a></li>
            <li><a href="#">Blog</a></li>
            <li><a href="#">Contact</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h3>Legal</h3>
          <ul class="footer-links">
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Terms of Service</a></li>
            <li><a href="#">Cookie Policy</a></li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>© <?php echo date('Y'); ?> DocuBills. The world's first free custom invoice generator. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <script>
    // Demo State Management
    let currentStep = 1;
    let selectedPriceColumn = 'Subtotal';
    let selectedColumns = ['Description of Goods', 'Unit Price', 'Subtotal'];
    const sheetData = <?php echo json_encode($sheetData); ?>;
    const sheetColumns = <?php echo json_encode($sheetColumns); ?>;
    
    // Initialize demo
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize price calculation table
      updatePriceCalculationTable();
      
      // Set up data source toggles
      document.querySelectorAll('input[name="dataSource"]').forEach(radio => {
        radio.addEventListener('change', function() {
          if (this.id === 'googleSource') {
            document.getElementById('googleSection').style.display = 'block';
            document.getElementById('uploadSection').style.display = 'none';
            document.getElementById('dataPreview').classList.remove('hidden');
          } else {
            document.getElementById('googleSection').style.display = 'none';
            document.getElementById('uploadSection').style.display = 'block';
            document.getElementById('dataPreview').classList.add('hidden');
          }
        });
      });
      
      // Set up price mode toggles
      document.querySelectorAll('input[name="priceMode"]').forEach(radio => {
        radio.addEventListener('change', function() {
          if (this.id === 'autoPrice') {
            document.getElementById('autoPriceOption').classList.add('active');
            document.getElementById('manualPriceOption').classList.remove('active');
            document.querySelectorAll('.column-options input[type="radio"]').forEach(input => {
              input.disabled = false;
            });
          } else {
            document.getElementById('autoPriceOption').classList.remove('active');
            document.getElementById('manualPriceOption').classList.add('active');
            document.querySelectorAll('.column-options input[type="radio"]').forEach(input => {
              input.disabled = true;
            });
          }
        });
      });
      
      // Set up price column selection
      document.querySelectorAll('input[name="priceColumn"]').forEach(radio => {
        radio.addEventListener('change', function() {
          selectedPriceColumn = this.value;
          updatePriceCalculationTable();
        });
      });
      
      // Set up columns to include checkboxes
      document.querySelectorAll('.columns-grid input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
          const column = this.getAttribute('data-column');
          if (this.checked) {
            if (!selectedColumns.includes(column)) {
              selectedColumns.push(column);
            }
          } else {
            selectedColumns = selectedColumns.filter(col => col !== column);
          }
          updatePriceCalculationTable();
        });
      });
      
      // Scroll animations
      const revealElements = document.querySelectorAll('.reveal');
      
      const revealOnScroll = () => {
        revealElements.forEach(element => {
          const elementTop = element.getBoundingClientRect().top;
          const windowHeight = window.innerHeight;
          
          if (elementTop < windowHeight - 100) {
            element.classList.add('active');
          }
        });
      };
      
      window.addEventListener('scroll', revealOnScroll);
      revealOnScroll();
    });
    
    // Update price calculation table
    function updatePriceCalculationTable() {
      const tbody = document.getElementById('priceCalculationBody');
      const totalCell = document.getElementById('totalAmount');
      const headerCell = document.getElementById('selectedPriceHeader');
      
      if (!tbody) return;
      
      tbody.innerHTML = '';
      
      // Update header
      headerCell.textContent = selectedPriceColumn;
      
      // Find the index of selected price column
      const priceColumnIndex = sheetColumns.indexOf(selectedPriceColumn);
      const descriptionIndex = sheetColumns.indexOf('Description of Goods');
      
      let total = 0;
      
      // Add rows for each data item
      sheetData.forEach(row => {
        const priceValue = parseFloat(row[priceColumnIndex]) || 0;
        total += priceValue;
        
        const tr = document.createElement('tr');
        tr.className = 'real-time-update';
        
        const descriptionTd = document.createElement('td');
        descriptionTd.textContent = row[descriptionIndex];
        
        const priceColumnTd = document.createElement('td');
        priceColumnTd.innerHTML = `<strong>${selectedPriceColumn}</strong>`;
        
        const amountTd = document.createElement('td');
        amountTd.textContent = '$' + priceValue.toFixed(2);
        
        tr.appendChild(descriptionTd);
        tr.appendChild(priceColumnTd);
        tr.appendChild(amountTd);
        tbody.appendChild(tr);
      });
      
      // Update total
      totalCell.textContent = '$' + total.toFixed(2);
      totalCell.classList.add('calculation-update');
      setTimeout(() => totalCell.classList.remove('calculation-update'), 1000);
    }
    
    // Navigation functions
    function nextStep(step) {
      // Update client info in preview
      if (step === 4) {
        const company = document.getElementById('demoCompany').value || 'Acme Corporation';
        const contact = document.getElementById('demoContact').value || 'John Smith';
        const email = document.getElementById('demoEmail').value || 'john@acmecorp.com';
        document.getElementById('finalClientInfo').textContent = `${company}\n${contact}\n${email}`;
      }
      
      // Hide current step
      document.getElementById(`step${currentStep}`).classList.remove('active');
      document.querySelector(`.demo-tab[data-step="${currentStep}"]`).classList.remove('active');
      
      // Show new step
      document.getElementById(`step${step}`).classList.add('active');
      document.querySelector(`.demo-tab[data-step="${step}"]`).classList.add('active');
      
      currentStep = step;
      
      // Scroll to top of demo
      document.querySelector('.demo-content').scrollTop = 0;
    }
    
    function showPreviewCTA() {
      // Show CTA modal before proceeding to preview
      const modal = document.createElement('div');
      modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        animation: fadeIn 0.3s ease;
      `;
      
      modal.innerHTML = `
        <div style="background: white; border-radius: var(--radius-lg); padding: 40px; max-width: 500px; width: 90%; text-align: center; animation: slideUp 0.3s ease;">
          <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(67, 97, 238, 0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
            <i class="fas fa-lock" style="font-size: 2rem; color: var(--primary);"></i>
          </div>
          <h3 style="margin-bottom: 10px; color: var(--dark);">Ready to Preview Your Invoice?</h3>
          <p style="color: var(--muted); margin-bottom: 25px;">Sign up for free to see your complete professional invoice with all details.</p>
          
          <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px; flex-wrap: wrap;">
            <a href="register.php" class="btn" style="background: var(--gradient-primary); color: white; padding: 14px 32px; border-radius: var(--radius-md); text-decoration: none; font-weight: 600;">
              <i class="fas fa-rocket"></i> Sign Up Free to Preview
            </a>
            <button onclick="closeModalAndProceed()" style="background: white; color: var(--primary); border: 2px solid var(--primary); padding: 14px 32px; border-radius: var(--radius-md); font-weight: 600; cursor: pointer;">
              Continue Demo
            </button>
          </div>
          
          <p style="margin-top: 20px; font-size: 0.9rem; color: var(--muted);">
            <i class="fas fa-check-circle"></i> No credit card required
          </p>
        </div>
      `;
      
      document.body.appendChild(modal);
      
      // Add animations
      const style = document.createElement('style');
      style.textContent = `
        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }
        @keyframes slideUp {
          from { transform: translateY(20px); opacity: 0; }
          to { transform: translateY(0); opacity: 1; }
        }
      `;
      document.head.appendChild(style);
    }
    
    function closeModalAndProceed() {
      document.querySelector('div[style*="position: fixed"]').remove();
      nextStep(4);
    }
    
    function prevStep(step) {
      nextStep(step);
    }
    
    function restartDemo() {
      currentStep = 1;
      
      // Reset all steps
      document.querySelectorAll('.form-step').forEach(step => {
        step.classList.remove('active');
      });
      
      document.querySelectorAll('.demo-tab').forEach(tab => {
        tab.classList.remove('active');
      });
      
      // Show first step
      document.getElementById('step1').classList.add('active');
      document.querySelector('.demo-tab[data-step="1"]').classList.add('active');
      
      // Scroll to demo section
      document.getElementById('demo').scrollIntoView({ behavior: 'smooth' });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          window.scrollTo({
            top: targetElement.offsetTop - 80,
            behavior: 'smooth'
          });
        }
      });
    });
    
    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    
    window.addEventListener('scroll', () => {
      if (window.scrollY > 50) {
        navbar.style.boxShadow = '0 4px 20px rgba(2, 6, 23, 0.08)';
      } else {
        navbar.style.boxShadow = '0 4px 20px rgba(2, 6, 23, 0.08)';
      }
    });
    
    // Demo tabs click handlers
    document.querySelectorAll('.demo-tab').forEach(tab => {
      tab.addEventListener('click', function() {
        const step = parseInt(this.getAttribute('data-step'));
        if (step !== currentStep) {
          nextStep(step);
        }
      });
    });
  </script>
</body>
</html>