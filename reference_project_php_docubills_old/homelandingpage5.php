<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocuBills | Generate Custom Invoices in 3 Simple Steps</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --primary-light: #eef2ff;
            --secondary: #7209b7;
            --accent: #06d6a0;
            --accent-dark: #05b587;
            --text-dark: #1a1a2e;
            --text-muted: #6c757d;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --border: #e9ecef;
            --shadow-sm: 0 4px 12px rgba(0, 0, 0, 0.05);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 50px rgba(0, 0, 0, 0.12);
            --radius-sm: 8px;
            --radius: 12px;
            --radius-lg: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            line-height: 1.7;
            overflow-x: hidden;
            background-color: var(--white);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1rem;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        section {
            padding: 100px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 40px;
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-title p {
            font-size: 18px;
            color: var(--text-muted);
            max-width: 700px;
            margin: 0 auto;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: 'Poppins', sans-serif;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: -100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }

        .btn:hover::after {
            left: 100%;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(67, 97, 238, 0.4);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-secondary:hover {
            background-color: rgba(67, 97, 238, 0.05);
            transform: translateY(-3px);
        }

        .btn-accent {
            background-color: var(--accent);
            color: white;
            box-shadow: 0 8px 20px rgba(6, 214, 160, 0.3);
        }

        .btn-accent:hover {
            background-color: var(--accent-dark);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(6, 214, 160, 0.4);
        }

        .btn-lg {
            padding: 14px 30px;
            font-size: 16px;
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: var(--shadow-sm);
            z-index: 1000;
            padding: 18px 0;
            transition: var(--transition);
        }

        header.scrolled {
            padding: 12px 0;
            box-shadow: var(--shadow);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 26px;
            font-weight: 800;
            color: var(--primary);
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .nav-links {
            display: flex;
            gap: 36px;
            list-style: none;
        }

        .nav-links a {
            font-weight: 500;
            position: relative;
            padding: 8px 0;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--primary);
            transition: var(--transition);
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary);
        }

        .nav-actions {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .mobile-menu-btn {
            display: none;
            width: 40px;
            height: 40px;
            background: var(--primary-light);
            border-radius: var(--radius-sm);
            border: none;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--primary);
            cursor: pointer;
        }

        /* Hero Section */
        .hero {
            padding-top: 160px;
            padding-bottom: 100px;
            background: linear-gradient(135deg, #f5f7ff 0%, #f0f4ff 100%);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(67, 97, 238, 0.1) 0%, rgba(67, 97, 238, 0) 70%);
            top: -300px;
            right: -200px;
        }

        .hero::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(114, 9, 183, 0.1) 0%, rgba(114, 9, 183, 0) 70%);
            bottom: -200px;
            left: -100px;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero-text {
            max-width: 600px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background-color: rgba(67, 97, 238, 0.12); /* ✅ pill bg */
            color: #4361ee;                            /* ✅ text color */
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 24px;
            border: 1px solid rgba(67, 97, 238, 0.22);
        }
        
        /* ✅ icon color only */
        .hero-badge i {
            color: #7209b7;
        }
        
        .hero-text h1 {
            font-size: 52px;
            margin-bottom: 24px;
            line-height: 1.1;
        }

        .hero-text h1 span {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-text p {
            font-size: 18px;
            color: var(--text-muted);
            margin-bottom: 36px;
        }

        .hero-actions {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        .hero-stats {
            display: flex;
            gap: 32px;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-muted);
        }

        .hero-image {
            position: relative;
        }

        .dashboard-preview {
            width: 100%;
            max-width: 600px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 12px solid white;
            transform: perspective(1000px) rotateY(-10deg) rotateX(5deg);
            transition: var(--transition-slow);
        
            position: relative;
            z-index: 2;
        }

        .dashboard-preview:hover {
            transform: perspective(1000px) rotateY(-5deg) rotateX(2deg);
        }

        .floating-card {
            position: absolute;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            animation: float 6s ease-in-out infinite;
            z-index: 5;
            --tx: 0px;
            --ty: 0px;
            pointer-events: none;
        }

        .floating-card-1 {
            top: 0;
            right: 0;
            --tx: 60%;
            --ty: -35%;
            animation-delay: 0s;
        }

        .floating-card-2 {
            bottom: -38px; /* ✅ half outside the frame */
            left: 18px;
            --tx: 0px;
            --ty: 0px;
            animation-delay: 2s;
        }

        .floating-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-light);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 22px;
        }

        @keyframes float {
            0%, 100% { transform: translate(var(--tx), var(--ty)); }
            50% { transform: translate(var(--tx), calc(var(--ty) - 15px)); }
        }

        /* Features Section */
        .features {
            background-color: var(--white);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .feature-card {
            background-color: var(--white);
            border-radius: var(--radius);
            padding: 40px 30px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--border);
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 0;
            background: linear-gradient(to bottom, var(--primary), var(--secondary));
            transition: var(--transition-slow);
        }

        .feature-card:hover::before {
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            color: white;
            font-size: 28px;
            transition: var(--transition);
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .feature-card h3 {
            font-size: 22px;
            margin-bottom: 16px;
        }

        .feature-card p {
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .feature-list {
            list-style: none;
        }

        .feature-list li {
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .feature-list i {
            color: var(--accent);
            font-size: 14px;
            margin-top: 5px;
        }

        /* Steps Section */
        .steps {
            background-color: var(--light-bg);
            position: relative;
            overflow: hidden;
        }

        .steps::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(6, 214, 160, 0.1) 0%, rgba(6, 214, 160, 0) 70%);
            top: -150px;
            right: -150px;
        }

        .steps-container {
            display: flex;
            justify-content: space-between;
            gap: 40px;
            position: relative;
            z-index: 1;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 40px 30px;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            position: relative;
            transition: var(--transition);
        }

        .step:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .step-number {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            margin: 0 auto 30px;
            position: relative;
        }

        .step-number::after {
            content: '';
            position: absolute;
            width: 120%;
            height: 120%;
            border-radius: 50%;
            border: 2px dashed var(--primary-light);
            animation: spin 20s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .step h3 {
            font-size: 22px;
            margin-bottom: 16px;
        }

        .step p {
            color: var(--text-muted);
        }

        .step-connector {
            position: absolute;
            top: 50%;
            right: -20px;
            transform: translateY(-50%);
            color: var(--primary-light);
            font-size: 24px;
            z-index: 2;
        }

        /* Demo Section */
        .demo {
            background-color: var(--white);
        }

        .demo-container {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .demo-header {
            padding: 50px 50px 0;
            color: white;
            text-align: center;
        }

        .demo-header h2 {
            font-size: 36px;
            margin-bottom: 16px;
        }

        .demo-header p {
            font-size: 18px;
            opacity: 0.9;
            max-width: 700px;
            margin: 0 auto 30px;
        }

        .demo-content {
            background: white;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            padding: 50px;
            margin-top: 40px;
            min-height: 500px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .demo-placeholder {
            max-width: 800px;
            margin: 0 auto;
        }

        .demo-icon {
            font-size: 80px;
            color: var(--primary);
            margin-bottom: 30px;
            opacity: 0.7;
        }

        .demo-placeholder h3 {
            font-size: 32px;
            margin-bottom: 20px;
            color: var(--text-dark);
        }

        .demo-placeholder p {
            font-size: 18px;
            color: var(--text-muted);
            margin-bottom: 30px;
            max-width: 600px;
        }

        .demo-features {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 40px;
        }

        .demo-feature {
            background: var(--light-bg);
            padding: 20px;
            border-radius: var(--radius);
            text-align: center;
            min-width: 180px;
            transition: var(--transition);
        }

        .demo-feature:hover {
            transform: translateY(-5px);
            background: var(--primary-light);
        }

        .demo-feature i {
            font-size: 30px;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .demo-feature h4 {
            font-size: 16px;
            margin-bottom: 8px;
        }

        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(67, 97, 238, 0.1) 0%, rgba(67, 97, 238, 0) 70%);
            bottom: -200px;
            right: -200px;
        }

        .cta-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }

        .cta h2 {
            font-size: 42px;
            margin-bottom: 20px;
        }

        .cta p {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 40px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-badges {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        .cta-badge {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 24px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cta-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .form-input {
            flex: 1;
            min-width: 300px;
            padding: 18px 24px;
            border-radius: var(--radius);
            border: none;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            box-shadow: var(--shadow);
        }

        .form-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.3);
        }

        /* Footer */
        footer {
            background-color: #0f172a;
            color: white;
            padding: 80px 0 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 50px;
            margin-bottom: 60px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
        }

        .footer-logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .footer-about p {
            color: #94a3b8;
            margin-bottom: 30px;
        }

        .social-links {
            display: flex;
            gap: 16px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            transition: var(--transition);
        }

        .social-link:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }

        .footer-column h3 {
            font-size: 18px;
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
            color: #94a3b8;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: white;
            padding-left: 8px;
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #94a3b8;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .hero-stats {
                justify-content: center;
            }
            
            .floating-card-1,
            .floating-card-2 {
                position: relative;
                top: auto;
                right: auto;
                bottom: auto;
                left: auto;
                margin: 16px auto 0;
                max-width: 320px;
                animation: none;
                transform: none;
            }
            
            .steps-container {
                flex-direction: column;
            }
            
            .step-connector {
                display: none;
            }
        }

        @media (max-width: 768px) {
            section {
                padding: 80px 0;
            }
            
            .hero-text h1 {
                font-size: 40px;
            }
            
            .section-title h2 {
                font-size: 32px;
            }
            
            .nav-links, .nav-actions .btn-secondary {
                display: none;
            }
            
            .mobile-menu-btn {
                display: flex;
            }
            
            .hero-actions {
                justify-content: center;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .floating-card {
                position: relative;
                margin-bottom: 20px;
                animation: none;
            }
            
            .floating-card-1, .floating-card-2 {
                position: relative;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                margin: 20px auto;
                max-width: 300px;
            }
            
            .demo-content {
                padding: 30px 20px;
            }
            
            .form-group {
                flex-direction: column;
            }
            
            .form-input {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header id="header">
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <span>DocuBills</span>
                </a>
                
                <ul class="nav-links">
                    <li><a href="#features" class="active">Features</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#demo">Interactive Demo</a></li>
                    <li><a href="#testimonials">Testimonials</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                </ul>
                
                <div class="nav-actions">
                    <a href="#" class="btn btn-secondary">Sign In</a>
                    <a href="#cta" class="btn btn-primary">Get Started Free</a>
                </div>
                
                <button class="mobile-menu-btn" id="mobileMenuBtn">
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
                            <i class="fas fa-play-circle"></i>
                            Watch Interactive Demo
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
                    <img id="heroImage" src="/assets/hero.png" alt="Invoice Dashboard Preview" class="dashboard-preview">
                    
                    <div class="floating-card floating-card-1">
                        <div class="floating-icon">
                            <i class="fas fa-file-upload"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 5px 0; font-size: 16px;">Upload & Generate</h4>
                            <p style="font-size: 14px; margin: 0; color: var(--text-muted);">Excel or Google Sheets</p>
                        </div>
                    </div>
                    
                    <div class="floating-card floating-card-2">
                        <div class="floating-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 5px 0; font-size: 16px;">Automated Follow-ups</h4>
                            <p style="font-size: 14px; margin: 0; color: var(--text-muted);">Set custom reminders</p>
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

    <!-- Interactive Demo Section -->
    <section id="demo" class="demo">
        <div class="container">
            <div class="demo-container">
                <div class="demo-header">
                    <h2>Interactive Demo</h2>
                    <p>Experience the power of DocuBills firsthand. Try our interactive demo to see how easy invoice generation can be.</p>
                    <a href="#cta" class="btn btn-accent btn-lg">
                        <i class="fas fa-play"></i>
                        Launch Interactive Demo
                    </a>
                </div>
                
                <div class="demo-content">
                    <div class="demo-placeholder">
                        <i class="fas fa-laptop-code demo-icon"></i>
                        <h3>Experience DocuBills in Action</h3>
                        <p>This interactive demo will allow you to upload a sample spreadsheet, select columns, customize the invoice, and see the final result in real-time.</p>
                        
                        <div class="demo-features">
                            <div class="demo-feature">
                                <i class="fas fa-upload"></i>
                                <h4>Upload Data</h4>
                                <p>Drag & drop or import from Google Sheets</p>
                            </div>
                            
                            <div class="demo-feature">
                                <i class="fas fa-sliders-h"></i>
                                <h4>Customize</h4>
                                <p>Select columns, add branding, set currency</p>
                            </div>
                            
                            <div class="demo-feature">
                                <i class="fas fa-file-pdf"></i>
                                <h4>Generate PDF</h4>
                                <p>Download or send directly to clients</p>
                            </div>
                            
                            <div class="demo-feature">
                                <i class="fas fa-robot"></i>
                                <h4>Automate</h4>
                                <p>Set up recurring invoices & reminders</p>
                            </div>
                        </div>
                        
                        <p style="margin-top: 40px; font-size: 16px;">
                            <i class="fas fa-info-circle" style="color: var(--primary); margin-right: 8px;"></i>
                            The interactive demo will be implemented with JavaScript to simulate the actual invoice generation workflow.
                        </p>
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
                        <div class="footer-logo-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <span>DocuBills</span>
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
                        <li><a href="#pricing">Pricing</a></li>
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
                <p>&copy; 2023 DocuBills. All rights reserved. | bebo.php</p>
            </div>
        </div>
    </footer>

    <script>
        // ✅ Hero Image Config (change only this one line)
        window.DOCUBILLS = {
            heroImage: "/assets/hero.png"
        };

        // ✅ Optional: change image via URL: ?hero=yourfile.jpg
        (function () {
            const img = document.getElementById("heroImage");
            if (!img) return;

            const params = new URLSearchParams(window.location.search);
            const q = params.get("hero");

            let src = (window.DOCUBILLS && window.DOCUBILLS.heroImage) ? window.DOCUBILLS.heroImage : img.src;

            if (q) {
                const safe = q.replace(/[^a-zA-Z0-9_.-]/g, "");
                if (safe) src = "/assets/" + safe;
            }

            if (src) img.src = src;
        })();

        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navLinks = document.querySelector('.nav-links');
        const navActions = document.querySelector('.nav-actions');
        
        mobileMenuBtn.addEventListener('click', function() {
            const isMobileMenuOpen = navLinks.style.display === 'flex';
            
            if (isMobileMenuOpen) {
                navLinks.style.display = 'none';
                navActions.style.display = 'none';
            } else {
                navLinks.style.display = 'flex';
                navActions.style.display = 'flex';
                
                // Style for mobile
                navLinks.style.flexDirection = 'column';
                navLinks.style.position = 'absolute';
                navLinks.style.top = '100%';
                navLinks.style.left = '0';
                navLinks.style.width = '100%';
                navLinks.style.backgroundColor = 'var(--white)';
                navLinks.style.padding = '30px 24px';
                navLinks.style.boxShadow = 'var(--shadow-lg)';
                navLinks.style.gap = '20px';
                
                navActions.style.flexDirection = 'column';
                navActions.style.position = 'absolute';
                navActions.style.top = 'calc(100% + 200px)';
                navActions.style.left = '0';
                navActions.style.width = '100%';
                navActions.style.padding = '0 24px 30px';
                navActions.style.gap = '16px';
            }
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

        // Smooth scrolling for anchor links
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

        // Interactive demo placeholder interaction
        const demoPlaceholder = document.querySelector('.demo-placeholder');
        if (demoPlaceholder) {
            demoPlaceholder.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
                
                alert("In the actual implementation, this will launch an interactive demo where you can upload a sample spreadsheet and generate an invoice step-by-step. The demo will include:\n\n1. Sample spreadsheet upload\n2. Column selection interface\n3. Invoice preview with customization options\n4. Final PDF generation\n5. Automated reminder setup");
            });
        }

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
</body>
</html>