

<?php $__env->startSection('content'); ?>

    <!-- Header -->
    <header id="header">
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo">
                    <img src="<?php echo e(asset('homepage/images/docubills-logo.png')); ?>" alt="Docubills - Your paperwork, made simple">
                </a>
                
                <ul class="nav-links">
                    <li><a href="#features" class="active">Features</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#demo">Interactive Demo</a></li>
                </ul>
                
                <div class="nav-actions">
                    <?php if(auth()->guard()->check()): ?>
                        <?php
                            $user = Auth::user();
                            $userName = $user->full_name ?? $user->username ?? 'User';
                            $initials = '';
                            foreach (explode(' ', $userName) as $word) {
                                $word = trim($word);
                                if ($word !== '') {
                                    $initials .= strtoupper(substr($word, 0, 1));
                                }
                            }
                        ?>
                        <div class="landing-user-profile" id="landingUserProfile">
                            <div class="landing-user-avatar">
                                <?php if($user->avatar): ?>
                                    <?php
                                        $avatarUrl = $user->avatar;
                                        // If it's already a full URL, use it; otherwise use asset()
                                        if (!preg_match('#^https?://#i', $avatarUrl)) {
                                            $avatarUrl = asset(ltrim($avatarUrl, '/'));
                                        }
                                    ?>
                                    <img src="<?php echo e($avatarUrl); ?>?v=<?php echo e(time()); ?>" alt="Avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <span style="display:none;"><?php echo e($initials); ?></span>
                                <?php else: ?>
                                    <?php echo e($initials); ?>

                                <?php endif; ?>
                            </div>
                            <span class="landing-user-name"><?php echo e($userName); ?></span>
                            <div class="landing-profile-menu" id="landingProfileMenu">
                                <?php if(has_permission('view_dashboard')): ?>
                                    <a href="<?php echo e(route('dashboard')); ?>"><i class="fas fa-chart-line"></i> Dashboard</a>
                                <?php endif; ?>
                                <a href="<?php echo e(route('clients.index')); ?>"><i class="fas fa-users"></i> Clients</a>
                                <form method="POST" action="<?php echo e(route('logout')); ?>" style="display: inline;">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" style="background: none; border: none; width: 100%; text-align: left; padding: 0.75rem 1rem; color: var(--text-dark); cursor: pointer; display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" class="btn btn-secondary" id="signInBtn">Sign In</a>
                        <a href="#cta" class="btn btn-primary">Get Started Free</a>
                    <?php endif; ?>
                </div>
                
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
        </div>
    </header>
    
    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileOverlay"></div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="hero-badge">
                        <i class="fas fa-bolt"></i>
                        <span>3 steps. No mapping. Full control.</span>
                    </div>

                    <h1>Generate Custom Invoices in <span>3 Simple Steps</span></h1>
                    <p>The only invoice generator that eliminates column mapping complexity. Upload your spreadsheet, select columns, and get professional invoices instantly. No hassle, no confusion.</p>
                    
                    <div class="hero-actions">
                        <a href="#cta" class="btn btn-primary btn-lg">
                            <i class="fas fa-rocket"></i>
                            Start Free Trial
                        </a>
                        <a href="#demo" class="btn btn-secondary btn-lg">
                            <i class="fas fa-mouse-pointer"></i>
                            Try Interactive Demo
                        </a>
                    </div>
                    
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-value">3-Step</div>
                            <div class="stat-label">Process</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">2,500+</div>
                            <div class="stat-label">Businesses</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">14-Day</div>
                            <div class="stat-label">Free Trial</div>
                        </div>
                    </div>
                </div>
                
                <div class="hero-image">
                    <img id="heroImage" src="<?php echo e(asset('homepage/images/hero.png')); ?>" alt="Invoice Dashboard Preview" class="dashboard-preview">
                    
                    <div class="floating-row">
                        <div class="floating-card floating-card-2">
                            <div class="floating-icon">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 5px 0; font-size: 16px;">Automated Follow-ups</h4>
                                <p style="font-size: 14px; margin: 0; color: var(--text-muted);">Set custom reminders</p>
                            </div>
                        </div>
                    
                        <div class="floating-card floating-card-1">
                            <div class="floating-icon">
                                <i class="fas fa-file-upload"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 5px 0; font-size: 16px;">Upload & Generate</h4>
                                <p style="font-size: 14px; margin: 0; color: var(--text-muted);">Excel or Google Sheets</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-title">
                <h2>Everything You Need for Effortless Invoicing</h2>
                <p>DocuBills combines simplicity with powerful features to streamline your billing process from start to finish</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-upload"></i>
                    </div>
                    <h3>Smart Upload & Generate</h3>
                    <p>Upload Excel or Google Sheets via URL. Our smart system automatically detects columns and calculates invoice totals without any complex mapping.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> No complex column mapping</li>
                        <li><i class="fas fa-check-circle"></i> Auto column detection</li>
                        <li><i class="fas fa-check-circle"></i> Support for multiple formats</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-columns"></i>
                    </div>
                    <h3>Select & Deselect</h3>
                    <p>Easily include or exclude columns and rows from your final invoice. Perfect for removing accidental entries or sensitive data.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Column/row selection</li>
                        <li><i class="fas fa-check-circle"></i> Real-time preview</li>
                        <li><i class="fas fa-check-circle"></i> Data cleanup tools</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h3>Recurring & Customizable</h3>
                    <p>Set up recurring invoices, add banking info, select currency symbols, and customize every detail directly from the preview page.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Recurring invoice scheduling</li>
                        <li><i class="fas fa-check-circle"></i> Multi-currency support</li>
                        <li><i class="fas fa-check-circle"></i> Banking details integration</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <h3>Automated Follow-ups</h3>
                    <p>Set custom cadences for invoice reminders and write email content right in the app. Never chase clients manually again.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Custom reminder schedules</li>
                        <li><i class="fas fa-check-circle"></i> Email template editor</li>
                        <li><i class="fas fa-check-circle"></i> CC/BCC controls</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Centralized Management</h3>
                    <p>Manage all invoices in one dashboard. Update statuses with one click and automatically notify relevant parties.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Unified invoice dashboard</li>
                        <li><i class="fas fa-check-circle"></i> One-click status updates</li>
                        <li><i class="fas fa-check-circle"></i> Automated notifications</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Admin Control Center</h3>
                    <p>Granular control over every feature with role options and individual permission matrices for complete oversight.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Role-based access control</li>
                        <li><i class="fas fa-check-circle"></i> Permission matrix</li>
                        <li><i class="fas fa-check-circle"></i> Audit logs</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="steps">
        <div class="container">
            <div class="section-title">
                <h2>How It Works: Invoice Generation in 3 Steps</h2>
                <p>Our streamlined process saves you hours of manual work every month</p>
            </div>
            
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Upload Your Data</h3>
                    <p>Upload Excel or connect Google Sheets via URL. No need to manually map columns or reformat your data.</p>
                </div>
                
                <div class="step-connector">
                    <i class="fas fa-arrow-right"></i>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Customize & Preview</h3>
                    <p>Select columns to include, add branding, set currency, and preview the invoice exactly as it will appear.</p>
                </div>
                
                <div class="step-connector">
                    <i class="fas fa-arrow-right"></i>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Send & Manage</h3>
                    <p>Send invoices directly to clients, set up automated reminders, and track payments all in one place.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Demo Section (âœ… 3-Step Tabs) -->
    <section id="demo" class="demo">
        <div class="container">
            <div class="demo-container">
                <div class="demo-header">
                    <h2>Try DocuBills in 3 Steps</h2>
                    <p>Click each step to preview exactly what happens in the real workflow.</p>
    
                    <!-- âœ… Tabs -->
                    <div class="demo-tabs" role="tablist" aria-label="DocuBills 3-step demo">
                        <button class="demo-tab" type="button" role="tab"
                                id="tab-step1" aria-controls="panel-step1" aria-selected="true">
                            <span class="demo-tab-num">1</span> Step 1
                        </button>
    
                        <button class="demo-tab" type="button" role="tab"
                                id="tab-step2" aria-controls="panel-step2" aria-selected="false">
                            <span class="demo-tab-num">2</span> Step 2
                        </button>
    
                        <button class="demo-tab" type="button" role="tab"
                                id="tab-step3" aria-controls="panel-step3" aria-selected="false">
                            <span class="demo-tab-num">3</span> Step 3
                        </button>
                        
                        <button class="demo-reset-btn" type="button" id="demoResetBtn" title="Reset demo to default state">
                            <i class="fas fa-redo"></i> Reset Demo
                        </button>
                    </div>
                </div>
    
                <div class="demo-content">
    
                <!-- âœ… Panel: Step 1 (create-invoice.php) -->
                <div id="panel-step1" class="demo-panel active" role="tabpanel" aria-labelledby="tab-step1">
                  <div class="demo-frame-wrap">
                    <div class="demo-step1-embed" id="demoStep1">
                
                      <div class="demo-app">
                        <div class="app-container">
                
                          <!-- Demo Main -->
                          <div class="main-content">
                            <div class="page-header">
                              <h1 class="page-title">Create New Invoice</h1>
                            </div>
                
                            <form id="demoInvoiceForm" class="demo-form" autocomplete="off">
                
                              <!-- Bill To Section -->
                              <div class="form-section">
                                <h2 class="form-section-title">
                                  <i class="fas fa-building"></i> Bill To Information
                                </h2>
                                <br>
                
                                <div class="form-grid">
                                  <div class="form-group position-relative">
                                    <label for="bill_to_name" class="form-label required">Company Name</label>
                                    <input type="text" id="bill_to_name" name="bill_to_name" class="form-control"
                                           placeholder="Enter company name" required autocomplete="off"
                                           value="Acme Logistics">
                
                                    <!-- Autocomplete suggestions (demo only) -->
                                    <div id="clientSuggestions" class="autocomplete-list" style="display:none;"></div>
                                  </div>
                
                                  <div class="form-group">
                                    <label for="bill_to_rep" class="form-label">Contact Name</label>
                                    <input type="text" id="bill_to_rep" name="bill_to_rep" class="form-control"
                                           placeholder="Contact person's name"
                                           value="Sarah Khan">
                                  </div>
                
                                  <div class="form-group">
                                    <label for="bill_to_address" class="form-label">Address</label>
                                    <input type="text" id="bill_to_address" name="bill_to_address" class="form-control"
                                           placeholder="Full address"
                                           value="Suite 210, 123 Main St, Toronto, ON">
                                  </div>
                
                                  <div class="form-group">
                                    <label for="bill_to_phone" class="form-label">Phone</label>
                                    <input type="text" id="bill_to_phone" name="bill_to_phone" class="form-control"
                                           placeholder="Phone number"
                                           value="+1 647-555-0199">
                                  </div>
                
                                  <div class="form-group">
                                    <label for="bill_to_email" class="form-label required">Email</label>
                                    <input type="email" id="bill_to_email" name="bill_to_email" class="form-control"
                                           placeholder="Email address" required
                                           value="billing@acmelogistics.com">
                                  </div>
                                </div>
                              </div>
                
                              <!-- Data Source Section -->
                              <div class="form-section">
                                <h2 class="form-section-title">
                                  <i class="fas fa-database"></i> Invoice Data Source
                                </h2>
                
                                <div class="form-group">
                                  <label class="form-label">Choose Invoice Source:</label><br>
                                  <label style="margin-right:1em">
                                    <input type="radio" name="invoice_source" value="google" checked>
                                    Google Sheet URL
                                  </label>
                                  <label style="margin-right:1em">
                                    <input type="radio" name="invoice_source" value="upload">
                                    Upload Excel File
                                  </label>
                                </div>
                
                                <!-- Google Section -->
                                <div id="google-section">
                                  <div class="form-group">
                                    <label for="google_sheet_url" class="form-label">Google Sheet URL</label>
                                    <div class="locked-field">
                                      <input type="url" id="google_sheet_url" name="google_sheet_url"
                                             class="form-control is-locked"
                                             placeholder="https://docs.google.com/spreadsheets/..."
                                             value="https://docs.google.com/spreadsheets/d/DEMO-SHEET-ID/edit#gid=0"
                                             readonly aria-readonly="true">
                                      <span class="material-symbols-rounded lock-icon" aria-hidden="true" title="Locked in demo">lock</span>
                                    </div>

                                    <p class="upload-hint">Make sure the Google Sheet is set to "Anyone with the link can view"</p>
                                  </div>
                                </div>
                
                                <!-- Upload Section (hidden by default) -->
                                <div id="upload-section" style="display:none;">
                                  <div class="form-group">
                                    <label class="form-label">Upload Excel File</label>
                                    <div class="upload-container is-locked" id="uploadArea">
                                      <!-- âœ… Overlay + Yellow Padlock -->
                                      <div class="lock-overlay" aria-hidden="true">
                                        <span class="material-symbols-rounded" aria-hidden="true">lock</span>
                                      </div>
                                    
                                      <div class="upload-icon">
                                        <i class="fas fa-file-excel"></i>
                                      </div>
                                    
                                      <p class="upload-text">Drag & drop your Excel file here or click to browse</p>
                                      <p class="upload-hint">Supports .xls and .xlsx formats</p>
                                    
                                      <input type="file" id="excel_file" name="excel_file" accept=".xls,.xlsx" style="display: none;">
                                    </div>
                
                                    <div id="fileNameDemo" style="margin-top: 10px; font-size: 0.9rem; color: var(--primary); display: none;"></div>
                                    <div id="fileErrorDemo" class="error-text" style="display:none;"></div>
                                  </div>
                                </div>
                
                              </div>
                
                              <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-invoice"></i> Create Invoice
                              </button>
                
                              <div class="demo-mini-note">
                                Demo mode: no login, no database, no server calls â€” buttons are for preview only.
                              </div>
                
                            </form>
                          </div>
                        </div>
                      </div>
                
                    </div>
                  </div>
                </div>
    
                <!-- âœ… Panel: Step 2 (Configure Invoice Pricing) -->
                <div id="panel-step2" class="demo-panel" role="tabpanel" aria-labelledby="tab-step2">
                  <div class="demo-frame-wrap">
                    <div class="demo-step2-embed" id="demoStep2">
                
                      <div class="demo-app">
                        <div class="app-container">
                
                          <!-- Demo Main -->
                          <div class="main-content">
                            <div class="page-header">
                              <h1 class="page-title">Configure Invoice Pricing</h1>
                            </div>
                
                            <!-- demo alert (hidden by default) -->
                            <div class="alert demo-step2-alert" style="display:none;"></div>
                
                            <form id="demoPricingForm" autocomplete="off">
                
                                <div class="form-section">
                                  <h2 class="form-section-title"><i class="fas fa-money-bill-wave"></i> Pricing Method</h2>
                
                                  <div class="price-option active" id="demoAutoPriceOption">
                                    <label>
                                      <input type="radio" name="price_mode2" value="column" checked>
                                      <strong>Automatic Pricing</strong> - Use a column from my data
                                    </label>
                
                                    <div class="column-options" id="demoPriceColumns">
                                      <p style="margin-bottom: 1.5rem;">Select which column contains item prices:</p>
                                      <!-- radios injected by JS -->
                                    </div>
                                  </div>
                
                                  <div class="price-option" id="demoManualPriceOption">
                                    <label>
                                      <input type="radio" name="price_mode2" value="manual">
                                      <strong>Manual Pricing</strong> - I'll enter the total invoice amount myself
                                    </label>
                
                                    <div class="manual-notice">
                                      <i class="fas fa-info-circle"></i> You'll enter the total amount on the next screen
                                    </div>
                                  </div>
                                </div>
                
                                <hr>
                
                                <h2 class="form-section-title">
                                  <i class="fas fa-columns"></i> Columns to Include
                                  <small style="font-weight:400; margin-left:1rem; color:var(--gray);">
                                    (max 15)
                                  </small>
                                </h2>
                
                                <div id="demoColumnPicker" class="form-group" style="max-height:300px; overflow:auto;">
                                  <!-- checkboxes injected by JS -->
                                </div>
                
                                <button type="submit" class="btn btn-primary">
                                  Continue to Invoice Preview
                                </button>
                
                                <div class="demo-mini-note">
                                  Demo mode: preview-only â€” no session, no redirect, no database.
                                </div>
                
                            </form>
                
                          </div>
                        </div>
                      </div>
                
                    </div>
                  </div>
                </div>
    
                <!-- âœ… Panel: Step 3 (Invoice Preview Demo) -->
                <div id="panel-step3" class="demo-panel" role="tabpanel" aria-labelledby="tab-step3">
                  <div class="demo-frame-wrap">
                    <div class="demo-step3-embed" id="demoStep3">
                
                      <div class="demo-app demo-step3-skin">
                        <div class="app-container">
                          <div class="main-content">
                
                            <div class="page-header">
                              <div class="page-title">Invoice Preview</div>
                              <div class="page-actions">
                                <button type="button" class="btn" id="demoSaveBtn">
                                  <i class="fas fa-save"></i> Save Invoice
                                </button>
                              </div>
                            </div>
                
                            <div class="invoice-box">
                
                                <!-- Row 1 â€“ logo + Bill-To -->
                                <div class="inv-row-top">
                                  <div class="inv-logo">
                                    <div class="demo-logo-box"><img src="<?php echo e(asset('homepage/images/docubills-logo.png')); ?>" alt="Docubills"></div>
                                  </div>
                                  <div class="inv-billto-title">Bill&nbsp;To:</div>
                                </div>
                
                                <!-- Row 2 â€“ company block vs. client block -->
                                <div class="invoice-header-section">
                                  <div class="company-info" id="demoCompanyInfo"></div>
                                  <div class="bill-to" id="demoBillTo"></div>
                                </div>
                
                                <!-- Invoice Title Bar Color Picker -->
                                <div class="titlebar-picker">
                                  <div class="form-label" style="margin:0;">
                                    <strong>Invoice Title Bar Color (PDF Heading)</strong>
                                  </div>
                
                                  <div class="color-swatch-row" id="demoTitleBarColorRow"></div>
                
                                  <div id="demoInvoiceTitlePreview" class="invoice-title-preview">
                                    INVOICE
                                  </div>
                                </div>
                
                                <!-- Column selector -->
                                <div class="column-toggle-wrapper">
                                  <div class="form-label" style="margin-bottom:6px;"><strong>Columns to include:</strong></div>
                                  <div class="column-toggle-list" id="demoColumnToggles"></div>
                                </div>
                
                                <!-- Invoice Table -->
                                <div class="invoice-table-scroll" id="demoTableScroll">
                                  <table id="demoInvoiceTable">
                                    <thead></thead>
                                    <tbody></tbody>
                                  </table>
                                </div>
                
                                <!-- Total Amount Section -->
                                <div class="flex-container">
                                  <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                      <button type="button" id="demoAddFieldBtn" class="btn">Add Line Item</button>
                                  </div>
                                
                                  <div class="total-display" style="display:flex;justify-content:flex-end;align-items:center;gap:7px;">

                                    <div style="font-weight:700;">Total Amount:</div>
                
                                    <select id="demoCurrencyCode" class="form-control"
                                            style="width:auto; min-width:70px; padding:6px 8px; font-size:14px;">
                                      <!-- options injected by JS -->
                                    </select>
                
                                    <input
                                      type="number"
                                      id="demoManualTotalInput"
                                      class="form-control"
                                      inputmode="decimal"
                                      step="0.01"
                                      min="0"
                                      placeholder="0.00"
                                      style="width:140px; padding:6px 8px; font-size:14px; text-align:right; display:none;"
                                    />
                                    
                                    <span id="demoTotalAmount" style="text-align:right; display:inline-block;">0.00</span>
                                  </div>
                                </div>
                
                                <!-- Stripe limit warning -->
                                <div id="demoStripeWarning" class="stripe-warning hidden">
                                  <i class="fas fa-exclamation-triangle"></i>
                                  <div>
                                    <div><strong>Online payment limit reached</strong></div>
                                    <div style="margin-top:4px;">
                                      Stripe Checkout has a maximum single payment limit of
                                      <strong><span class="currencyPrefix"></span>999,999.99</strong>.<br>
                                      This invoice's total is currently
                                      <strong><span class="currencyPrefix"></span><span id="demoStripeLimitDisplay">0.00</span></strong>,
                                      so your client will <u>not</u> be able to pay via the Pay&nbsp;Now button.
                                    </div>
                                    <label style="display:block; margin-top:8px; font-size:12px;">
                                      <input type="checkbox" id="demoManualOnlyAck">
                                      I understand that Stripe will not be available for this invoice. Create it for manual payment only.
                                    </label>
                                  </div>
                                </div>
                
                                <!-- Date Pickers -->
                                <div class="date-section">
                                  <div class="date-column">
                                    <div class="form-group">
                                      <label class="form-label"><strong>Invoice Date:</strong></label>
                                      <input type="date" id="demoInvoiceDate" class="form-control" required>
                                      <div style="margin-top:6px;">
                                      </div>
                                    </div>
                
                                    <div class="form-group">
                                      <label class="form-label"><strong>Invoice Time:</strong></label>
                                      <input type="time" id="demoInvoiceTime" step="60" class="form-control" required>
                                    </div>
                                  </div>
                
                                  <div class="date-column">
                                    <div class="form-group">
                                      <label class="form-label"><strong>Due Date:</strong></label>
                                      <input type="date" id="demoDueDate" class="form-control" required>
                                    </div>
                
                                    <div class="form-group">
                                      <label style="display:flex;gap:8px;align-items:center;">
                                        <input type="checkbox" id="demoToggleDueTime">
                                        Include Due Time
                                      </label>
                                      <div id="demoDueTimeContainer" style="display:none; margin-top: 8px;">
                                        <label class="form-label"><strong>Due Time:</strong></label>
                                        <input type="time" id="demoDueTime" step="60" class="form-control">
                                      </div>
                                    </div>
                                  </div>
                                </div>
                
                                <!-- Recurring Invoice Toggle -->
                                <div class="recurring-row">
                                  <div class="recurring-row-label">
                                    <strong>Recurring Invoice:</strong>
                                    <span>Send this same amount to the same client every month on this invoice date.</span>
                                  </div>
                                  <button type="button" id="demoRecurringToggle" class="recurring-toggle recurring-off">
                                    <i class="fas fa-sync-alt"></i>
                                    <span id="demoRecurringText">Disabled (One-time)</span>
                                  </button>
                                </div>
                
                                <!-- Banking Details -->
                                <div class="form-group bank-head">
                                  <div>
                                    <label class="form-label"><strong>Banking Details (for this invoice)</strong></label>
                                    <p class="bank-sub">
                                      These fields are pre-filled from Settings â†’ Payment Methods. You can adjust them for this invoice only.
                                    </p>
                                  </div>
                
                                  <label style="font-size:13px; white-space:nowrap; cursor:pointer;">
                                    <input type="checkbox" id="demoToggleBankDetails">
                                    Show on this invoice
                                  </label>
                                </div>
                
                                <div id="demoBankingDrawer" class="bank-drawer">
                                  <div class="date-section">
                                    <div class="date-column">
                                      <div class="form-group">
                                        <label class="form-label">Account Holder Name</label>
                                        <input type="text" id="demoAccountHolder" class="form-control" value="DocuBills Inc.">
                                      </div>
                                      <div class="form-group">
                                        <label class="form-label">Bank Name</label>
                                        <input type="text" id="demoBankName" class="form-control" value="Example Bank">
                                      </div>
                                      <div class="form-group">
                                        <label class="form-label">Account Number</label>
                                        <input type="text" id="demoAccountNumber" class="form-control" value="1234567890">
                                      </div>
                                    </div>

                                    <div class="date-column">
                                      <div class="form-group">
                                        <label class="form-label">IBAN</label>
                                        <input type="text" id="demoIBAN" class="form-control" value="GB29NWBK60161331926819">
                                      </div>
                                      <div class="form-group">
                                        <label class="form-label">SWIFT / BIC</label>
                                        <input type="text" id="demoSWIFT" class="form-control" value="NWBKGB2L">
                                      </div>
                                      <div class="form-group">
                                        <label class="form-label">Routing / Sort Code</label>
                                        <input type="text" id="demoRoutingCode" class="form-control" value="123456">
                                      </div>
                                    </div>
                                  </div>

                                  <div class="form-group">
                                    <label class="form-label">Additional Payment Instructions</label>
                                    <textarea id="demoPaymentInstructions" class="form-control" rows="3">Please include invoice number in reference.</textarea>
                                  </div>
                                </div>

                              </div>
                            
                            <!-- Bottom Save Invoice Button -->
                            <div style="margin-top: 24px; text-align: center;">
                              <button type="button" class="btn btn-save-demo" id="demoSaveBtnBottom">
                                <i class="fas fa-save"></i> Save Invoice
                              </button>
                            </div>
                
                          </div>
                        </div>
                      </div>
                
                    </div>
                  </div>
                </div>

               </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="cta" class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Start Generating Professional Invoices Today</h2>
                <p>Join thousands of businesses that have streamlined their billing process with DocuBills. No credit card required for the free trial.</p>
                
                <div class="cta-badges">
                    <div class="cta-badge">
                        <i class="fas fa-check-circle"></i>
                        <span>14-day free trial</span>
                    </div>
                    <div class="cta-badge">
                        <i class="fas fa-check-circle"></i>
                        <span>No credit card required</span>
                    </div>
                    <div class="cta-badge">
                        <i class="fas fa-check-circle"></i>
                        <span>Cancel anytime</span>
                    </div>
                </div>
                
                <div class="cta-form">
                    <form id="signupForm">
                        <div class="form-group">
                            <input type="email" class="form-input" placeholder="Enter your work email" required>
                            <button type="submit" class="btn btn-accent btn-lg">
                                Start Free Trial <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                    <p style="font-size: 14px; margin-top: 20px; opacity: 0.7;">By signing up, you agree to our Terms of Service and Privacy Policy.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-about">
                    <a href="#" class="footer-logo">
                        <img src="<?php echo e(asset('homepage/images/docubills-logo.png')); ?>" alt="Docubills - Your paperwork, made simple">
                    </a>
                    <p>The simplest way to generate professional invoices from your spreadsheets. Streamline your billing process with our 3-step solution.</p>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Product</h3>
                    <ul class="footer-links">
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#demo">Interactive Demo</a></li>
                        <li><a href="#signup">Sign Up Free</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Company</h3>
                    <ul class="footer-links">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Press</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Support</h3>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">API Reference</a></li>
                        <li><a href="#">Status</a></li>
                        <li><a href="#">Community</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2025 DocuBills. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // âœ… Hero Image Config (change only this one line)
        window.DOCUBILLS = {
            heroImage: "<?php echo e(asset('homepage/images/hero.png')); ?>"
        };

        // âœ… Optional: change image via URL: ?hero=yourfile.jpg
        (function () {
            const img = document.getElementById("heroImage");
            if (!img) return;

            const params = new URLSearchParams(window.location.search);
            const q = params.get("hero");

            let src = (window.DOCUBILLS && window.DOCUBILLS.heroImage) ? window.DOCUBILLS.heroImage : img.src;

            if (q) {
                const safe = q.replace(/[^a-zA-Z0-9_.-]/g, "");
                if (safe) src = "<?php echo e(asset('assets')); ?>/" + safe;
            }

            if (src) img.src = src;
        })();

        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navbar = document.querySelector('.navbar');
        const mobileOverlay = document.getElementById('mobileOverlay');
        
        function toggleMobileMenu() {
            const isOpen = navbar.classList.contains('mobile-open');
            
            if (isOpen) {
                navbar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
                mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                document.body.style.overflow = '';
            } else {
                navbar.classList.add('mobile-open');
                mobileOverlay.classList.add('active');
                mobileMenuBtn.innerHTML = '<i class="fas fa-times"></i>';
                document.body.style.overflow = 'hidden';
            }
        }
        
        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
        mobileOverlay.addEventListener('click', toggleMobileMenu);
        
        // Close mobile menu when clicking nav links
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                if (navbar.classList.contains('mobile-open')) {
                    toggleMobileMenu();
                }
            });
        });

        // Form submission
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            
            if (email) {
                // Show success message
                alert(`Thank you for signing up! A confirmation email has been sent to ${email}. You can now access your free trial.`);
                
                // Reset form
                this.querySelector('input[type="email"]').value = '';
                
                // In a real implementation, you would send this data to your server
                console.log('Signup email:', email);
            }
        });

        // Smooth scrolling for anchor links (only for # links)
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                    
                    // Close mobile menu if open
                    if (window.innerWidth <= 768) {
                        navLinks.style.display = 'none';
                        navActions.style.display = 'none';
                    }
                }
            });
        });

        // Ensure Sign In button works properly - just close mobile menu if needed
        const signInBtn = document.getElementById('signInBtn');
        if (signInBtn) {
            signInBtn.addEventListener('click', function(e) {
                // Close mobile menu if open (but don't prevent navigation)
                if (window.innerWidth <= 768 && navbar.classList.contains('mobile-open')) {
                    toggleMobileMenu();
                }
                // Navigation will happen naturally via href attribute
            }, false);
        }

        // Landing page user profile menu toggle
        const landingUserProfile = document.getElementById('landingUserProfile');
        const landingProfileMenu = document.getElementById('landingProfileMenu');
        if (landingUserProfile && landingProfileMenu) {
            landingUserProfile.addEventListener('click', function(e) {
                e.stopPropagation();
                landingProfileMenu.classList.toggle('show');
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!landingUserProfile.contains(e.target) && !landingProfileMenu.contains(e.target)) {
                    landingProfileMenu.classList.remove('show');
                }
            });
        }

        // âœ… Demo Tabs Logic (Step 1 / Step 2 / Step 3) + programmatic navigation
        (function () {
          const tabs = document.querySelectorAll('.demo-tab');
          const panels = document.querySelectorAll('.demo-panel');
        
          function activate(tabId) {
            tabs.forEach(t => {
              const selected = (t.id === tabId);
              t.setAttribute('aria-selected', selected ? 'true' : 'false');
            });
        
            panels.forEach(p => p.classList.remove('active'));
        
            const tab = document.getElementById(tabId);
            if (!tab) return;
        
            const panelId = tab.getAttribute('aria-controls');
            const panel = document.getElementById(panelId);
            if (panel) panel.classList.add('active');

            // âœ… IMPORTANT: whenever Step 3 tab becomes active, capture Step 1 data and sync
            if (panelId === 'panel-step3') {
              // Capture Step 1 company details from form (reads default values)
              const step1Root = document.getElementById('demoStep1');
              if (step1Root) {
                window.DOCUBILLS_DEMO_STATE = window.DOCUBILLS_DEMO_STATE || {};
                // Only capture if not already set (preserves user-submitted data)
                if (!window.DOCUBILLS_DEMO_STATE.bill_to_name) {
                  window.DOCUBILLS_DEMO_STATE.bill_to_name = step1Root.querySelector('#bill_to_name')?.value || '';
                  window.DOCUBILLS_DEMO_STATE.bill_to_rep = step1Root.querySelector('#bill_to_rep')?.value || '';
                  window.DOCUBILLS_DEMO_STATE.bill_to_email = step1Root.querySelector('#bill_to_email')?.value || '';
                  window.DOCUBILLS_DEMO_STATE.bill_to_phone = step1Root.querySelector('#bill_to_phone')?.value || '';
                  window.DOCUBILLS_DEMO_STATE.bill_to_address = step1Root.querySelector('#bill_to_address')?.value || '';
                }
              }

              // Sync Step 3 UI
              if (window.DocuBillsDemoStep3 && typeof window.DocuBillsDemoStep3.sync === 'function') {
                window.DocuBillsDemoStep3.sync();
              }
            }
          }
        
          // âœ… Convenience: go(1|2|3)
          function go(stepNum) {
            const map = { 1: 'tab-step1', 2: 'tab-step2', 3: 'tab-step3' };
            const tabId = map[stepNum] || 'tab-step1';
            activate(tabId);
        
            // optional: keep the demo in view
            const demo = document.getElementById('demo');
            if (demo) demo.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        
          // âœ… expose globally for Step 1 / Step 2 scripts
          window.DocuBillsDemo = window.DocuBillsDemo || {};
          window.DocuBillsDemo.activate = activate;
          window.DocuBillsDemo.go = go;
        
          tabs.forEach(t => t.addEventListener('click', () => activate(t.id)));
        
          // default
          activate('tab-step1');
          
          // âœ… Reset Demo functionality
          function resetDemo() {
            // Reset global state to defaults
            window.DOCUBILLS_DEMO_STATE = {
              bill_to_name: 'Acme Logistics',
              bill_to_rep: 'Sarah Khan',
              bill_to_address: 'Suite 210, 123 Main St, Toronto, ON',
              bill_to_phone: '+1 647-555-0199',
              bill_to_email: 'billing@acmelogistics.com',
              price_mode: 'column',
              price_column: 'Sub Total',
              include_cols: null, // Will default to all columns
              titlebar_color: '#0033D9',
              manual_total: null
            };
            
            // Reset Step 1 form inputs
            const step1Root = document.getElementById('demoStep1');
            if (step1Root) {
              const billToName = step1Root.querySelector('#bill_to_name');
              const billToRep = step1Root.querySelector('#bill_to_rep');
              const billToAddress = step1Root.querySelector('#bill_to_address');
              const billToPhone = step1Root.querySelector('#bill_to_phone');
              const billToEmail = step1Root.querySelector('#bill_to_email');
              const googleSheetUrl = step1Root.querySelector('#google_sheet_url');
              const invoiceSource = step1Root.querySelector('input[name="invoice_source"][value="google"]');
              
              if (billToName) billToName.value = 'Acme Logistics';
              if (billToRep) billToRep.value = 'Sarah Khan';
              if (billToAddress) billToAddress.value = 'Suite 210, 123 Main St, Toronto, ON';
              if (billToPhone) billToPhone.value = '+1 647-555-0199';
              if (billToEmail) billToEmail.value = 'billing@acmelogistics.com';
              if (googleSheetUrl) googleSheetUrl.value = 'https://docs.google.com/spreadsheets/d/DEMO-SHEET-ID/edit#gid=0';
              if (invoiceSource) invoiceSource.checked = true;
              
              // Hide upload section, show google section
              const uploadSection = step1Root.querySelector('#upload-section');
              const googleSection = step1Root.querySelector('#google-section');
              if (uploadSection) uploadSection.style.display = 'none';
              if (googleSection) googleSection.style.display = 'block';
            }
            
            // Reset Step 2 pricing mode and column selections
            const step2Root = document.getElementById('demoStep2');
            if (step2Root) {
              // Reset to automatic pricing with "Sub Total" column
              const autoPriceOption = step2Root.querySelector('#demoAutoPriceOption input[value="column"]');
              const manualPriceOption = step2Root.querySelector('#demoManualPriceOption input[value="manual"]');
              const subTotalRadio = step2Root.querySelector('input[name="price_column2"][value="Sub Total"]');
              
              if (autoPriceOption) {
                autoPriceOption.checked = true;
                autoPriceOption.dispatchEvent(new Event('change', { bubbles: true }));
              }
              if (manualPriceOption) manualPriceOption.checked = false;
              
              // Reset all column checkboxes to checked
              const columnCheckboxes = step2Root.querySelectorAll('#demoColumnPicker input[type="checkbox"]');
              columnCheckboxes.forEach(cb => {
                cb.checked = true;
                cb.disabled = false;
              });
              
              // Select Sub Total as price column
              if (subTotalRadio) {
                subTotalRadio.checked = true;
                subTotalRadio.dispatchEvent(new Event('change', { bubbles: true }));
              }
            }
            
            // Reset Step 3 data
            const originalHeaders = ["Trip Date","Pickup","Dropoff","KM","Rate","Sub Total","Tax","Total"];
            if (window.DocuBillsDemoStep3 && typeof window.DocuBillsDemoStep3.__resetData === 'function') {
              window.DocuBillsDemoStep3.__resetData(originalHeaders);
            }
            
            // Sync Step 3 after reset
            if (window.DocuBillsDemoStep3 && typeof window.DocuBillsDemoStep3.sync === 'function') {
              window.DocuBillsDemoStep3.sync();
            }
            
            // Navigate to Step 1
            go(1);
          }
          
          // Expose reset function globally
          window.DocuBillsDemo.reset = resetDemo;
          
          // Wire up reset button
          const resetBtn = document.getElementById('demoResetBtn');
          if (resetBtn) {
            resetBtn.addEventListener('click', resetDemo);
          }
        })();
        
        // Feature cards hover effect enhancement
        const featureCards = document.querySelectorAll('.feature-card');
        featureCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                const icon = this.querySelector('.feature-icon');
                if (icon) {
                    icon.style.transform = 'scale(1.1) rotate(5deg)';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                const icon = this.querySelector('.feature-icon');
                if (icon) {
                    icon.style.transform = 'scale(1) rotate(0deg)';
                }
            });
        });
    </script>
    
<script>
(function(){
  const root = document.getElementById('demoStep1');
  if (!root) return;

  // âœ… Demo Clients (replaces search_clients.php fetch)
  const demoClients = [
    {
      company_name: "Acme Logistics",
      representative: "Sarah Khan",
      address: "Suite 210, 123 Main St, Toronto, ON",
      phone: "+1 647-555-0199",
      email: "billing@acmelogistics.com"
    },
    {
      company_name: "WomenFirst Inc.",
      representative: "Operations Team",
      address: "Downtown, Toronto, ON",
      phone: "+1 416-555-0147",
      email: "accounts@womenfirst.ca"
    },
    {
      company_name: "FastTechBPO",
      representative: "Billing Dept",
      address: "BPO Center, Lahore",
      phone: "+92 300-123-4567",
      email: "billing@fasttechbpo.com"
    }
  ];

  // âœ… Toggle Google/Upload sections (THIS is the â€œradio clickâ€ behavior)
  const googleSection = root.querySelector('#google-section');
  const uploadSection = root.querySelector('#upload-section');
  const radios = root.querySelectorAll('input[name="invoice_source"]');

  function syncSourceUI(){
    const selected = root.querySelector('input[name="invoice_source"]:checked')?.value || 'google';
    const isUpload = (selected === 'upload');

    if (googleSection) googleSection.style.display = isUpload ? 'none' : 'block';
    if (uploadSection) uploadSection.style.display = isUpload ? 'block' : 'none';
  }
  radios.forEach(r => r.addEventListener('change', syncSourceUI));
  syncSourceUI();

  // âœ… Step 1 -> Step 2 navigation
    const form = root.querySelector('#demoInvoiceForm');
    if (form){
      form.addEventListener('submit', function(e){
        e.preventDefault();

        // (Optional) You can do basic "required" check here if you want,
        // but your inputs already have required attributes.

        // âœ… Capture company details from Step 1 form
        window.DOCUBILLS_DEMO_STATE = window.DOCUBILLS_DEMO_STATE || {};
        window.DOCUBILLS_DEMO_STATE.bill_to_name = root.querySelector('#bill_to_name')?.value || '';
        window.DOCUBILLS_DEMO_STATE.bill_to_rep = root.querySelector('#bill_to_rep')?.value || '';
        window.DOCUBILLS_DEMO_STATE.bill_to_email = root.querySelector('#bill_to_email')?.value || '';
        window.DOCUBILLS_DEMO_STATE.bill_to_phone = root.querySelector('#bill_to_phone')?.value || '';
        window.DOCUBILLS_DEMO_STATE.bill_to_address = root.querySelector('#bill_to_address')?.value || '';

        if (window.DocuBillsDemo && typeof window.DocuBillsDemo.go === 'function') {
          window.DocuBillsDemo.go(2); // âœ… go to Step 2 tab
        }
      });
    }

   // âœ… Upload area (REAL UI behavior)
  const uploadArea = root.querySelector('#uploadArea');
  const fileInput  = root.querySelector('#excel_file');
  const fileNameEl = root.querySelector('#fileNameDemo');
  const fileErrEl  = root.querySelector('#fileErrorDemo');

  function showFileName(name){
    if (fileNameEl){
      fileNameEl.textContent = "Selected file: " + name;
      fileNameEl.style.display = "block";
    }
  }

  function showFileError(msg){
    if (fileErrEl){
      fileErrEl.textContent = msg;
      fileErrEl.style.display = "block";
    }
  }

  function clearFileUI(){
    if (fileNameEl){
      fileNameEl.textContent = "";
      fileNameEl.style.display = "none";
    }
    if (fileErrEl){
      fileErrEl.textContent = "";
      fileErrEl.style.display = "none";
    }
  }

  function isExcelFile(file){
    const name = (file?.name || "").toLowerCase();
    return name.endsWith(".xls") || name.endsWith(".xlsx");
  }

  function handleSelectedFile(file){
    clearFileUI();

    if (!file) return;

    if (!isExcelFile(file)){
      showFileError("Please upload a valid Excel file (.xls or .xlsx).");
      return;
    }

    showFileName(file.name);
  }

  // Click to browse (disable in demo when locked)
    if (uploadArea && fileInput){
      uploadArea.addEventListener("click", (e) => {
        if (uploadArea.classList.contains("is-locked")) {
          e.preventDefault();
          e.stopPropagation();
          return;
        }
        fileInput.click();
      });
    }

  // On browse selection
  if (fileInput){
    fileInput.addEventListener("change", () => {
      const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
      handleSelectedFile(file);
    });
  }

  // Drag & Drop
  if (uploadArea){
    ["dragenter","dragover"].forEach(evt => {
      uploadArea.addEventListener(evt, (e) => {
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.add("dragover");
      });
    });

    ["dragleave","drop"].forEach(evt => {
      uploadArea.addEventListener(evt, (e) => {
        e.preventDefault();
        e.stopPropagation();
        uploadArea.classList.remove("dragover");
      });
    });

    uploadArea.addEventListener("drop", (e) => {
      if (uploadArea.classList.contains("is-locked")) return;
    
      const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0] ? e.dataTransfer.files[0] : null;
      handleSelectedFile(file);
    });
  }

  // âœ… Demo autocomplete (no fetch)
  const companyInput   = root.querySelector('#bill_to_name');
  const repInput       = root.querySelector('#bill_to_rep');
  const addressInput   = root.querySelector('#bill_to_address');
  const phoneInput     = root.querySelector('#bill_to_phone');
  const emailInput     = root.querySelector('#bill_to_email');
  const suggestionsBox = root.querySelector('#clientSuggestions');

  function clearSuggestions(){
    if (!suggestionsBox) return;
    suggestionsBox.innerHTML = '';
    suggestionsBox.style.display = 'none';
  }

  function renderSuggestions(list){
    if (!suggestionsBox) return;
    suggestionsBox.innerHTML = '';
    if (!list.length){
      clearSuggestions();
      return;
    }

    list.forEach(client => {
      const item = document.createElement('div');
      item.className = 'autocomplete-item';

      const left = document.createElement('div');
      left.className = 'autocomplete-company';
      left.textContent = client.company_name;

      const right = document.createElement('div');
      right.className = 'autocomplete-rep';
      right.textContent = client.representative ? ('Contact: ' + client.representative) : '';

      item.appendChild(left);
      item.appendChild(right);

      item.addEventListener('click', () => {
        if (companyInput) companyInput.value = client.company_name || '';
        if (repInput) repInput.value = client.representative || '';
        if (addressInput) addressInput.value = client.address || '';
        if (phoneInput) phoneInput.value = client.phone || '';
        if (emailInput) emailInput.value = client.email || '';
        clearSuggestions();
      });

      suggestionsBox.appendChild(item);
    });

    suggestionsBox.style.display = 'block';
  }

  if (companyInput && suggestionsBox){
    document.addEventListener('click', function(e){
      if (!suggestionsBox.contains(e.target) && e.target !== companyInput) clearSuggestions();
    });

    companyInput.addEventListener('input', function(){
      const q = (this.value || '').trim().toLowerCase();
      if (!q){
        clearSuggestions();
        return;
      }
      const matches = demoClients
        .filter(c => (c.company_name || '').toLowerCase().includes(q))
        .slice(0, 6);
      renderSuggestions(matches);
    });

    companyInput.addEventListener('keydown', function(e){
      if (e.key === 'Escape') clearSuggestions();
    });
  }

})();
</script>

<script>
(function(){
  const root = document.getElementById('demoStep2');
  if (!root) return;

  // âœ… Demo headers (replace PHP $headers loop)
  const demoHeaders = [
    "Trip Date",
    "Pickup",
    "Dropoff",
    "KM",
    "Rate",
    "Sub Total",
    "Tax",
    "Total"
  ];

  // âœ… Fake totals per column (to simulate your PHP validation)
  const demoColumnTotals = {
    "Sub Total": 1280.50,
    "Total": 1446.97,
    "Tax": 166.47,
    "KM": 0,
    "Rate": 0,
    "Trip Date": 0,
    "Pickup": 0,
    "Dropoff": 0
  };

  // DOM
  const autoOption   = root.querySelector('#demoAutoPriceOption');
  const manualOption = root.querySelector('#demoManualPriceOption');
  const priceColsBox = root.querySelector('#demoPriceColumns');
  const columnPicker = root.querySelector('#demoColumnPicker');
  const form         = root.querySelector('#demoPricingForm');
  const alertBox     = root.querySelector('.demo-step2-alert');

  function showAlert(msg){
    if (!alertBox) return;
    alertBox.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + msg;
    alertBox.style.display = 'block';
  }
  function hideAlert(){
    if (!alertBox) return;
    alertBox.innerHTML = '';
    alertBox.style.display = 'none';
  }

  // âœ… Build price-column radios (like your foreach $headers)
  function renderPriceColumnRadios(){
    // keep the first <p> then inject below it
    const keepP = priceColsBox.querySelector('p');
    priceColsBox.innerHTML = '';
    if (keepP) priceColsBox.appendChild(keepP);

    demoHeaders.forEach((col, i) => {
      const wrap = document.createElement('div');
      wrap.className = 'form-group';

      const id = 'demo_price_col_' + i;

      wrap.innerHTML = `
        <label for="${id}">
          <input type="radio" id="${id}" name="price_column2" value="${col}">
          Column: <strong>${col}</strong>
        </label>
      `;

      priceColsBox.appendChild(wrap);
    });

    // Default select "Sub Total" if present
    const defaultCol = Array.from(root.querySelectorAll('input[name="price_column2"]'))
      .find(r => r.value === 'Sub Total');
    if (defaultCol) defaultCol.checked = true;
  }

  // âœ… Build include columns checkboxes (like your include_cols[])
  function renderIncludeColumns(){
    columnPicker.innerHTML = '';
    demoHeaders.forEach((col, idx) => {
      const label = document.createElement('label');
      label.style.display = 'block';
      label.style.marginBottom = '0.5rem';
      label.innerHTML = `
        <input type="checkbox" name="include_cols2[]" value="${idx}" checked>
        ${col}
      `;
      columnPicker.appendChild(label);
    });
  }

  renderPriceColumnRadios();
  renderIncludeColumns();

  const priceColumnRadios = () => Array.from(root.querySelectorAll('input[name="price_column2"]'));
  const priceModeRadios   = () => Array.from(root.querySelectorAll('input[name="price_mode2"]'));

  // âœ… keep selected auto column stored (helps Step 3 stay correct)
  priceColumnRadios().forEach(r => {
    r.addEventListener('change', () => {
      if (!r.checked) return;

      // Store state
      window.DOCUBILLS_DEMO_STATE = window.DOCUBILLS_DEMO_STATE || {};
      window.DOCUBILLS_DEMO_STATE.price_mode = 'column';
      window.DOCUBILLS_DEMO_STATE.price_column = r.value;

      // âœ… Lock the corresponding checkbox in column picker
      const selectedCol = r.value;  // e.g., "Sub Total"
      const colIndex = demoHeaders.indexOf(selectedCol);

      if (colIndex >= 0) {
        // First, unlock all checkboxes (clear previous lock)
        const allCheckboxes = root.querySelectorAll('#demoColumnPicker input[type="checkbox"]');
        allCheckboxes.forEach(cb => {
          const wasRequired = cb.dataset.priceColumnLock === '1';
          if (wasRequired) {
            cb.disabled = false;
            delete cb.dataset.priceColumnLock;
            const label = cb.closest('label');
            if (label) label.style.opacity = '';
          }
        });

        // Lock the selected pricing column checkbox
        const targetCheckbox = root.querySelector(`#demoColumnPicker input[type="checkbox"][value="${colIndex}"]`);
        if (targetCheckbox) {
          targetCheckbox.checked = true;
          targetCheckbox.disabled = true;
          targetCheckbox.dataset.priceColumnLock = '1';

          // Visual indication
          const label = targetCheckbox.closest('label');
          if (label) label.style.opacity = '0.7';
        }
      }
    });
  });

  // âœ… Apply initial lock to default pricing column (Sub Total)
  const initialPriceRadio = root.querySelector('input[name="price_column2"]:checked');
  if (initialPriceRadio) {
    initialPriceRadio.dispatchEvent(new Event('change'));
  }

  function setMode(isManual){
    hideAlert();

    autoOption.classList.toggle('active', !isManual);
    manualOption.classList.toggle('active', isManual);

    // toggle required/disabled on column radios (like your real JS)
    priceColumnRadios().forEach(r => {
      r.required = !isManual;
      r.disabled = isManual;
    });

    // update mode radio state
    const autoRadio = root.querySelector('input[name="price_mode2"][value="column"]');
    const manRadio  = root.querySelector('input[name="price_mode2"][value="manual"]');
    if (autoRadio && manRadio){
      autoRadio.checked = !isManual;
      manRadio.checked  = isManual;
    }
    
    // âœ… FIX: keep global state in sync immediately (not only on submit)
    window.DOCUBILLS_DEMO_STATE = window.DOCUBILLS_DEMO_STATE || {};
    window.DOCUBILLS_DEMO_STATE.price_mode = isManual ? 'manual' : 'column';

    if (isManual) {
      window.DOCUBILLS_DEMO_STATE.price_column = null;

      // âœ… Clear all pricing column locks when switching to manual mode
      const allCheckboxes = root.querySelectorAll('#demoColumnPicker input[type="checkbox"]');
      allCheckboxes.forEach(cb => {
        if (cb.dataset.priceColumnLock === '1') {
          cb.disabled = false;
          delete cb.dataset.priceColumnLock;
          const label = cb.closest('label');
          if (label) label.style.opacity = '';
        }
      });
    } else {
      const sel = root.querySelector('input[name="price_column2"]:checked');
      if (sel) window.DOCUBILLS_DEMO_STATE.price_column = sel.value;
    }
  }

  // click cards
  autoOption.addEventListener('click', (e) => {
    if (e.target.tagName !== 'INPUT') setMode(false);
  });
  manualOption.addEventListener('click', (e) => {
    if (e.target.tagName !== 'INPUT') setMode(true);
  });

  // change radios
  priceModeRadios().forEach(r => {
    r.addEventListener('change', () => setMode(r.value === 'manual'));
  });

  // initial required state
  setMode(false);

  // âœ… max 15 enforcement (same logic)
  const max = 15;
  function enforceColumnLimit(){
    const checks = Array.from(root.querySelectorAll('#demoColumnPicker input[type="checkbox"]'));
    const checkedCount = checks.filter(c => c.checked).length;

    checks.forEach(c => {
      // âœ… Don't override pricing column lock
      if (c.dataset.priceColumnLock === '1') {
        c.disabled = true;
        return;
      }

      if (!c.checked && checkedCount >= max) c.disabled = true;
      else c.disabled = false;
    });
  }

  root.querySelectorAll('#demoColumnPicker input[type="checkbox"]').forEach(c => {
    c.addEventListener('change', enforceColumnLimit);
  });
  enforceColumnLimit();

  // âœ… submit (demo validation + Step 2 -> Step 3 navigation)
    form.addEventListener('submit', function(e){
      e.preventDefault();
      hideAlert();
    
      const mode = root.querySelector('input[name="price_mode2"]:checked')?.value || 'column';
    
      // capture included columns (optional â€“ useful if later you want Step 3 to reflect selection)
      const includeCols = Array.from(root.querySelectorAll('#demoColumnPicker input[type="checkbox"]'))
        .filter(c => c.checked)
        .map(c => Number(c.value));
    
      if (mode === 'manual'){
        // âœ… manual mode still proceeds to Step 3 in demo
        window.DOCUBILLS_DEMO_STATE = Object.assign({}, window.DOCUBILLS_DEMO_STATE || {}, {
          price_mode: 'manual',
          price_column: null,
          include_cols: includeCols
        });
    
        if (window.DocuBillsDemo && typeof window.DocuBillsDemo.go === 'function') {
          if (window.DocuBillsDemoStep3 && typeof window.DocuBillsDemoStep3.sync === 'function') {
            window.DocuBillsDemoStep3.sync();
          }
          window.DocuBillsDemo.go(3);
        }
        return;
      }
    
      const selectedCol = root.querySelector('input[name="price_column2"]:checked');
      if (!selectedCol){
        showAlert('Please select a price column for automatic pricing.');
        autoOption.scrollIntoView({ behavior: 'smooth', block: 'center' });
        autoOption.style.borderColor = 'var(--danger)';
        setTimeout(() => autoOption.style.borderColor = '', 1400);
        return;
      }
    
      const total = Number(demoColumnTotals[selectedCol.value] || 0);
      if (total <= 0){
        showAlert(
          'The selected price column did not produce a valid total amount. ' +
          'Please choose a different column (for example, "Sub Total") or verify your data.'
        );
        return;
      }
    
      // âœ… store state (optional - merge to preserve Step 1 data)
      window.DOCUBILLS_DEMO_STATE = Object.assign({}, window.DOCUBILLS_DEMO_STATE || {}, {
        price_mode: 'column',
        price_column: selectedCol.value,
        include_cols: includeCols
      });

      // âœ… go to Step 3
      if (window.DocuBillsDemo && typeof window.DocuBillsDemo.go === 'function') {
        if (window.DocuBillsDemoStep3 && typeof window.DocuBillsDemoStep3.sync === 'function') {
          window.DocuBillsDemoStep3.sync();
        }
        window.DocuBillsDemo.go(3);
      }
    });

})();
</script>

<script>
(function () {
  const root = document.getElementById('demoStep3');
  if (!root) return;

  const STRIPE_MAX_TOTAL = 999999.99;

  // -----------------------------
  // Demo columns + rows
  // -----------------------------
  const headers = ["Trip Date","Pickup","Dropoff","KM","Rate","Sub Total","Tax","Total"];

  let rows = [
    ["2026-01-01","Downtown","Airport","18","45.00","810.00","105.30","915.30"],
    ["2026-01-02","Mall","Hotel","12","45.00","540.00","70.20","610.20"],
    ["2026-01-03","Office","Station","8","45.00","360.00","46.80","406.80"],
    ["2026-01-04","Clinic","Home","6","45.00","270.00","35.10","305.10"]
  ];

  // Row enabled flags (for row checkboxes)
  let rowEnabled = rows.map(() => true);

  const els = {
    swatchRow: root.querySelector('#demoTitleBarColorRow'),
    titlePreview: root.querySelector('#demoInvoiceTitlePreview'),

    toggles: root.querySelector('#demoColumnToggles'),
    thead: root.querySelector('#demoInvoiceTable thead'),
    tbody: root.querySelector('#demoInvoiceTable tbody'),

    totalSpan: root.querySelector('#demoTotalAmount'),
    currencySelect: root.querySelector('#demoCurrencyCode'),
    manualTotal: root.querySelector('#demoManualTotalInput'),

    stripeWarn: root.querySelector('#demoStripeWarning'),
    stripeLimit: root.querySelector('#demoStripeLimitDisplay'),
    stripeAck: root.querySelector('#demoManualOnlyAck'),
    saveBtn: root.querySelector('#demoSaveBtn'),

    addFieldBtn: root.querySelector('#demoAddFieldBtn'), // ONLY button now

    invoiceDate: root.querySelector('#demoInvoiceDate'),
    invoiceTime: root.querySelector('#demoInvoiceTime'),
    dueDate: root.querySelector('#demoDueDate'),
    toggleDueTime: root.querySelector('#demoToggleDueTime'),
    dueTimeWrap: root.querySelector('#demoDueTimeContainer'),
    dueTime: root.querySelector('#demoDueTime'),

    recurringBtn: root.querySelector('#demoRecurringToggle'),
    recurringText: root.querySelector('#demoRecurringText'),

    toggleBank: root.querySelector('#demoToggleBankDetails'),
    bankDrawer: root.querySelector('#demoBankingDrawer')
  };

  function getState() {
    window.DOCUBILLS_DEMO_STATE = window.DOCUBILLS_DEMO_STATE || {};
    return window.DOCUBILLS_DEMO_STATE;
  }

  // -----------------------------
  // Date helpers
  // -----------------------------
  function pad2(n){ return String(n).padStart(2,'0'); }
  function yyyy_mm_dd(d){ return `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`; }
  function hh_mm(d){ return `${pad2(d.getHours())}:${pad2(d.getMinutes())}`; }

  function ensureDefaultDates() {
    const now = new Date();
    if (els.invoiceDate && !els.invoiceDate.value) els.invoiceDate.value = yyyy_mm_dd(now);
    if (els.invoiceTime && !els.invoiceTime.value) els.invoiceTime.value = hh_mm(now);

    if (els.dueDate && !els.dueDate.value) {
      const due = new Date(now);
      due.setDate(due.getDate() + 30);
      els.dueDate.value = yyyy_mm_dd(due);
    }
  }

  // -----------------------------
  // Money helpers
  // -----------------------------
  function parseMoney(val) {
    const s = String(val ?? '').trim();
    if (!s) return 0;
    const cleaned = s.replace(/[^0-9.,-]/g, '').replace(/,/g, '');
    const num = parseFloat(cleaned);
    return Number.isFinite(num) ? num : 0;
  }
  function format2(n) {
    const x = Number(n);
    if (!Number.isFinite(x)) return "0.00";
    return x.toFixed(2);
  }

  // -----------------------------
  // Currencies (MORE like generate_invoice.php)
  // -----------------------------
  const currencyMap = {
    USD: "$", CAD: "$", AUD: "$", NZD: "$", SGD: "$", HKD: "$",
    GBP: "Â£", EUR: "â‚¬", CHF: "CHF",
    PKR: "â‚¨", INR: "â‚¹", BDT: "à§³", LKR: "Rs",
    AED: "Ø¯.Ø¥", SAR: "ï·¼", QAR: "Ø±.Ù‚", KWD: "Ø¯.Ùƒ", OMR: "Ø±.Ø¹.",
    JPY: "Â¥", CNY: "Â¥", KRW: "â‚©",
    SEK: "kr", NOK: "kr", DKK: "kr", ZAR: "R"
  };

  function currentCurrencyCode(){
    return (els.currencySelect && els.currencySelect.value) ? els.currencySelect.value : "USD";
  }
  function currentPrefix(){
    const code = currentCurrencyCode();
    return currencyMap[code] || "";
  }
  function fillCurrencyOptions(){
    if (!els.currencySelect) return;
    const codes = Object.keys(currencyMap);
    els.currencySelect.innerHTML = "";
    codes.forEach(code => {
      const opt = document.createElement("option");
      opt.value = code;
      opt.textContent = code;
      els.currencySelect.appendChild(opt);
    });

    // default to CAD (your demo was CAD)
    if (!els.currencySelect.value) els.currencySelect.value = "CAD";
    else els.currencySelect.value = els.currencySelect.value || "CAD";
  }
  function updateStripeCurrencyPrefix(){
    const prefixEls = root.querySelectorAll('.currencyPrefix');
    prefixEls.forEach(e => e.textContent = currentPrefix());
  }

  // -----------------------------
  // Title bar color swatches (better set)
  // -----------------------------
  const TITLE_BAR_COLORS = [
    "#0033D9", "#4361ee", "#3f37c9", "#7209b7",
    "#06d6a0", "#16a34a", "#f72585", "#f8961e",
    "#111827", "#0f172a"
  ];

  function luminance(hex){
    const c = hex.replace('#','');
    const r = parseInt(c.substring(0,2),16)/255;
    const g = parseInt(c.substring(2,4),16)/255;
    const b = parseInt(c.substring(4,6),16)/255;
    const a = [r,g,b].map(v => (v <= 0.03928 ? v/12.92 : Math.pow((v+0.055)/1.055, 2.4)));
    return 0.2126*a[0] + 0.7152*a[1] + 0.0722*a[2];
  }

  function setTitleBarColor(hex){
    if (!els.titlePreview) return;
    const lum = luminance(hex);
    els.titlePreview.style.background = hex;
    els.titlePreview.style.color = (lum > 0.6) ? "#111827" : "#ffffff";

    // selected ring
    if (els.swatchRow){
      els.swatchRow.querySelectorAll('.color-swatch').forEach(btn => {
        btn.classList.toggle('is-selected', btn.dataset.color === hex);
      });
    }

    const st = getState();
    st.titlebar_color = hex;
  }

  function renderTitleBarSwatches(){
    if (!els.swatchRow) return;
    els.swatchRow.innerHTML = "";

    TITLE_BAR_COLORS.forEach(hex => {
      const btn = document.createElement('button');
      btn.type = "button";
      btn.className = "color-swatch";
      btn.dataset.color = hex;
      btn.innerHTML = `<span class="swatch-box" style="background:${hex}"></span>`;
      btn.addEventListener('click', () => setTitleBarColor(hex));
      els.swatchRow.appendChild(btn);
    });

    const st = getState();
    const defaultColor = st.titlebar_color || "#0033D9";
    setTitleBarColor(defaultColor);
  }

  // -----------------------------
  // Company info display
  // -----------------------------
  function renderCompanyInfo(){
    const st = getState();
    const companyInfoEl = root.querySelector('#demoCompanyInfo');
    const billToEl = root.querySelector('#demoBillTo');

    // âœ… LEFT SIDE: Your company/sender information
    if (companyInfoEl) {
      companyInfoEl.innerHTML = `
        <div class="company-name">DocuBills</div>
        <div>Pakistan</div>
        <div>+92-323-8970703</div>
        <div>docubills@gmail.com</div>
        <div>(SST/HST: 987654321)</div>
      `;
    }

    // âœ… RIGHT SIDE: Bill To client information OR banking details
    renderBillToSection();
  }

  function renderBillToSection(){
    const billToEl = root.querySelector('#demoBillTo');
    if (!billToEl) return;

    const st = getState();
    const showBanking = els.toggleBank && els.toggleBank.checked;

    if (showBanking) {
      // Show payment/banking details when toggle is ON
      const accountHolder = root.querySelector('#demoAccountHolder')?.value || '';
      const bankName = root.querySelector('#demoBankName')?.value || '';
      const accountNumber = root.querySelector('#demoAccountNumber')?.value || '';
      const iban = root.querySelector('#demoIBAN')?.value || '';
      const swift = root.querySelector('#demoSWIFT')?.value || '';
      const routingCode = root.querySelector('#demoRoutingCode')?.value || '';
      const paymentInstructions = root.querySelector('#demoPaymentInstructions')?.value || '';

      billToEl.innerHTML = `
        <div style="font-size: 14px; color: #1a1a2e; margin-bottom: 8px;"><strong>Payment Details:</strong></div>
        ${accountHolder ? `<div style="font-size: 14px;"><strong>Account Holder:</strong> ${accountHolder}</div>` : ''}
        ${bankName ? `<div style="font-size: 14px;"><strong>Bank:</strong> ${bankName}</div>` : ''}
        ${iban ? `<div style="font-size: 14px;"><strong>IBAN:</strong> ${iban}</div>` : ''}
        ${swift ? `<div style="font-size: 14px;"><strong>SWIFT/BIC:</strong> ${swift}</div>` : ''}
        ${accountNumber ? `<div style="font-size: 14px;"><strong>Account No:</strong> ${accountNumber}</div>` : ''}
        ${routingCode ? `<div style="font-size: 14px;"><strong>Routing Code:</strong> ${routingCode}</div>` : ''}
        ${paymentInstructions ? `<div style="font-size: 14px; margin-top: 8px; font-style: italic;">${paymentInstructions}</div>` : ''}
      `;
    } else {
      // âœ… Show Bill To client information (from Step 1)
      billToEl.innerHTML = `
        <div style="font-weight: 700; font-size: 16px; margin-bottom: 4px;">${st.bill_to_name || ''}</div>
        ${st.bill_to_rep ? `<div style="font-size: 14px;">${st.bill_to_rep}</div>` : ''}
        ${st.bill_to_address ? `<div style="font-size: 14px;">${st.bill_to_address}</div>` : ''}
        ${st.bill_to_phone ? `<div style="font-size: 14px;">${st.bill_to_phone}</div>` : ''}
        ${st.bill_to_email ? `<div style="font-size: 14px;">${st.bill_to_email}</div>` : ''}
      `;
    }
  }

  // âœ… Keep backward compatibility - renderBankingDetails now calls renderBillToSection
  function renderBankingDetails(){
    renderBillToSection();
  }

  // -----------------------------
  // Column visibility + REQUIRED lock
  // -----------------------------
  let visibleCols = new Set(headers.map((_,i)=>i)); // indices

  function getPriceModeAndColumn(){
    const st = getState();
    const mode = st.price_mode || "column"; // "column" or "manual"
    const col  = st.price_column || "Sub Total";
    return { mode, col };
  }

  function applyIncludeColsFromStep2(){
    const st = getState();
    if (Array.isArray(st.include_cols) && st.include_cols.length){
      visibleCols = new Set(st.include_cols.map(n => Number(n)).filter(n => Number.isFinite(n)));
    } else {
      visibleCols = new Set(headers.map((_,i)=>i));
    }

    // If automatic pricing, force required column visible
    const { mode, col } = getPriceModeAndColumn();
    if (mode === "column"){
      const reqIdx = headers.indexOf(col);
      if (reqIdx >= 0) visibleCols.add(reqIdx);
    }
  }

  function renderColumnToggles(){
    if (!els.toggles) return;
    els.toggles.innerHTML = "";

    const { mode, col } = getPriceModeAndColumn();
    const requiredIdx = (mode === "column") ? headers.indexOf(col) : -1;

    headers.forEach((name, idx) => {
      const wrap = document.createElement('label');
      wrap.className = 'column-toggle-item';
      wrap.dataset.col = name; // Store column name for sync function
      if (idx === requiredIdx) wrap.classList.add('price-column-label');

      const cb = document.createElement('input');
      cb.type = "checkbox";
      cb.checked = visibleCols.has(idx);

      // âœ… lock required column like generate_invoice.php
      if (idx === requiredIdx){
        cb.checked = true;
        cb.disabled = true;          // grey + unclickable
        visibleCols.add(idx);        // ensure it's in visible set
      }

      cb.addEventListener('change', () => {
        if (cb.checked) visibleCols.add(idx);
        else visibleCols.delete(idx);
        renderTable();
        syncTotalsAndStripe();
      });

      const text = document.createElement('span');
      text.textContent = name;

      wrap.appendChild(cb);
      wrap.appendChild(text);

      if (idx === requiredIdx){
        const pill = document.createElement('span');
        pill.className = 'required-pill';
        pill.textContent = 'REQUIRED FOR TOTAL';
        wrap.appendChild(pill);
      }

      els.toggles.appendChild(wrap);
    });
  }

  // Update column toggle labels when headers change
  function updateColumnToggleLabels(){
    if (!els.toggles) return;
    const toggleItems = els.toggles.querySelectorAll('.column-toggle-item');
    toggleItems.forEach((item, idx) => {
      if (idx < headers.length) {
        // Find the text span (first span that's not the required-pill)
        const spans = item.querySelectorAll('span');
        const textSpan = Array.from(spans).find(span => !span.classList.contains('required-pill'));
        if (textSpan) {
          textSpan.textContent = headers[idx];
        }
      }
    });
  }

  // -----------------------------
  // Row checkboxes + table render
  // -----------------------------
  function renderTable(){
    if (!els.thead || !els.tbody) return;

    // THEAD
    els.thead.innerHTML = "";
    const trh = document.createElement('tr');

    // âœ… first column = row checkbox column (always visible)
    const th0 = document.createElement('th');
    th0.className = "header-cell";
    th0.style.width = "46px";
    th0.style.textAlign = "center";

    // optional: select all
    th0.innerHTML = `<input type="checkbox" id="demoSelectAllRows" title="Select all rows">`;
    trh.appendChild(th0);

    // visible columns - editable headers
    headers.forEach((h, i) => {
      if (!visibleCols.has(i)) return;
      const th = document.createElement('th');
      th.className = "header-cell";
      th.contentEditable = "true";
      th.spellcheck = false;
      th.setAttribute('data-col-index', i);
      th.textContent = h;

      // Handle input changes to update headers array
      th.addEventListener('input', () => {
        headers[i] = th.textContent.trim();
        // Update column toggle labels when header changes
        updateColumnToggleLabels();
      });

      // Handle blur to ensure data is saved
      th.addEventListener('blur', () => {
        headers[i] = th.textContent.trim();
        updateColumnToggleLabels();
      });

      // Handle Enter key to save and move to next header
      th.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          th.blur();
          // Move to next editable header in same row
          const nextHeader = trh.querySelector(`th[data-col-index="${i + 1}"]`);
          if (nextHeader && nextHeader.contentEditable === 'true') {
            nextHeader.focus();
          }
        }
        // Allow Escape to cancel (optional)
        if (e.key === 'Escape') {
          th.textContent = headers[i];
          th.blur();
        }
      });

      trh.appendChild(th);
    });

    els.thead.appendChild(trh);

    // Select all behavior
    const selectAll = trh.querySelector('#demoSelectAllRows');
    if (selectAll){
      const allOn = rowEnabled.every(v => v === true);
      selectAll.checked = allOn;
      selectAll.addEventListener('change', () => {
        const on = !!selectAll.checked;
        rowEnabled = rowEnabled.map(() => on);
        renderTable();
        syncTotalsAndStripe();
      });
    }

    // TBODY
    els.tbody.innerHTML = "";

    rows.forEach((row, rIdx) => {
      const tr = document.createElement('tr');

      if (!rowEnabled[rIdx]) tr.classList.add('row-disabled');

      // âœ… checkbox cell
      const td0 = document.createElement('td');
      td0.style.textAlign = "center";
      td0.style.verticalAlign = "middle";

      const cb = document.createElement('input');
      cb.type = "checkbox";
      cb.checked = !!rowEnabled[rIdx];
      cb.title = "Include this row";
      cb.addEventListener('change', () => {
        rowEnabled[rIdx] = !!cb.checked;
        renderTable();
        syncTotalsAndStripe();
      });

      td0.appendChild(cb);
      tr.appendChild(td0);

      // visible data cells - all columns are editable (only if row is enabled)
      headers.forEach((_, cIdx) => {
        if (!visibleCols.has(cIdx)) return;

        const td = document.createElement('td');
        const isRowEnabled = !!rowEnabled[rIdx];
        td.className = isRowEnabled ? "editable-cell" : "editable-cell readonly-cell";
        td.contentEditable = isRowEnabled ? "true" : "false";
        td.spellcheck = false;
        td.setAttribute('data-row', rIdx);
        td.setAttribute('data-col', cIdx);
        td.textContent = (row[cIdx] ?? "");

        // Handle input changes (only if row is enabled)
        td.addEventListener('input', () => {
          if (!rowEnabled[rIdx]) {
            // Row is disabled, restore original value
            td.textContent = (row[cIdx] ?? "");
            return;
          }
          rows[rIdx][cIdx] = td.textContent.trim();
          syncTotalsAndStripe();
        });

        // Handle blur to ensure data is saved and remove focus styling
        td.addEventListener('blur', () => {
          if (!rowEnabled[rIdx]) {
            // Row is disabled, restore original value
            td.textContent = (row[cIdx] ?? "");
            td.style.backgroundColor = '';
            td.style.outline = '';
            return;
          }
          rows[rIdx][cIdx] = td.textContent.trim();
          syncTotalsAndStripe();
          td.style.backgroundColor = '';
          td.style.outline = '';
        });

        // Handle Enter key to save and move to next cell
        td.addEventListener('keydown', (e) => {
          if (!rowEnabled[rIdx]) {
            // Row is disabled, prevent editing
            e.preventDefault();
            return;
          }
          if (e.key === 'Enter') {
            e.preventDefault();
            td.blur();
            // Move to next editable cell in same row
            const nextCell = tr.querySelector(`td[data-col="${cIdx + 1}"]`);
            if (nextCell && nextCell.contentEditable === 'true') {
              nextCell.focus();
            }
          }
          // Allow Escape to cancel (optional)
          if (e.key === 'Escape') {
            td.textContent = (row[cIdx] ?? "");
            td.blur();
          }
        });

        // Add visual feedback on focus (only if row is enabled)
        td.addEventListener('focus', () => {
          if (!rowEnabled[rIdx]) {
            // Row is disabled, blur immediately
            td.blur();
            return;
          }
          td.style.backgroundColor = '#fff';
          td.style.outline = '2px solid #4361ee';
        });

        tr.appendChild(td);
      });

      els.tbody.appendChild(tr);
    });
  }

  // -----------------------------
  // Totals + manual mode + Stripe warning
  // -----------------------------
  function calculateAutoTotal(priceColName){
    const idx = headers.indexOf(priceColName);
    if (idx < 0) return 0;

    let sum = 0;
    rows.forEach((r, i) => {
      if (!rowEnabled[i]) return;
      sum += parseMoney(r[idx]);
    });
    return sum;
  }

  function setManualModeUI(isManual){
    if (!els.manualTotal || !els.totalSpan) return;

    els.manualTotal.style.display = isManual ? "inline-block" : "none";
    els.totalSpan.style.display   = isManual ? "none" : "inline-block";
  }

  function getCurrentTotal(){
    const { mode, col } = getPriceModeAndColumn();
    if (mode === "manual"){
      return parseMoney(els.manualTotal ? els.manualTotal.value : 0);
    }
    return calculateAutoTotal(col);
  }

  function syncTotalsAndStripe(){
    const { mode } = getPriceModeAndColumn();
    setManualModeUI(mode === "manual");

    const total = getCurrentTotal();

    if (mode !== "manual" && els.totalSpan){
      els.totalSpan.textContent = format2(total);
    }

    // Stripe warning
    if (!els.stripeWarn || !els.stripeLimit || !els.saveBtn) return;

    const over = total > STRIPE_MAX_TOTAL;
    els.stripeLimit.textContent = format2(total);

    if (over){
      els.stripeWarn.classList.remove('hidden');

      const acked = !!(els.stripeAck && els.stripeAck.checked);
      // In demo: disable save until ack checked (matches your real â€œrestrictâ€ behavior)
      els.saveBtn.disabled = !acked;
      els.saveBtn.classList.toggle('btn-disabled-stripe', !acked);
    } else {
      els.stripeWarn.classList.add('hidden');
      if (els.stripeAck) els.stripeAck.checked = false;

      els.saveBtn.disabled = false;
      els.saveBtn.classList.remove('btn-disabled-stripe');
    }

    updateStripeCurrencyPrefix();
  }

  // -----------------------------
  // Add Field button = add NEW ROW (like your generate_invoice.php behavior)
  // -----------------------------
  function addNewRow(){
    const empty = headers.map(() => "");
    rows.push(empty);
    rowEnabled.push(true);
    renderTable();
    syncTotalsAndStripe();
  }

  // -----------------------------
  // Other UI toggles (due time + recurring + bank drawer)
  // -----------------------------
  function wireOtherControls(){
    if (els.toggleDueTime && els.dueTimeWrap){
      els.toggleDueTime.addEventListener('change', () => {
        els.dueTimeWrap.style.display = els.toggleDueTime.checked ? "block" : "none";
      });
    }

    if (els.recurringBtn && els.recurringText){
      els.recurringBtn.addEventListener('click', () => {
        const on = els.recurringBtn.classList.contains('recurring-on');
        els.recurringBtn.classList.toggle('recurring-on', !on);
        els.recurringBtn.classList.toggle('recurring-off', on);
        els.recurringText.textContent = !on ? "Enabled (Monthly)" : "Disabled (One-time)";
      });
    }

    if (els.toggleBank && els.bankDrawer){
      els.toggleBank.addEventListener('change', () => {
        els.bankDrawer.classList.toggle('open', !!els.toggleBank.checked);
        renderBankingDetails();  // âœ… Update Bill To section when checkbox changes
      });
    }
  }

  // -----------------------------
  // Public sync() (your tab switch calls this)
  // -----------------------------
  function sync(){
    ensureDefaultDates();
    fillCurrencyOptions();
    renderCompanyInfo();         // âœ… render company details from Step 1
    applyIncludeColsFromStep2(); // reflects Step 2 choices
    renderTitleBarSwatches();
    renderColumnToggles();
    renderTable();
    syncTotalsAndStripe();
  }

  // âœ… ACCUMULATOR: Collect all Step 3 sync functions without overwriting
  window.DocuBillsDemoStep3 = window.DocuBillsDemoStep3 || {};
  window.DocuBillsDemoStep3.__syncFunctions = [];
  window.DocuBillsDemoStep3.__registerSync = function(fn) {
    if (typeof fn === 'function') {
      this.__syncFunctions.push(fn);
    }
  };

  // expose so tab switching can force a refresh
  window.DocuBillsDemoStep3.__registerSync(sync);

  // -----------------------------
  // Event wiring
  // -----------------------------
  if (els.currencySelect){
    els.currencySelect.addEventListener('change', () => {
      updateStripeCurrencyPrefix();
      syncTotalsAndStripe();
    });
  }

  if (els.manualTotal){
    els.manualTotal.addEventListener('input', () => {
      syncTotalsAndStripe();
    });
  }

  if (els.stripeAck){
    els.stripeAck.addEventListener('change', () => {
      syncTotalsAndStripe();
    });
  }

  if (els.addFieldBtn){
    els.addFieldBtn.addEventListener('click', addNewRow);
  }

  wireOtherControls();

  // Wire up banking input fields to update Bill To section in real-time
  const bankingInputs = [
    '#demoAccountHolder',
    '#demoBankName',
    '#demoAccountNumber',
    '#demoIBAN',
    '#demoSWIFT',
    '#demoRoutingCode',
    '#demoPaymentInstructions'
  ];

  bankingInputs.forEach(selector => {
    const input = root.querySelector(selector);
    if (input) {
      input.addEventListener('input', () => {
        if (els.toggleBank && els.toggleBank.checked) {
          renderBankingDetails();
        }
      });
    }
  });

  // âœ… Reset function for Step 3 data (inside main IIFE to access variables)
  window.DocuBillsDemoStep3 = window.DocuBillsDemoStep3 || {};
  window.DocuBillsDemoStep3.__resetData = function(originalHeaders) {
    // Store original data
    const originalRows = [
      ["2026-01-01","Downtown","Airport","18","45.00","810.00","105.30","915.30"],
      ["2026-01-02","Mall","Hotel","12","45.00","540.00","70.20","610.20"],
      ["2026-01-03","Office","Station","8","45.00","360.00","46.80","406.80"],
      ["2026-01-04","Clinic","Home","6","45.00","270.00","35.10","305.10"]
    ];
    
    // Reset headers array (modify in place since it's const)
    if (originalHeaders && Array.isArray(originalHeaders)) {
      headers.length = 0;
      headers.push(...originalHeaders);
    }
    
    // Reset rows array
    rows.length = 0;
    rows.push(...originalRows.map(r => [...r]));
    
    // Reset row enabled flags
    rowEnabled = rows.map(() => true);
    
    // Reset visible columns to all
    visibleCols = new Set(headers.map((_,i)=>i));
    
    // Reset currency to CAD
    if (els.currencySelect) els.currencySelect.value = 'CAD';
    
    // Reset manual total input
    if (els.manualTotal) els.manualTotal.value = '';
    
    // Reset invoice date to today
    if (els.invoiceDate) {
      const today = new Date();
      els.invoiceDate.value = today.toISOString().split('T')[0];
    }
    
    // Reset due date to 30 days from today
    if (els.dueDate) {
      const due = new Date();
      due.setDate(due.getDate() + 30);
      els.dueDate.value = due.toISOString().split('T')[0];
    }
    
    // Reset invoice time
    if (els.invoiceTime) {
      const now = new Date();
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      els.invoiceTime.value = `${hours}:${minutes}`;
    }
    
    // Reset title bar color to default
    const defaultColor = "#0033D9";
    const st = getState();
    st.titlebar_color = defaultColor;
    
    if (els.swatchRow) {
      els.swatchRow.querySelectorAll('.color-swatch').forEach(btn => {
        btn.classList.toggle('is-selected', btn.dataset.color === defaultColor);
      });
    }
    if (els.titlePreview) {
      els.titlePreview.style.background = defaultColor;
      els.titlePreview.style.color = "#ffffff";
    }
    
    // Reset banking toggle
    if (els.toggleBank) els.toggleBank.checked = false;
    if (els.bankDrawer) els.bankDrawer.classList.remove('open');
    
    // Reset banking fields
    const bankingFields = [
      '#demoAccountHolder', '#demoBankName', '#demoAccountNumber',
      '#demoIBAN', '#demoSWIFT', '#demoRoutingCode', '#demoPaymentInstructions'
    ];
    bankingFields.forEach(selector => {
      const field = root.querySelector(selector);
      if (field) field.value = '';
    });
    
    // Reset recurring toggle
    if (els.recurringBtn) {
      els.recurringBtn.classList.remove('recurring-on');
      els.recurringBtn.classList.add('recurring-off');
    }
    if (els.recurringText) els.recurringText.textContent = 'Disabled (One-time)';
    
    // Reset due time toggle
    if (els.toggleDueTime) els.toggleDueTime.checked = false;
    if (els.dueTimeWrap) els.dueTimeWrap.style.display = 'none';
    
    // Reset Stripe warning acknowledgment
    if (els.stripeAck) els.stripeAck.checked = false;
    
    // Re-render everything
    sync();
  };

  sync();
})();
</script>

<script>
(function () {
  function applyStep2ColumnSelectionToStep3() {
    const state = window.DOCUBILLS_DEMO_STATE || {};
    const includeRaw = Array.isArray(state.include_cols) ? state.include_cols : null;

    // If Step 2 never ran, don't change anything
    if (!includeRaw) return;

    const include = includeRaw
      .map(n => Number(n))
      .filter(n => Number.isInteger(n) && n >= 0);

    const includeSet = new Set(include);

    const table = document.getElementById('demoInvoiceTable');
    const togglesWrap = document.getElementById('demoColumnToggles');

    if (!table || !togglesWrap) return;

    const theadRow = table.querySelector('thead tr');
    if (!theadRow) return;

    const headerCells = Array.from(theadRow.children);
    const bodyRows = Array.from(table.querySelectorAll('tbody tr'));

    // Detect if there's a row-checkbox column at index 0 (your Step 3 has it)
    let offset = 0;
    if (headerCells[0]) {
      const first = headerCells[0];
      const hasCheckbox = !!first.querySelector('input[type="checkbox"]');
      const looksBlank = first.textContent.trim() === '';
      if (hasCheckbox || looksBlank) offset = 1;
    }

    // Determine how many "data" columns exist in Step 3
    const dataColCount = Math.max(0, headerCells.length - offset);

    // If include list is empty (edge case), hide ALL data cols
    // (This matches your request: unchecked => should not appear)
    for (let dataIdx = 0; dataIdx < dataColCount; dataIdx++) {
      const domIdx = dataIdx + offset;
      const shouldShow = includeSet.has(dataIdx);

      // 1) Hide/show table header + cells
      if (headerCells[domIdx]) headerCells[domIdx].style.display = shouldShow ? '' : 'none';

      bodyRows.forEach(tr => {
        const cell = tr.children[domIdx];
        if (cell) cell.style.display = shouldShow ? '' : 'none';
      });

      // 2) Hide/show the toggle checkbox in Step 3
      // Your toggles are injected in the same order as headers (no offset in toggles list)
      const toggleItem = togglesWrap.children[dataIdx];
      if (toggleItem) {
        toggleItem.style.display = shouldShow ? '' : 'none';

        // IMPORTANT: prevent Step 3 scripts from re-showing hidden cols later
        const toggleInput = toggleItem.querySelector('input[type="checkbox"]');
        if (toggleInput) {
          toggleInput.checked = !!shouldShow;
          toggleInput.disabled = !shouldShow;
        }
      }
    }
  }

  // Run when Step 3 becomes active (works for both click AND DocuBillsDemo.go(3))
  const step3Panel = document.getElementById('panel-step3');
  if (step3Panel) {
    const obs = new MutationObserver(() => {
      if (step3Panel.classList.contains('active')) {
        // run a couple times to catch any late DOM rendering
        applyStep2ColumnSelectionToStep3();
        requestAnimationFrame(applyStep2ColumnSelectionToStep3);
        setTimeout(applyStep2ColumnSelectionToStep3, 50);
      }
    });
    obs.observe(step3Panel, { attributes: true, attributeFilter: ['class'] });
  }

  // Also run if user clicks Step 3 tab directly
  const step3Tab = document.getElementById('tab-step3');
  if (step3Tab) step3Tab.addEventListener('click', () => {
    applyStep2ColumnSelectionToStep3();
    setTimeout(applyStep2ColumnSelectionToStep3, 30);
  });
  
  /* ============================================================
     âœ… FIX: Manual pricing must show a textbox in Step 3
     - If Step 2 is manual -> show #demoManualTotalInput and hide #demoTotalAmount
     - If Step 2 is auto   -> hide input and show computed total
  ============================================================ */
  (function demoPricingSyncFix(){
    const root = document.getElementById('demoStep3');
    if (!root) return;

    const STRIPE_MAX_TOTAL = 999999.99;
    const headers = ["Trip Date","Pickup","Dropoff","KM","Rate","Sub Total","Tax","Total"];

    const manualInput = root.querySelector('#demoManualTotalInput');
    const totalSpan = root.querySelector('#demoTotalAmount');
    if (!manualInput || !totalSpan) return;

    const warnBox = root.querySelector('#demoStripeWarning');
    const warnDisp = root.querySelector('#demoStripeLimitDisplay');
    const warnAck = root.querySelector('#demoManualOnlyAck');
    const saveBtn = root.querySelector('#demoSaveBtn');

    function getState(){
      return window.DOCUBILLS_DEMO_STATE || {};
    }
    function setState(patch){
      window.DOCUBILLS_DEMO_STATE = Object.assign({}, getState(), patch);
      return window.DOCUBILLS_DEMO_STATE;
    }

    function parseNumber(v){
      const n = parseFloat(String(v ?? '').replace(/[^0-9.-]/g, ''));
      return Number.isFinite(n) ? n : 0;
    }

    function getPriceDomIndex(colName){
      const idx = headers.indexOf(colName);
      if (idx < 0) return -1;
      return idx + 1; // +1 because first column is the row checkbox in this demo
    }

    function calcAutoTotal(colName){
      const domIdx = getPriceDomIndex(colName);
      if (domIdx < 0) return 0;

      let sum = 0;
      const trs = root.querySelectorAll('#demoInvoiceTable tbody tr');
      trs.forEach(tr => {
        const cb = tr.querySelector('td:first-child input[type="checkbox"]');
        if (cb && !cb.checked) return;

        const cell = tr.children[domIdx];
        if (!cell) return;

        sum += parseNumber(cell.textContent);
      });
      return sum;
    }

    function updateStripeUI(total){
      if (!warnBox || !warnDisp) return;

      warnDisp.textContent = Number(total).toFixed(2);

      // update prefix in warning (if present)
      const prefix = (typeof CURRENCY_DISPLAY !== 'undefined') ? CURRENCY_DISPLAY : '';
      root.querySelectorAll('.currencyPrefix').forEach(el => { el.textContent = prefix; });

      const over = total > STRIPE_MAX_TOTAL;
      warnBox.classList.toggle('hidden', !over);

      if (saveBtn){
        if (over && warnAck && !warnAck.checked){
          saveBtn.classList.add('btn-disabled-stripe');
          saveBtn.disabled = true;
        } else {
          saveBtn.classList.remove('btn-disabled-stripe');
          saveBtn.disabled = false;
        }
      }
    }

    function applyPricingUI(){
      const st = getState();
      const mode = (st.price_mode === 'manual') ? 'manual' : 'column';

      if (mode === 'manual'){
        manualInput.style.display = 'inline-block';
        totalSpan.style.display = 'none';

        if (st.manual_total !== undefined && String(manualInput.value) !== String(st.manual_total)){
          manualInput.value = st.manual_total;
        }

        const total = parseNumber(manualInput.value);
        // keep span updated (some existing logic may read it)
        totalSpan.textContent = total.toFixed(2);
        updateStripeUI(total);
        return;
      }

      // column mode
      manualInput.style.display = 'none';
      totalSpan.style.display = 'inline-block';

      const col = st.price_column || 'Sub Total';
      const total = calcAutoTotal(col);

      totalSpan.textContent = total.toFixed(2);
      updateStripeUI(total);
    }

    // Manual input -> store + recalc
    manualInput.addEventListener('input', () => {
      setState({ price_mode: 'manual', manual_total: manualInput.value, price_column: null });
      applyPricingUI();
    });

    // Recalc when table row checkboxes change + when warning ack changes
    root.addEventListener('change', (e) => {
      if (e.target && e.target.closest('#demoInvoiceTable')) applyPricingUI();
      if (e.target === warnAck) applyPricingUI();
    });

    // Expose for Step 2 submit hook
    window.DocuBillsDemoStep3 = window.DocuBillsDemoStep3 || {};
    window.DocuBillsDemoStep3.__registerSync(applyPricingUI);

    // If user clicks Step 3 tab directly
    const tab3 = document.getElementById('tab-step3');
    if (tab3) tab3.addEventListener('click', applyPricingUI);

    // Initial
    applyPricingUI();
  })();
  
  /* =========================================================
     âœ… FIX: Required column pill + locked checkbox
     - MANUAL pricing: lock "Sub Total"
     - AUTO pricing: lock selected price column (or fallback "Sub Total")
     This runs whenever Step 2 calls Step3.sync()
  ========================================================= */

  function __db_getState(){
    return window.DOCUBILLS_DEMO_STATE || {};
  }

  function __db_requiredCol(){
    const st = __db_getState();
    const mode = String(st.price_mode || 'column').toLowerCase();

    // âœ… Manual pricing: no column is required for total calculation
    if (mode === 'manual') return null;

    // âœ… Auto pricing locks the selected price column
    const chosen = st.price_column ? String(st.price_column) : 'Sub Total';
    return chosen || 'Sub Total';
  }

  function __db_applyManualUI(){
    const root = document.getElementById('demoStep3');
    if (!root) return;

    const st = __db_getState();
    const isManual = String(st.price_mode || '').toLowerCase() === 'manual';

    const manualInput = root.querySelector('#demoManualTotalInput');
    const totalSpan   = root.querySelector('#demoTotalAmount');

    if (manualInput) manualInput.style.display = isManual ? 'block' : 'none';
    if (totalSpan)   totalSpan.style.display   = isManual ? 'none'  : 'inline-block';
  }

  function __db_applyRequiredPillAndLock(){
    const root = document.getElementById('demoStep3');
    if (!root) return false;

    const required = __db_requiredCol();
    const wrap = root.querySelector('#demoColumnToggles');
    if (!wrap) return false;

    const items = Array.from(wrap.querySelectorAll('.column-toggle-item'));
    if (!items.length) return false;

    // If manual pricing mode, no column is required - remove all pills and locks
    if (!required) {
      items.forEach(item => {
        const cb = item.querySelector('input[type="checkbox"]');
        if (!cb) return;

        // Remove pill if present
        const oldPill = item.querySelector('.required-pill');
        if (oldPill) oldPill.remove();

        // Remove locks that WE applied
        if (cb.dataset.reqLock === '1') {
          cb.disabled = false;
          delete cb.dataset.reqLock;
        }

        item.classList.remove('price-column-label');
      });
      return true;
    }

    items.forEach(item => {
      const cb = item.querySelector('input[type="checkbox"]');
      if (!cb) return;

      // Remove pill we previously injected
      const oldPill = item.querySelector('.required-pill');
      if (oldPill) oldPill.remove();

      // Determine column name robustly
      let col = (item.getAttribute('data-col') || item.dataset.col || '').trim();
      if (!col) {
        col = (item.textContent || '')
          .replace(/REQUIRED FOR TOTAL/i, '')
          .trim();
        item.dataset.col = col; // cache it
      }

      const isReq = col.toLowerCase() === String(required).toLowerCase();

      // Only undo locks that WE applied
      if (cb.dataset.reqLock === '1' && !isReq){
        cb.disabled = false;
        delete cb.dataset.reqLock;
      }

      item.classList.remove('price-column-label');

      if (isReq){
        // Force checked + trigger your existing handlers BEFORE disabling
        cb.checked = true;
        cb.dispatchEvent(new Event('change', { bubbles: true }));

        cb.disabled = true;
        cb.dataset.reqLock = '1';

        item.classList.add('price-column-label');

        const pill = document.createElement('span');
        pill.className = 'required-pill';
        pill.textContent = 'REQUIRED FOR TOTAL';
        item.appendChild(pill);
      }
    });

    return true;
  }

  function __db_syncRequiredUI(){
    __db_applyManualUI();

    // Step 3 may render toggles slightly later â€” retry a few frames
    let tries = 0;
    (function tick(){
      const ok = __db_applyRequiredPillAndLock();
      if (ok) return;
      tries++;
      if (tries < 120) requestAnimationFrame(tick); // ~2s max
    })();
  }

  // âœ… Master sync executor: runs all registered functions in order
  window.DocuBillsDemoStep3 = window.DocuBillsDemoStep3 || {};
  window.DocuBillsDemoStep3.sync = function(){
    // Execute all accumulated sync functions
    const fns = window.DocuBillsDemoStep3.__syncFunctions || [];
    fns.forEach((fn, idx) => {
      try {
        fn();
      } catch(e){
        console.warn(`Step3 sync[${idx}] failed:`, e);
      }
    });

    // Always run required UI sync last
    __db_syncRequiredUI();
  };

  // âœ… Also run once on load + when Step 3 tab is clicked
  window.DocuBillsDemoStep3.sync();
  const __tab3 = document.getElementById('tab-step3');
  if (__tab3){
    __tab3.addEventListener('click', () => window.DocuBillsDemoStep3.sync());
  }

})();
</script>

<script>
// Signup Modal Handlers - Initialize when DOM is ready
(function() {
  function initSignupModal() {
    const signupModal = document.getElementById('demoSignupModal');
    if (!signupModal) return;
    
    function showSignupModal(e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      signupModal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }
    
    function hideSignupModal(e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      signupModal.style.display = 'none';
      document.body.style.overflow = '';
    }
    
    // Use event delegation for Save Invoice buttons (handles clicks on button or child elements like icons)
    document.addEventListener('click', function(e) {
      const btn = e.target.closest('#demoSaveBtn, #demoSaveBtnBottom');
      if (btn) {
        showSignupModal(e);
        return false;
      }
    }, true); // Use capture phase to catch early
    
    // Close modal handlers - use event delegation
    document.addEventListener('click', function(e) {
      if (e.target.closest('#demoSignupModalClose')) {
        hideSignupModal(e);
        return false;
      }
      
      // Close modal when clicking overlay
      if (e.target.classList.contains('demo-signup-modal-overlay')) {
        hideSignupModal(e);
        return false;
      }
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && signupModal.style.display === 'flex') {
        hideSignupModal(e);
      }
    });
  }
  
  // Initialize modal handlers when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSignupModal);
  } else {
    initSignupModal();
  }
})();
</script>

<!-- Signup Modal -->
<div id="demoSignupModal" class="demo-signup-modal" style="display: none;">
  <div class="demo-signup-modal-overlay"></div>
  <div class="demo-signup-modal-content">
    <button type="button" class="demo-signup-modal-close" id="demoSignupModalClose">
      <i class="fas fa-times"></i>
    </button>
    
    <h2 class="demo-signup-modal-title">To finalize your first invoice, sign up for free!</h2>
    <p class="demo-signup-modal-intro">You're one step away from generating a PDF, adding Stripe "Pay Now", and enabling automated reminders.</p>
    
    <div class="demo-signup-features-box">
      <div class="demo-signup-features-header">
        <i class="fas fa-star"></i>
        <strong>Unlocked after signup</strong>
      </div>
      <ul class="demo-signup-features-list">
        <li><i class="fas fa-check"></i> Generate invoice PDF</li>
        <li><i class="fas fa-check"></i> Stripe payment links</li>
        <li><i class="fas fa-check"></i> Email cadences + reminders</li>
        <li><i class="fas fa-check"></i> Recurring invoices</li>
      </ul>
    </div>
    
    <div class="demo-signup-modal-actions">
      <a href="<?php echo e(route('login')); ?>" class="demo-signup-btn-primary">
        Sign up free <i class="fas fa-arrow-right"></i>
      </a>
      <a href="<?php echo e(route('login')); ?>" class="demo-signup-btn-secondary">
        I already have an account
      </a>
    </div>
    
    <p class="demo-signup-modal-footer">No credit card required to start.</p>
  </div>
</div>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.homepage', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Invoice\Docubills web\Docubills\Docubills\resources\views/home/landing.blade.php ENDPATH**/ ?>