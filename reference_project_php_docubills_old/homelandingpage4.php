
"<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocuBills - Generate Custom Invoices in 3 Simple Steps</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --accent: #06d6a0;
            --text-dark: #1a1a2e;
            --text-light: #6c757d;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 15px 40px rgba(0, 0, 0, 0.12);
            --radius: 12px;
            --transition: all 0.3s ease;
            /* ✅ Hero image size controls (edit these anytime) */
            --hero-img-max-width: 560px;   /* image max width on desktop */
            --hero-img-height: 520px;      /* ✅ increase this to make image taller */
            --hero-img-fit: contain;       /* ✅ keeps original ratio (no distortion) */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            line-height: 1.2;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        section {
            padding: 80px 0;
        }

        .btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: 'Poppins', sans-serif;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-secondary:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .btn-accent {
            background-color: var(--accent);
            color: white;
        }

        .btn-accent:hover {
            background-color: #05c290;
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        /* Header & Navigation */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            background-color: var(--white);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            padding: 15px 0;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }

        .logo-icon {
            color: var(--secondary);
        }

        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
        }

        .nav-links a {
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-actions {
            display: flex;
            gap: 15px;
        }

        .mobile-menu-btn {
            display: none;
            font-size: 24px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-dark);
        }

        /* Hero Section */
        /* ✅ Desktop hero layout: text left, image right */
        .hero-content{
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 60px;
        }
        
        .hero-text{
            flex: 0 1 560px;   /* left side */
            max-width: 600px;
        }
        
        .hero-image{
            flex: 0 1 var(--hero-img-max-width);
            max-width: var(--hero-img-max-width);
            height: var(--hero-img-height);
            position: relative;
            display: flex;
            justify-content: flex-end;
            align-items: stretch;
            min-width: 320px;
        
            /* ✅ makes "empty space" look intentional */
            background: rgba(255,255,255,0.55);
            border-radius: var(--radius);
            padding: 10px; /* matches your image border thickness */
        }
        
        .hero {
            padding-top: 140px;
            padding-bottom: 60px;
            background: linear-gradient(135deg, #f5f7ff 0%, #f0f4ff 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-text {
            flex: 1;
            max-width: 600px;
        }

        .hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 14px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 14px;
        background-color: rgba(67, 97, 238, 0.12);
        color: var(--primary);
        border: 1px solid rgba(67, 97, 238, 0.25);
        margin-bottom: 16px;
        font-family: 'Poppins', sans-serif;
        }
    
        .hero-pill i {
        color: var(--secondary);
        font-size: 14px;
        }

        .hero-text h1 {
            font-size: 48px;
            margin-bottom: 20px;
            color: var(--text-dark);
        }

        .hero-text h1 span {
            color: var(--primary);
        }

        .hero-text p {
            font-size: 18px;
            color: var(--text-light);
            margin-bottom: 30px;
        }

        .hero-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .dashboard-preview {
          width: 100%;
          height: 100%;
          max-width: 100%;                 /* ✅ don’t cap it again */
          display: block;
          border-radius: var(--radius);
          box-shadow: var(--shadow);
          transform: perspective(1000px) rotateY(-10deg);
          transition: var(--transition);
          border: none;
          object-fit: var(--hero-img-fit); /* cover */
          object-position: center;
        }

        .dashboard-preview:hover {
            transform: perspective(1000px) rotateY(-5deg);
        }

        .floating-badge {
            position: absolute;
            background-color: var(--accent);
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 8px;
            animation: float 3s ease-in-out infinite;
        }

        .badge-1 {
            top: 20px;
            left: -20px;
            animation-delay: 0s;
        }

        .badge-2 {
            bottom: 40px;
            right: -10px;
            animation-delay: 1s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Features Section */
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-size: 36px;
            margin-bottom: 15px;
        }

        .section-header p {
            font-size: 18px;
            color: var(--text-light);
            max-width: 700px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .feature-card {
            background-color: var(--white);
            border-radius: var(--radius);
            padding: 30px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background-color: rgba(67, 97, 238, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: var(--primary);
            font-size: 24px;
        }

        .feature-card h3 {
            font-size: 22px;
            margin-bottom: 15px;
        }

        .feature-card p {
            color: var(--text-light);
            margin-bottom: 20px;
        }

        /* Steps Section */
        .steps-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 40px;
            margin-bottom: 60px;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 30px 20px;
            position: relative;
        }

        .step-number {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            margin: 0 auto 25px;
            position: relative;
        }

        .step-number::after {
            content: '';
            position: absolute;
            width: 2px;
            height: 70px;
            background-color: #e9ecef;
            bottom: -70px;
            left: 50%;
            transform: translateX(-50%);
        }

        .step:last-child .step-number::after {
            display: none;
        }

        .step h3 {
            font-size: 22px;
            margin-bottom: 15px;
        }

        /* Interactive Demo Section */
        .demo-section {
            background-color: var(--light-bg);
            border-radius: var(--radius);
            padding: 60px;
            text-align: center;
        }

        .demo-header {
            margin-bottom: 40px;
        }

        .demo-header h2 {
            font-size: 36px;
            margin-bottom: 15px;
        }

        .demo-placeholder {
            background-color: white;
            border-radius: var(--radius);
            padding: 60px 40px;
            box-shadow: var(--shadow);
            margin-bottom: 40px;
            border: 2px dashed #dee2e6;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .demo-icon {
            font-size: 80px;
            color: var(--primary);
            margin-bottom: 20px;
            opacity: 0.7;
        }

        .demo-placeholder h3 {
            font-size: 28px;
            margin-bottom: 15px;
        }

        .demo-placeholder p {
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto 25px;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            text-align: center;
            border-radius: var(--radius);
            padding: 80px 40px;
            margin: 60px 0;
        }

        .cta-section h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .cta-section p {
            font-size: 18px;
            margin-bottom: 40px;
            opacity: 0.9;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Footer */
        footer {
            background-color: var(--text-dark);
            color: white;
            padding: 60px 0 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-size: 20px;
            margin-bottom: 25px;
            color: var(--white);
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #adb5bd;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--white);
            padding-left: 5px;
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #2d3748;
            color: #adb5bd;
            font-size: 14px;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
          
            .hero-pill {
    margin-left: auto;
    margin-right: auto;
}

            .hero-text {
                text-align: center;
            }
            
            .hero-text h1 {
                font-size: 40px;
            }
            
            .steps-container {
                flex-direction: column;
            }
            
            .step-number::after {
                width: 70px;
                height: 2px;
                bottom: -35px;
                left: calc(50% + 30px);
                transform: translateY(-50%);
            }
            
            .demo-section {
                padding: 40px 20px;
            }
        }

        @media (max-width: 768px) {
            .nav-links, .nav-actions {
                display: none;
            }
             
            .mobile-menu-btn {
                display: block;
            }
            
            .hero-text h1 {
                font-size: 32px;
            }
            
            .section-header h2 {
                font-size: 28px;
            }
            
            .cta-section h2 {
                font-size: 28px;
            }
            
            .demo-placeholder {
                padding: 40px 20px;
            }
            
            .feature-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo">
                    <i class="fas fa-file-invoice-dollar logo-icon"></i>
                    <span>DocuBills</span>
                </a>
                
                <ul class="nav-links">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#demo">Demo</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                
                <div class="nav-actions">
                    <a href="#" class="btn btn-secondary">Log In</a>
                    <a href="#signup" class="btn btn-primary">Sign Up Free</a>
                </div>
                
                <button class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="hero-pill">
                    <i class="fas fa-bolt"></i>
                    <span>3 steps. No mapping. Full control.</span>
                    </div>

                    <h1>Generate Custom Invoices in <span>3 Simple Steps</span></h1>
                    <p>The only invoice generator that eliminates column mapping complexity. Upload your spreadsheet, select columns, and get professional invoices instantly. No hassle, no confusion.</p>
                    
                    <div class="hero-actions">
                        <a href="#signup" class="btn btn-primary btn-accent">
                            <i class="fas fa-rocket"></i> Start Free Trial
                        </a>
                        <a href="#demo" class="btn btn-secondary">
                            <i class="fas fa-play-circle"></i> Watch Demo
                        </a>
                    </div>
                    
                   
                </div>
                
                <div class="hero-image">
                    <img src="assets/hero.png" alt="Invoice Dashboard Preview" class="dashboard-preview"
                        onerror="this.onerror=null; this.src='https://placehold.co/900x650/png?text=DocuBills+Hero+Image';">

                    <div class="floating-badge badge-1">
                        <i class="fas fa-bolt"></i>
                        <span>3-Step Process</span>
                    </div>
                    
                    <div class="floating-badge badge-2">
                        <i class="fas fa-robot"></i>
                        <span>Automated Reminders</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features">
        <div class="container">
            <div class="section-header">
                <h2>Everything You Need for Effortless Invoicing</h2>
                <p>DocuBills combines simplicity with powerful features to streamline your billing process from start to finish.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-upload"></i>
                    </div>
                    <h3>Upload & Generate</h3>
                    <p>Upload Excel or Google Sheets via URL. Our smart system automatically detects columns and calculates invoice totals.</p>
                    <ul style="color: var(--text-light); padding-left: 20px;">
                        <li>No complex column mapping</li>
                        <li>Smart data detection</li>
                        <li>Support for multiple formats</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-columns"></i>
                    </div>
                    <h3>Include & Exclude</h3>
                    <p>Easily include or exclude columns and rows from your final invoice. Perfect for removing accidental entries or sensitive data.</p>
                    <ul style="color: var(--text-light); padding-left: 20px;">
                        <li>Column/row selection</li>
                        <li>Real-time preview</li>
                        <li>Data cleanup tools</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h3>Recurring & Customizable</h3>
                    <p>Set up recurring invoices, add banking info, select currency symbols, and customize every detail directly from the preview page.</p>
                    <ul style="color: var(--text-light); padding-left: 20px;">
                        <li>Recurring invoice scheduling</li>
                        <li>Multi-currency support</li>
                        <li>Banking details integration</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <h3>Automated Follow-ups</h3>
                    <p>Set custom cadences for invoice reminders and write email content right in the app. Never chase clients manually again.</p>
                    <ul style="color: var(--text-light); padding-left: 20px;">
                        <li>Custom reminder schedules</li>
                        <li>Email template editor</li>
                        <li>CC/BCC controls</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Centralized Management</h3>
                    <p>Manage all invoices in one dashboard. Update statuses with one click and automatically notify relevant parties.</p>
                    <ul style="color: var(--text-light); padding-left: 20px;">
                        <li>Unified invoice dashboard</li>
                        <li>One-click status updates</li>
                        <li>Automated notifications</li>
                    </ul>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Manage Accountants</h3>
                    <p>Granular control over every feature with role options and individual permission matrices for complete oversight.</p>
                    <ul style="color: var(--text-light); padding-left: 20px;">
                        <li>Role-based access control</li>
                        <li>Permission matrix</li>
                        <li>Audit logs</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" style="background-color: var(--light-bg);">
        <div class="container">
            <div class="section-header">
                <h2>How It Works: Invoice Generation in 3 Steps</h2>
                <p>Our streamlined process saves you hours of manual work every month</p>
            </div>
            
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Upload Your Data</h3>
                    <p>Upload Excel or connect Google Sheets via URL. No need to manually map columns or reformat your data.</p>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Customize & Preview</h3>
                    <p>Select columns to include, add branding, set currency, and preview the invoice exactly as it will appear.</p>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Send & Manage</h3>
                    <p>Send invoices directly to clients, set up automated reminders, and track payments all in one place.</p>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="#signup" class="btn btn-primary" style="padding: 16px 40px; font-size: 18px;">
                    <i class="fas fa-play-circle"></i> Get Started in Minutes
                </a>
            </div>
        </div>
    </section>

    <!-- Interactive Demo Section -->
    <section id="demo">
        <div class="container">
            <div class="demo-section">
                <div class="demo-header">
                    <h2>Interactive Demo</h2>
                    <p>Experience the power of DocuBills firsthand. Try our interactive demo to see how easy invoice generation can be.</p>
                </div>
                
                <div class="demo-placeholder" id="interactive-demo">
                    <i class="fas fa-laptop-code demo-icon"></i>
                    <h3>Interactive Demo Coming Soon</h3>
                    <p>This area will contain an interactive demo where visitors can experience the invoice generation process firsthand. The demo will allow users to upload a sample spreadsheet, select columns, customize the invoice, and see the final result.</p>
                    <p>For now, here's a preview of what you can do with DocuBills:</p>
                    
                    <div style="display: flex; gap: 15px; margin-top: 20px; flex-wrap: wrap; justify-content: center;">
                        <div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); min-width: 200px;">
                            <i class="fas fa-upload" style="color: var(--primary); margin-bottom: 10px; font-size: 24px;"></i>
                            <h4 style="margin-bottom: 8px;">Upload Data</h4>
                            <p style="font-size: 14px;">Drag & drop or import from Google Sheets</p>
                        </div>
                        
                        <div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); min-width: 200px;">
                            <i class="fas fa-sliders-h" style="color: var(--primary); margin-bottom: 10px; font-size: 24px;"></i>
                            <h4 style="margin-bottom: 8px;">Customize</h4>
                            <p style="font-size: 14px;">Select columns, add branding, set currency</p>
                        </div>
                        
                        <div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); min-width: 200px;">
                            <i class="fas fa-file-pdf" style="color: var(--primary); margin-bottom: 10px; font-size: 24px;"></i>
                            <h4 style="margin-bottom: 8px;">Generate PDF</h4>
                            <p style="font-size: 14px;">Download or send directly to clients</p>
                        </div>
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <p style="margin-bottom: 25px; color: var(--text-light); max-width: 700px; margin-left: auto; margin-right: auto;">
                        <i class="fas fa-info-circle" style="color: var(--primary); margin-right: 8px;"></i>
                        The interactive demo will be implemented with JavaScript to simulate the actual invoice generation workflow.
                    </p>
                    <a href="#signup" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Sign Up for Early Access
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="signup">
        <div class="container">
            <div class="cta-section">
                <h2>Start Generating Professional Invoices Today</h2>
                <p>Join thousands of businesses that have streamlined their billing process with DocuBills. No credit card required for the free trial.</p>
                
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; margin-bottom: 30px;">
                    <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; min-width: 200px;">
                        <i class="fas fa-check-circle" style="color: var(--accent); margin-right: 8px;"></i>
                        <span>14-day free trial</span>
                    </div>
                    <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; min-width: 200px;">
                        <i class="fas fa-check-circle" style="color: var(--accent); margin-right: 8px;"></i>
                        <span>No credit card required</span>
                    </div>
                    <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; min-width: 200px;">
                        <i class="fas fa-check-circle" style="color: var(--accent); margin-right: 8px;"></i>
                        <span>Cancel anytime</span>
                    </div>
                </div>
                
                <div style="max-width: 500px; margin: 0 auto;">
                    <form id="signup-form" style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <input type="email" placeholder="Enter your work email" style="flex: 1; min-width: 250px; padding: 16px 20px; border-radius: var(--radius); border: none; font-size: 16px;">
                        <button type="submit" class="btn btn-accent" style="padding: 16px 30px;">
                            Start Free Trial <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                    <p style="font-size: 14px; margin-top: 15px; opacity: 0.8;">By signing up, you agree to our Terms of Service and Privacy Policy.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>DocuBills</h3>
                    <p style="color: #adb5bd; margin-bottom: 20px;">The simplest way to generate professional invoices from your spreadsheets.</p>
                    <div style="display: flex; gap: 15px;">
                        <a href="#" style="color: #adb5bd; font-size: 20px;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color: #adb5bd; font-size: 20px;"><i class="fab fa-linkedin"></i></a>
                        <a href="#" style="color: #adb5bd; font-size: 20px;"><i class="fab fa-facebook"></i></a>
                        <a href="#" style="color: #adb5bd; font-size: 20px;"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Product</h3>
                    <ul class="footer-links">
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#pricing">Pricing</a></li>
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
                <p>&copy; 2023 DocuBills. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            const navLinks = document.querySelector('.nav-links');
            const navActions = document.querySelector('.nav-actions');
            
            if (navLinks.style.display === 'flex') {
                navLinks.style.display = 'none';
                navActions.style.display = 'none';
            } else {
                navLinks.style.display = 'flex';
                navActions.style.display = 'flex';
                navLinks.style.flexDirection = 'column';
                navLinks.style.position = 'absolute';
                navLinks.style.top = '70px';
                navLinks.style.left = '0';
                navLinks.style.width = '100%';
                navLinks.style.backgroundColor = 'var(--white)';
                navLinks.style.padding = '20px';
                navLinks.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
                
                navActions.style.flexDirection = 'column';
                navActions.style.position = 'absolute';
                navActions.style.top = '260px';
                navActions.style.left = '0';
                navActions.style.width = '100%';
                navActions.style.padding = '0 20px 20px';
                navActions.style.gap = '10px';
            }
        });

        // Form submission
        document.getElementById('signup-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            
            if (email) {
                alert(`Thank you for signing up! A confirmation email has been sent to ${email}. You can now access your free trial.`);
                this.querySelector('input[type="email"]').value = '';
                
                // In a real implementation, you would send this data to your server
                console.log('Signup email:', email);
            }
        });

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
                    
                    // Close mobile menu if open
                    if (window.innerWidth <= 768) {
                        document.querySelector('.nav-links').style.display = 'none';
                        document.querySelector('.nav-actions').style.display = 'none';
                    }
                }
            });
        });

        // Interactive demo placeholder interaction
        const demoPlaceholder = document.getElementById('interactive-demo');
        demoPlaceholder.addEventListener('click', function() {
            this.style.borderColor = 'var(--primary)';
            this.style.backgroundColor = '#f8f9ff';
            
            setTimeout(() => {
                this.style.borderColor = '#dee2e6';
                this.style.backgroundColor = 'white';
            }, 500);
            
            alert("In the actual implementation, this will launch an interactive demo where you can upload a sample spreadsheet and generate an invoice step-by-step.");
        });
        
        window.addEventListener('resize', () => {
  if (window.innerWidth > 768) {
    const navLinks = document.querySelector('.nav-links');
    const navActions = document.querySelector('.nav-actions');
    navLinks.style.display = '';
    navActions.style.display = '';
    navLinks.style.flexDirection = '';
    navLinks.style.position = '';
    navLinks.style.top = '';
    navLinks.style.left = '';
    navLinks.style.width = '';
    navLinks.style.backgroundColor = '';
    navLinks.style.padding = '';
    navLinks.style.boxShadow = '';
    navActions.style.flexDirection = '';
    navActions.style.position = '';
    navActions.style.top = '';
    navActions.style.left = '';
    navActions.style.width = '';
    navActions.style.padding = '';
    navActions.style.gap = '';
  }
});

    </script>
</body>
</html>"