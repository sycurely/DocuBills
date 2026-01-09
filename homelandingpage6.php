<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocuBills | Generate Custom Invoices in 3 Simple Steps</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,700,1,0" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --primary-light: #eef2ff;
            --secondary: #7209b7;
            --accent: #FFDC00;
            --accent-dark: #E6C600;
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

        *{
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
            color: rgba(0, 0, 0, 1);
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
            position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo img {
            height: 45px;
            width: auto;
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
            width: 44px;
            height: 44px;
            background-color: var(--primary);
            color: rgba(255, 255, 255, 1);
            border-radius: var(--radius-sm);
            border: none;
            align-items: center;
            justify-content: center;
            z-index: 1001;
            position: relative;
        }

        .mobile-menu-btn i {
            transition: var(--transition);
        }

        /* Mobile Menu Open State */
        .navbar.mobile-open .nav-links,
        .navbar.mobile-open .nav-actions {
            display: flex !important;
            flex-direction: column;
            position: absolute;
            left: 0;
            right: 0;
            background: var(--white);
            padding: 20px 24px;
            gap: 0;
            animation: slideDown 0.3s ease;
        }

        .navbar.mobile-open .nav-links {
            top: 100%;
            border-radius: 0;
            box-shadow: none;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .navbar.mobile-open .nav-actions {
            top: calc(100% + 200px);
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding-top: 12px;
            gap: 12px;
        }

        .navbar.mobile-open .nav-links li {
            border-bottom: 1px solid var(--border);
        }

        .navbar.mobile-open .nav-links li:last-child {
            border-bottom: none;
        }

        .navbar.mobile-open .nav-links a {
            display: block;
            padding: 14px 0;
            font-size: 16px;
        }

        .navbar.mobile-open .nav-actions .btn {
            width: 100%;
            justify-content: center;
            padding: 14px 24px;
        }

        .navbar.mobile-open .nav-actions .btn-secondary {
            display: flex !important;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile Menu Overlay */
        .mobile-menu-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
            backdrop-filter: blur(2px);
        }

        .mobile-menu-overlay.active {
            display: block;
            opacity: 1;
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
        
        /* ✅ NEW: keep BOTH floating cards in the SAME ROW, anchored to the image */
        .floating-row{
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 18px;
            display: flex;
            justify-content: space-between; /* left + right */
            gap: 16px;
            z-index: 6;
            pointer-events: none; /* cards won't block hover/clicks on image */
        }
        
        /* override "dangling" absolute+translate floating behavior */
        .floating-row .floating-card{
            position: relative;        /* no longer absolute */
            top: auto; right: auto; bottom: auto; left: auto;
            transform: none !important; /* stop translate */
            animation: none !important; /* stop floating animation */
            --tx: 0px;
            --ty: 0px;
            max-width: 280px;
            width: calc(50% - 8px);
        }
        
        /* ✅ Responsive: stack nicely on smaller screens */
        @media (max-width: 1024px){
            .floating-row{
                position: static;
                margin-top: 18px;
                justify-content: center;
                flex-wrap: wrap;
            }
            .floating-row .floating-card{
                width: 100%;
                max-width: 320px;
            }
        }

        .floating-icon {
            width: 50px;
            height: 50px;
            background-color: var(--primary);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
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
            color: var(--primary);
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
        
        /* ✅ Demo Tabs */
        .demo-tabs {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 26px;
            position: relative;
        }
        
        /* Center the step buttons as a group */
        .demo-tabs .demo-tab {
            flex-shrink: 0;
        }
        
        .demo-tab {
            appearance: none;
            border: 1px solid rgba(255, 255, 255, 0.28);
            background: rgba(255, 255, 255, 0.14);
            color: #fff;
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .demo-tab:hover {
            background: rgba(255, 255, 255, 0.22);
            transform: translateY(-1px);
        }
        
        .demo-tab[aria-selected="true"] {
            background: #fff;
            color: var(--primary);
            border-color: #fff;
            box-shadow: var(--shadow-sm);
        }
        
        .demo-tab-num {
            width: 22px;
            height: 22px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.22);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
        }
        
        .demo-tab[aria-selected="true"] .demo-tab-num {
            background: rgba(67, 97, 238, 0.12);
            color: var(--primary);
        }
        
        /* ✅ Reset Demo Button */
        .demo-reset-btn {
            appearance: none;
            border: 1px solid rgba(255, 255, 255, 0.28);
            background: rgba(108, 117, 125, 0.3);
            color: #fff;
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            position: absolute;
            right: 0;
        }
        
        .demo-reset-btn:hover {
            background: rgba(108, 117, 125, 0.5);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        
        .demo-reset-btn:active {
            transform: translateY(0);
        }
        
        .demo-reset-btn i {
            font-size: 12px;
        }
        
        /* ✅ Panels */
        .demo-content {
            align-items: stretch;
            justify-content: flex-start;
            text-align: left;
        }
        
        .demo-panel {
            display: none;
            width: 100%;
            max-width: 100%;
            margin: 0;
        }
        
        .demo-panel.active {
            display: block;
        }
        
        .demo-frame-wrap {
            width: 100%;
            height: auto;
            border-radius: var(--radius);
            overflow: visible;
            border: 1px solid var(--border);
            background: #fff;
            box-shadow: var(--shadow);
        }
        
        .demo-iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }
        
        .demo-step-placeholder {
            text-align: center;
            padding: 30px 10px;
        }
        
        .demo-step-placeholder .demo-icon {
            margin-bottom: 16px;
        }
        
        .demo-note {
            margin-top: 14px;
            font-size: 14px;
            color: var(--text-muted);
            text-align: center;
        }
        
        /* Signup Modal Styles */
        .demo-signup-modal {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          z-index: 10000;
          display: flex;
          align-items: center;
          justify-content: center;
        }
        
        .demo-signup-modal-overlay {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0, 0, 0, 0.5);
          backdrop-filter: blur(4px);
        }
        
        .demo-signup-modal-content {
          position: relative;
          background: var(--white);
          border-radius: 16px;
          padding: 40px;
          max-width: 520px;
          width: 90%;
          max-height: 90vh;
          overflow-y: auto;
          box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
          z-index: 10001;
        }
        
        .demo-signup-modal-close {
          position: absolute;
          top: 16px;
          right: 16px;
          width: 36px;
          height: 36px;
          border-radius: 50%;
          background: var(--light-bg);
          border: none;
          color: var(--text-dark);
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          transition: all 0.2s ease;
          font-size: 18px;
        }
        
        .demo-signup-modal-close:hover {
          background: var(--primary-light);
          color: var(--primary);
          transform: rotate(90deg);
        }
        
        .demo-signup-modal-title {
          font-size: 28px;
          font-weight: 700;
          color: var(--text-dark);
          margin: 0 0 16px 0;
          line-height: 1.3;
        }
        
        .demo-signup-modal-intro {
          font-size: 15px;
          color: var(--text-muted);
          margin: 0 0 24px 0;
          line-height: 1.5;
        }
        
        .demo-signup-features-box {
          background: var(--primary-light);
          border-radius: 12px;
          padding: 20px;
          margin-bottom: 24px;
          border: 1px solid var(--border);
        }
        
        .demo-signup-features-header {
          display: flex;
          align-items: center;
          gap: 10px;
          margin-bottom: 16px;
          color: var(--primary);
          font-size: 16px;
          font-weight: 600;
        }
        
        .demo-signup-features-header i {
          font-size: 18px;
        }
        
        .demo-signup-features-list {
          list-style: none;
          padding: 0;
          margin: 0;
        }
        
        .demo-signup-features-list li {
          display: flex;
          align-items: center;
          gap: 12px;
          color: var(--text-dark);
          font-size: 15px;
          margin-bottom: 12px;
          padding-left: 0;
        }
        
        .demo-signup-features-list li:last-child {
          margin-bottom: 0;
        }
        
        .demo-signup-features-list i {
          color: var(--primary);
          font-size: 16px;
        }
        
        .demo-signup-modal-actions {
          display: flex;
          flex-direction: column;
          gap: 12px;
          margin-bottom: 16px;
        }
        
        .demo-signup-btn-primary {
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 8px;
          padding: 16px 24px;
          background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
          color: white;
          text-decoration: none;
          border-radius: 10px;
          font-weight: 600;
          font-size: 16px;
          transition: all 0.3s ease;
          box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .demo-signup-btn-primary:hover {
          transform: translateY(-2px);
          box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }
        
        .demo-signup-btn-secondary {
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 14px 24px;
          background: var(--white);
          color: var(--primary);
          text-decoration: none;
          border: 2px solid var(--primary);
          border-radius: 10px;
          font-weight: 500;
          font-size: 15px;
          transition: all 0.2s ease;
        }
        
        .demo-signup-btn-secondary:hover {
          background: var(--primary-light);
          border-color: var(--primary-dark);
        }
        
        .demo-signup-modal-footer {
          text-align: center;
          font-size: 13px;
          color: var(--text-muted);
          margin: 0;
        }
        
        @media (max-width: 768px) {
            .demo-frame-wrap {
                height: auto;
            }
            
            .demo-reset-btn {
                display: none;
            }
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
            padding: 0px;
            margin-top: 40px;
            min-height: 500px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: flex-start;
            text-align: left;
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
            background-color: var(--primary);
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
            margin-bottom: 20px;
        }

        .footer-logo img {
            height: 50px;
            width: auto;
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

        /* Tablet breakpoint - adjust nav spacing */
        @media (max-width: 1024px) {
            .nav-links {
                gap: 24px;
            }
            
            .nav-actions {
                gap: 12px;
            }
            
            .nav-actions .btn {
                padding: 10px 16px;
                font-size: 14px;
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
            
            .nav-links, .nav-actions {
                display: none;
            }
            
            .mobile-menu-btn {
                display: flex;
            }
            
            /* Ensure mobile menu shows correctly when open */
            .navbar.mobile-open .nav-links,
            .navbar.mobile-open .nav-actions {
                display: flex !important;
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
                padding: 0px;
            }
            
            .form-group {
                flex-direction: column;
            }
            
            .form-input {
                min-width: 100%;
            }
        }
        
        /* =========================================================
           ✅ Step 1 Demo: EXACT create-invoice.php styles (SCOPED)
           ✅ This block OVERRIDES landing-page .btn + heading styles
        ========================================================= */
        
        .demo-step1-embed{
          height: auto;
          overflow: visible;
          background: #f5f7fb; /* same as --body-bg */
        }
        
        /* ✅ Step 2 embed wrapper */
        .demo-step2-embed{
          height: auto;
          overflow: visible;
          background: #f5f7fb; /* same as --body-bg */
        }
        
        /* ✅ Step 3 embed wrapper */
        .demo-step3-embed{
          height: auto;
          overflow: visible;
          background: #f5f7fb;
        }
        
        /* ✅ Step 3 skin: its own variables so it won’t affect Step 1/2 */
        .demo-step3-skin{
          --primary: #0033D9;
          --primary-light: #4895ef;
          --secondary: #3f37c9;
          --success: #4cc9f0;
          --danger: #f72585;
          --warning: #f8961e;
          --dark: #212529;
          --light: #f8f9fa;
          --gray: #6c757d;
          --border: #dee2e6;
          --card-bg: #ffffff;
          --body-bg: #f5f7fb;
        }
        
        .demo-step3-skin .main-content{
          background: var(--body-bg);
        }
        
        /* ✅ Step 3 invoice UI */
        .demo-step3-skin .invoice-box{
          margin: auto;
          padding: 30px;
          border: 1px solid #eee;
          background: var(--card-bg);
          border-radius: 10px;
          box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .demo-step3-skin .inv-row-top{
          display:flex;
          justify-content:space-between;
          align-items:flex-start;
        }
        
        .demo-step3-skin .demo-logo-box{
          width: 120px;
          height: 70px;
          border: none;
          border-radius: 10px;
          display:flex;
          align-items:center;
          justify-content:center;
          overflow: hidden;
        }
        
        .demo-step3-skin .demo-logo-box img {
          width: 100%;
          height: 100%;
          object-fit: contain;
        }
        
        .demo-step3-skin .inv-billto-title{
          font-size:16px;
          font-weight:700;
          text-align:right;
        }
        
        .demo-step3-skin .invoice-header-section{
          display:flex;
          justify-content:space-between;
          margin-top: 6px;
          margin-bottom: 30px;
          gap: 18px;
        }
        
        .demo-step3-skin .company-info,
        .demo-step3-skin .bill-to{
          font-size:14px;
          line-height:1.5;
        }
        
        .demo-step3-skin .company-name{
          font-weight:700;
          font-size:16px;
          margin-bottom:4px;
        }
        
        .demo-step3-skin .bill-to{
          text-align:right;
        }
        
        /* Title bar picker */
        .demo-step3-skin .titlebar-picker{
          margin-top: 12px;
          padding: 12px;
          border: 1px solid var(--border);
          border-radius: 10px;
          background: var(--light);
        }
        
        .demo-step3-skin .color-swatch-row{
          display:flex;
          flex-wrap:wrap;
          gap:8px;
          margin-top:10px;
          align-items:center;
        }
        
        .demo-step3-skin .color-swatch{
          border: 2px solid transparent;
          background: transparent;
          padding: 2px;
          border-radius: 10px;
          cursor: pointer;
          transition: transform .12s ease, border-color .2s ease, box-shadow .2s ease;
          line-height: 0;
        }
        
        .demo-step3-skin .color-swatch:active{ transform: translateY(1px); }
        
        .demo-step3-skin .color-swatch .swatch-box{
          display: inline-block;
          width:26px;
          height:26px;
          border-radius:8px;
        }
        
        .demo-step3-skin .color-swatch.is-selected{
          border-color: var(--primary);
          box-shadow: 0 0 0 3px rgba(67,97,238,0.18);
        }
        
        .demo-step3-skin .invoice-title-preview{
          margin-top: 12px;
          border-radius: 10px;
          padding: 12px 14px;
          font-size: 16px;
          font-weight: 800;
          text-align:center;
          letter-spacing: .03em;
        }
        
        /* Column toggle chips */
        .demo-step3-skin .column-toggle-wrapper{
          margin-top: 10px;
          margin-bottom: 8px;
          padding: 8px 10px;
          background: #f8f9fa;
          border: 1px solid var(--border);
          border-radius: 6px;
        }
        
        .demo-step3-skin .column-toggle-list{
          display:flex;
          flex-wrap:wrap;
          gap:8px 16px;
        }
        
        .demo-step3-skin .column-toggle-item{
          font-size:12px;
          display:inline-flex;
          align-items:center;
          gap:4px;
          padding:3px 6px;
          border-radius:4px;
          background:#ffffff;
          border:1px solid #e0e0e0;
        }
        
        .demo-step3-skin .column-toggle-item input{ margin:0; }
        
        .demo-step3-skin .column-toggle-item.price-column-label{
          border-color: var(--primary);
          background: #e9f0ff;
          opacity: 0.7;
          cursor: not-allowed;
        }
        
        .demo-step3-skin .column-toggle-item.price-column-label input[type="checkbox"]{
          cursor: not-allowed;
        }
        
        .demo-step3-skin .required-pill{
          font-size: 10px;
          padding: 1px 6px;
          border-radius: 999px;
          background: var(--primary);
          color: #fff;
          text-transform: uppercase;
          letter-spacing: 0.03em;
        }
        
        /* Table */
        .demo-step3-skin .invoice-table-scroll{
          width: 100%;
          overflow-x: auto;
          -webkit-overflow-scrolling: touch;
          margin-top: 10px;
        }
        
        .demo-step3-skin table{
          width: 100%;
          border-collapse: collapse;
          font-size: 12px;
          table-layout: fixed;
        }
        
        /* ✅ Fix: prevents empty phantom columns on the right when columns are hidden */
        .demo-step3-skin #demoInvoiceTable{
          width: max-content;  /* shrink to visible columns */
          min-width: 100%;     /* but never smaller than the container */
        }


        .demo-step3-skin th, .demo-step3-skin td{
          border: 1px solid var(--border);
          padding: 8px;
          vertical-align: top;
          text-align: left;
          word-break: break-word;
          overflow: hidden;
          white-space: normal;
        }
        
        .demo-step3-skin thead th{
          white-space: nowrap;
        }
        
        .demo-step3-skin .header-cell{
          background-color: var(--primary) !important;
          color: #fff !important;
          font-weight: 600;
          cursor: text;
          transition: background-color 0.2s ease, outline 0.2s ease;
        }
        
        .demo-step3-skin .header-cell:hover{
          background-color: #5a7aff !important;
        }
        
        .demo-step3-skin .header-cell:focus{
          background-color: #6b8aff !important;
          outline: 2px solid #ffffff !important;
          outline-offset: -2px;
        }
        
        .demo-step3-skin .editable-cell{
          background-color: #fff9db !important;
          cursor: text;
          transition: background-color 0.2s ease;
        }
        
        .demo-step3-skin .editable-cell:hover{
          background-color: #fff3b8 !important;
        }
        
        .demo-step3-skin .editable-cell:focus{
          background-color: #ffffff !important;
          outline: 2px solid #4361ee !important;
          outline-offset: -2px;
        }
        
        .demo-step3-skin .readonly-cell{
          background-color: #f5f5f5 !important;
          color: #777;
        }
        
        .demo-step3-skin .row-disabled{
          opacity: .45;
          background: var(--light) !important;
        }
        
        .demo-step3-skin .row-disabled td:not(:first-child){
          pointer-events: none;
          background: #f5f5f5 !important;
          color: #777;
        }
        
        /* Totals area */
        .demo-step3-skin .flex-container{
          display:flex;
          justify-content:space-between;
          align-items:center;
          margin-top: 20px;
          gap: 14px;
          flex-wrap: wrap;
        }
        
        .demo-step3-skin .total-display{
          font-size: 16px;
          font-weight: bold;
          text-align: right;
          padding: 10px;
          background-color: #f8f9fa;
          border-radius: 5px;
          border: 1px solid #eee;
          min-width: 250px;
          width: fit-content;
          max-width: 420px;
          white-space: nowrap;
        }
        
        /* Stripe warning */
        .demo-step3-skin .stripe-warning{
          margin-top: 10px;
          padding: 10px 12px;
          border-radius: 6px;
          border: 1px solid var(--warning);
          background: #fff8e6;
          color: #7c3a00;
          font-size: 12px;
          display: flex;
          gap: 10px;
          align-items: flex-start;
        }
        
        .demo-step3-skin .stripe-warning.hidden{ display:none; }
        
        .demo-step3-skin .btn-disabled-stripe{
          opacity: .6;
          cursor: not-allowed;
        }
        
        /* Date sections */
        .demo-step3-skin .date-section{
          display:flex;
          gap: 20px;
          margin: 20px 0;
          flex-wrap: wrap;
        }
        
        .demo-step3-skin .date-column{ flex: 1; min-width: 220px; }
        
        /* ✅ Step 3 Date/Time typography to match generate_invoice.php */
.demo-step3-skin .date-section .form-group{
  margin-bottom: 14px;
}

/* Labels like "Invoice Date", "Invoice Time", "Due Date" */
.demo-step3-skin .date-section .form-label{
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 6px;
  color: var(--dark);
}

/* Your HTML uses <strong> inside labels — force it to NOT change size */
.demo-step3-skin .date-section .form-label strong{
  font-size: inherit;
  font-weight: inherit;
}

/* Inputs: force same font + size for date/time controls */
.demo-step3-skin .date-section input[type="date"],
.demo-step3-skin .date-section input[type="time"]{
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  line-height: 1.2;
  padding: 10px 12px;
}

/* Checkbox row ("Include Due Time") font sizing */
.demo-step3-skin .date-section label{
  font-size: 14px;
}

        
        /* Recurring toggle */
        .demo-step3-skin .recurring-row{
          margin-top: 16px;
          margin-bottom: 40px;
          display:flex;
          justify-content:flex-start;
          align-items:center;
          gap:12px;
          flex-wrap:wrap;
        }
        
        .demo-step3-skin .recurring-row-label{
          font-size: 13px;
          color: var(--gray);
        }
        
        .demo-step3-skin .recurring-toggle{
          display:inline-flex;
          align-items:center;
          gap:8px;
          padding:8px 16px;
          border-radius:999px;
          border:none;
          font-size:13px;
          font-weight:500;
          cursor:pointer;
          transition: background-color .25s ease, box-shadow .25s ease, transform .1s ease;
          color:#fff;
        }
        
        .demo-step3-skin .recurring-toggle.recurring-on{
          background-color:#16a34a;
          box-shadow: 0 4px 8px rgba(22,163,74,0.35);
        }
        
        .demo-step3-skin .recurring-toggle.recurring-off{
          background-color:#b91c1c;
          box-shadow: 0 4px 8px rgba(185,28,28,0.35);
        }
        
        .demo-step3-skin .recurring-toggle:active{
          transform: translateY(1px);
        }
        
        /* Banking drawer */
        .demo-step3-skin .bank-head{
          display:flex;
          justify-content:space-between;
          align-items:flex-start;
          gap:12px;
          flex-wrap:wrap;
        }
        
        .demo-step3-skin .bank-sub{
          font-size: 12px;
          color: var(--gray);
          margin: 4px 0 10px;
        }
        
        .demo-step3-skin .bank-drawer{
          max-height: 0;
          overflow: hidden;
          opacity: 0;
          transform: translateY(-4px);
          transition: max-height .3s ease, opacity .25s ease, transform .25s ease;
        }
        
        .demo-step3-skin .bank-drawer.open{
          max-height: 800px;
          opacity: 1;
          transform: translateY(0);
          margin-top: 8px;
        }

        /* ✅ Step 2 page-specific styles (SCOPED under .demo-app) */
        .demo-app .price-option{
          padding: 1rem;
          border: 2px solid var(--border);
          border-radius: var(--radius);
          margin-bottom: 1rem;
          cursor: pointer;
          transition: var(--transition);
          background: #fff;
        }
        
        .demo-app .price-option:hover{
          border-color: var(--primary-light);
          background-color: rgba(67, 97, 238, 0.05);
        }
        
        .demo-app .price-option.active{
          border-color: var(--primary);
          background-color: rgba(67, 97, 238, 0.1);
        }
        
        /* show column picker only when active */
        .demo-app .price-option .column-options{ display: none; }
        .demo-app .price-option.active .column-options{ display: block; }
        
        .demo-app .column-options{
          padding: 1rem;
          background: rgba(0,0,0,0.03);
          border-radius: var(--radius);
          margin-top: 1rem;
        }
        
        .demo-app .manual-notice{
          background-color: #fff8e6;
          border-left: 4px solid var(--warning);
          padding: 1rem;
          margin-top: 1rem;
          border-radius: 0 var(--radius) var(--radius) 0;
          color: var(--dark);
          font-size: 14px;
        }
        
        .demo-app .alert{
          padding: 0.9rem 1rem;
          border-radius: var(--radius);
          margin-bottom: 1rem;
          border-left: 4px solid var(--danger);
          background-color: #ffe5ea;
          color: #721c24;
          font-size: 0.95rem;
        }

        /* Scope everything under .demo-app (no :root leaks) */
        .demo-app{
          --primary: #4361ee;
          --primary-light: #4895ef;
          --secondary: #3f37c9;
          --success: #4cc9f0;
          --danger: #f72585;
          --warning: #f8961e;
          --dark: #212529;
          --light: #f8f9fa;
          --gray: #6c757d;
          --border: #dee2e6;
          --card-bg: #ffffff;
          --body-bg: #f5f7fb;
          --header-height: 70px;
          --sidebar-width: 250px;
          --transition: all 0.3s ease;
          --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
          --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.1);
          --radius: 10px;
          --sidebar-bg: #2c3e50;
        
          /* ✅ force same typography (prevents landing page Poppins headings) */
          font-family: 'Inter', sans-serif;
          font-size: 16px;
          line-height: 1.5;
          color: var(--dark);
        }
        
        .demo-app .material-symbols-rounded{
          font-variation-settings: 'FILL' 1, 'wght' 700, 'GRAD' 0, 'opsz' 24;
        }

        /* ✅ neutralize landing page heading font + margins inside demo */
        .demo-app h1,
        .demo-app h2,
        .demo-app h3,
        .demo-app h4,
        .demo-app h5,
        .demo-app h6{
          font-family: inherit;
          margin: 0;
        }
        
        /* ✅ neutralize landing page global link/button styles inside demo */
        .demo-app a{ color: inherit; text-decoration: none; }
        .demo-app *{ box-sizing: border-box; }
        
        .demo-app .app-container{
          display: flex;
          min-height: 100%;
        }
        
        /* In real app: padding accounts for header.
           In demo: keep the SAME padding so spacing looks identical. */
        .demo-app .main-content{
          flex: 1;
          padding: 24px 1.5rem 1.5rem;
          transition: var(--transition);
        }
        
        .demo-app .page-header{
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 2rem;
        }
        
        .demo-app .page-title{
          font-size: 1.8rem;
          font-weight: 700;
          color: var(--primary);
        }
        
        /* ✅ Demo form should behave like real create-invoice form (no extra wrapper styling) */
        .demo-app .demo-form{
          background: transparent;
          border: none;
          padding: 0;
          box-shadow: none;
        }

        /* (Optional card class — your create-invoice.php defines it) */
        .demo-app .card{
          background: var(--card-bg);
          border-radius: var(--radius);
          box-shadow: var(--shadow);
          transition: var(--transition);
          overflow: hidden;
          padding: 2rem;
          margin-bottom: 1.5rem;
        }
        
        .demo-app .form-section{
          border: 1px solid var(--border);
          border-radius: var(--radius);
          padding: 1.5rem;
          margin-bottom: 1.5rem;
          background: var(--card-bg);
        }
        
        .demo-app .form-section-title{
          font-weight: 600;
          color: var(--primary);
          margin-bottom: 1rem;
          display: flex;
          align-items: center;
          gap: 10px;
        }
        
        /* Your file uses .form-group margin spacing */
        .demo-app .form-group{
          margin-bottom: 1.2rem;
        }
        
        .demo-app .position-relative{
          position: relative;
        }
        
        .demo-app .autocomplete-list{
          position: absolute;
          top: 100%;
          left: 0;
          right: 0;
          background: var(--card-bg);
          border: 1px solid var(--border);
          border-top: none;
          border-radius: 0 0 var(--radius) var(--radius);
          box-shadow: var(--shadow);
          max-height: 220px;
          overflow-y: auto;
          z-index: 1000;
        }
        
        .demo-app .autocomplete-item{
          padding: 0.5rem 0.75rem;
          cursor: pointer;
          font-size: 0.95rem;
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 0.5rem;
        }
        
        .demo-app .autocomplete-item:hover{
          background: rgba(67, 97, 238, 0.08);
        }
        
        .demo-app .autocomplete-company{
          font-weight: 500;
          color: var(--dark);
        }
        
        .demo-app .autocomplete-rep{
          font-size: 0.85rem;
          color: var(--gray);
        }
        
        .demo-app .form-label{
          display: block;
          margin-bottom: 0.5rem;
          font-weight: 500;
        }
        
        .demo-app .form-control{
          width: 100%;
          padding: 0.8rem 1rem;
          border: 1px solid var(--border);
          border-radius: var(--radius);
          background: var(--card-bg);
          color: var(--dark);
          font-size: 1rem;
          transition: var(--transition);
        }
        
        /* ✅ Locked (read-only) UI */
        .demo-app .locked-field{
          position: relative;
        }
        
        .demo-app .form-control.is-locked{
          background: #f3f4f6;
          color: #6b7280;
          cursor: not-allowed;
          padding-right: 44px; /* space for lock icon */
        }
        
        .demo-app .lock-icon{
          position: absolute;
          right: 14px;
          top: 50%;
          transform: translateY(-50%);
          color: #facc15;            /* ✅ yellow */
          font-size: 18px;           /* ✅ looks better for Material icons */
          line-height: 1;
          pointer-events: none;
        }
        
        /* ✅ Lock the upload area too */
        .demo-app .upload-container.is-locked{
          position: relative;
          cursor: not-allowed;
          opacity: 0.9;
        }
        
        .demo-app .upload-container.is-locked:hover{
          border-color: rgba(245, 158, 11, 0.65);
          background: rgba(245, 158, 11, 0.06);
        }
        
        .demo-app .upload-container.is-locked::after{
          display: none;
        }
        
        .demo-app .upload-container .lock-badge{
          position: absolute;
          top: 12px;
          right: 12px;
          z-index: 2;
          display: inline-flex;
          align-items: center;
          gap: 8px;
          padding: 6px 10px;
          border-radius: 999px;
          background: rgba(0,0,0,0.06);
          border: 1px solid rgba(0,0,0,0.08);
          color: #374151;
          font-size: 12px;
          font-weight: 700;
        }
        
        .demo-app .upload-container .lock-badge i{
          color: #6b7280;
        }
        
        .demo-app .form-control:focus{
          border-color: var(--primary);
          outline: none;
          box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        /* ✅ CRITICAL: override landing-page .btn styles */
        .demo-app .btn{
          padding: 0.8rem 1.5rem;
          border-radius: var(--radius);
          border: none;
          font-weight: 600;
          cursor: pointer;
          transition: var(--transition);
          display: inline-flex;
          align-items: center;
          gap: 8px;
          font-size: 1rem;
        
          /* ✅ Match create-invoice: no Poppins/shimmer/lift behavior */
          font-family: inherit;
          position: static;
          overflow: visible;
          transform: none;
        }
        
        /* ✅ Remove landing page shimmer overlay inside demo */
        .demo-app .btn::after{
          content: none !important;
          display: none !important;
        }
        
        /* ✅ Remove landing hover lift inside demo */
        .demo-app .btn:hover{
          transform: none !important;
        }
        
        .demo-app .btn-primary{
          background: var(--primary);
          color: white;
        }
        
        .demo-app .btn-primary:hover{
          background: var(--secondary);
          box-shadow: var(--shadow-hover);
        }
        
        /* Save Invoice Button - Stand Out Styling */
        #demoSaveBtn,
        #demoSaveBtnBottom {
          background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
          color: white;
          font-weight: 700;
          padding: 14px 28px;
          font-size: 1.05rem;
          box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
          border: none;
          position: relative;
          overflow: hidden;
          transition: all 0.3s ease;
        }
        
        #demoSaveBtn::before,
        #demoSaveBtnBottom::before {
          content: '';
          position: absolute;
          top: 0;
          left: -100%;
          width: 100%;
          height: 100%;
          background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
          transition: left 0.5s;
        }
        
        #demoSaveBtn:hover::before,
        #demoSaveBtnBottom:hover::before {
          left: 100%;
        }
        
        #demoSaveBtn:hover,
        #demoSaveBtnBottom:hover {
          transform: translateY(-2px);
          box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        #demoSaveBtn:active,
        #demoSaveBtnBottom:active {
          transform: translateY(0);
        }
        
        #demoSaveBtn i,
        #demoSaveBtnBottom i {
          margin-right: 8px;
        }
        
        /* (Optional classes referenced in your real file) */
        .demo-app .btn-secondary{
          background: transparent;
          color: var(--primary);
          border: 1px solid var(--border);
        }
        
        .demo-app .btn-secondary:hover{
          border-color: var(--primary);
          background: rgba(67, 97, 238, 0.05);
        }
        
        .demo-app .btn-sm{
          padding: 0.55rem 0.9rem;
          font-size: 0.9rem;
        }
        
        .demo-app .form-grid{
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
          gap: 1.2rem;
        }
        
        .demo-app .upload-container{
          width: 100% !important;
          max-width: 100% !important;
          display: block;
          margin-left: 0 !important;
          margin-right: 0 !important;
        
          border: 2px dashed var(--border);
          border-radius: var(--radius);
          padding: 2rem;
          text-align: center;
          margin-top: 1rem;
          transition: var(--transition);
          cursor: pointer;
        }

        /* ✅ Ensure upload container can host the overlay */
        .demo-app .upload-container{
          position: relative;
        }
        
        /* ✅ Yellow lock badge */
        .demo-app .upload-container.is-locked .lock-badge{
          background: rgba(245, 158, 11, 0.14);
          border: 1px solid rgba(245, 158, 11, 0.35);
          color: #92400e;
          z-index: 4;
        }
        .demo-app .upload-container.is-locked .lock-badge i{
          color: #f59e0b; /* ✅ yellow lock */
        }
        
        /* ✅ Strong lock overlay (blocks interaction) */
        .demo-app .upload-container .lock-overlay{
          display: none;
        }
        .demo-app .upload-container.is-locked .lock-overlay{
          position: absolute;
          inset: 0;
          border-radius: var(--radius);
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 12px;
          padding: 18px;
          text-align: center;
          z-index: 3;
        
          /* strong overlay look */
          background: repeating-linear-gradient(
            135deg,
            rgba(245, 158, 11, 0.20) 0px,
            rgba(245, 158, 11, 0.20) 12px,
            rgba(245, 158, 11, 0.10) 12px,
            rgba(245, 158, 11, 0.10) 24px
          );
          border: 2px solid rgba(245, 158, 11, 0.55);
          backdrop-filter: blur(1px);
        }
        
        /* yellow lock icon big */
        .demo-app .upload-container.is-locked .lock-overlay i{
          font-size: 22px;
          color: #f59e0b; /* ✅ yellow */
        }
        
        /* overlay text */
        .demo-app .upload-container.is-locked .lock-title{
          font-weight: 900;
          color: #92400e;
          font-size: 14px;
          line-height: 1.2;
        }
        .demo-app .upload-container.is-locked .lock-sub{
          margin-top: 4px;
          font-weight: 600;
          color: rgba(146, 64, 14, 0.85);
          font-size: 12px;
        }
        
        .demo-app .upload-container:hover{
          border-color: var(--primary);
          background: rgba(67, 97, 238, 0.05);
        }
        
        .demo-app .upload-icon{
          font-size: 2.5rem;
          color: var(--primary);
          margin-bottom: 1rem;
        }
        
        .demo-app .upload-text{
          color: var(--gray);
          margin-bottom: 1rem;
        }
        
        .demo-app .upload-hint{
          font-size: 0.9rem;
          color: var(--gray);
        }
        
        .demo-app .required::after{
          content: " *";
          color: var(--danger);
        }
        
        .demo-app .error-text{
          margin-top: 0.4rem;
          font-size: 0.85rem;
          color: var(--danger);
        }
        
        /* Your custom table builder styles */
        .demo-app #custom-table-container table{
          width: 100%;
          border-collapse: collapse;
          margin-top: 1rem;
        }
        
        .demo-app #custom-table-container th,
        .demo-app #custom-table-container td{
          border: 1px solid var(--border);
          padding: 0.5rem;
          min-height: 2rem;
        }
        
        @media (max-width: 768px){
          .demo-app .form-grid{
            grid-template-columns: 1fr;
          }
        
          .demo-app .page-header{
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
          }
        }
        
        @media (max-width: 768px){
          .demo-app .form-grid{
            grid-template-columns: 1fr;
          }
        
          .demo-app .page-header{
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
          }
        }
        
        /* =========================================================
           ✅ KEEP original dropbox design + neutral lock overlay
           (Overrides the yellow/striped lock styling)
        ========================================================= */
        
        /* Keep the original dropzone look even when locked */
        .demo-app .upload-container.is-locked{
          opacity: 1 !important;
          cursor: not-allowed;
        }
        
        /* Don’t change colors on hover when locked */
        .demo-app .upload-container.is-locked:hover{
          border-color: var(--border) !important;
          background: transparent !important;
        }
        
        /* Hide the top-right “Locked” badge (you only want center padlock) */
        .demo-app .upload-container.is-locked .lock-badge{
          display: none !important;
        }
        
        /* Neutral (colorless) semi-opaque overlay + centered padlock */
        .demo-app .upload-container .lock-overlay{
          display: none;
        }
        
        .demo-app .upload-container.is-locked .lock-overlay{
          position: absolute;
          inset: 0;
          border-radius: var(--radius);
          display: flex;
          align-items: center;
          justify-content: center;
          z-index: 3;
        
          /* ✅ colorless semi-opaque overlay */
          background: rgba(255, 255, 255, 0.72);
          backdrop-filter: blur(2px);
          -webkit-backdrop-filter: blur(2px);
        
          /* blocks interaction */
          pointer-events: all;
        }
        
        /* Hide the overlay text container (keep ONLY the padlock) */
        .demo-app .upload-container.is-locked .lock-overlay > div{
          display: none !important;
        }
        
        /* Center padlock style */
        .demo-app .upload-container.is-locked .lock-overlay > .material-symbols-rounded{
          font-size: 52px;
          color: #facc15;           /* ✅ yellow */
          line-height: 1;
          filter: drop-shadow(0 6px 12px rgba(0,0,0,0.15));
        }
        
        .demo-app .upload-container.dragover{
          border-color: var(--primary);
          background: rgba(67, 97, 238, 0.05);
        }

    </style>
</head>
<body>
    <!-- Header -->
    <header id="header">
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo">
                    <img src="/assets/docubills-logo.png" alt="Docubills - Your paperwork, made simple">
                </a>
                
                <ul class="nav-links">
                    <li><a href="#features" class="active">Features</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#demo">Interactive Demo</a></li>
                </ul>
                
                <div class="nav-actions">
                    <a href="login.php" class="btn btn-secondary">Sign In</a>
                    <a href="register.php" class="btn btn-primary">Get Started Free</a>
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
                    <img id="heroImage" src="/assets/hero.png" alt="Invoice Dashboard Preview" class="dashboard-preview">
                    
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

    <!-- Interactive Demo Section (✅ 3-Step Tabs) -->
    <section id="demo" class="demo">
        <div class="container">
            <div class="demo-container">
                <div class="demo-header">
                    <h2>Try DocuBills in 3 Steps</h2>
                    <p>Click each step to preview exactly what happens in the real workflow.</p>
    
                    <!-- ✅ Tabs -->
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
    
                <!-- ✅ Panel: Step 1 (create-invoice.php) -->
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
                                      <!-- ✅ Overlay + Yellow Padlock -->
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
                                Demo mode: no login, no database, no server calls — buttons are for preview only.
                              </div>
                
                            </form>
                          </div>
                        </div>
                      </div>
                
                    </div>
                  </div>
                </div>
    
                <!-- ✅ Panel: Step 2 (Configure Invoice Pricing) -->
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
                                  Demo mode: preview-only — no session, no redirect, no database.
                                </div>
                
                            </form>
                
                          </div>
                        </div>
                      </div>
                
                    </div>
                  </div>
                </div>
    
                <!-- ✅ Panel: Step 3 (Invoice Preview Demo) -->
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
                
                                <!-- Row 1 – logo + Bill-To -->
                                <div class="inv-row-top">
                                  <div class="inv-logo">
                                    <div class="demo-logo-box"><img src="/assets/docubills-logo.png" alt="Docubills"></div>
                                  </div>
                                  <div class="inv-billto-title">Bill&nbsp;To:</div>
                                </div>
                
                                <!-- Row 2 – company block vs. client block -->
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
                                      These fields are pre-filled from Settings → Payment Methods. You can adjust them for this invoice only.
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
                        <img src="/assets/docubills-logo.png" alt="Docubills - Your paperwork, made simple">
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

        // ✅ Demo Tabs Logic (Step 1 / Step 2 / Step 3) + programmatic navigation
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

            // ✅ IMPORTANT: whenever Step 3 tab becomes active, capture Step 1 data and sync
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
        
          // ✅ Convenience: go(1|2|3)
          function go(stepNum) {
            const map = { 1: 'tab-step1', 2: 'tab-step2', 3: 'tab-step3' };
            const tabId = map[stepNum] || 'tab-step1';
            activate(tabId);
        
            // optional: keep the demo in view
            const demo = document.getElementById('demo');
            if (demo) demo.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        
          // ✅ expose globally for Step 1 / Step 2 scripts
          window.DocuBillsDemo = window.DocuBillsDemo || {};
          window.DocuBillsDemo.activate = activate;
          window.DocuBillsDemo.go = go;
        
          tabs.forEach(t => t.addEventListener('click', () => activate(t.id)));
        
          // default
          activate('tab-step1');
          
          // ✅ Reset Demo functionality
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

  // ✅ Demo Clients (replaces search_clients.php fetch)
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

  // ✅ Toggle Google/Upload sections (THIS is the “radio click” behavior)
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

  // ✅ Step 1 -> Step 2 navigation
    const form = root.querySelector('#demoInvoiceForm');
    if (form){
      form.addEventListener('submit', function(e){
        e.preventDefault();

        // (Optional) You can do basic "required" check here if you want,
        // but your inputs already have required attributes.

        // ✅ Capture company details from Step 1 form
        window.DOCUBILLS_DEMO_STATE = window.DOCUBILLS_DEMO_STATE || {};
        window.DOCUBILLS_DEMO_STATE.bill_to_name = root.querySelector('#bill_to_name')?.value || '';
        window.DOCUBILLS_DEMO_STATE.bill_to_rep = root.querySelector('#bill_to_rep')?.value || '';
        window.DOCUBILLS_DEMO_STATE.bill_to_email = root.querySelector('#bill_to_email')?.value || '';
        window.DOCUBILLS_DEMO_STATE.bill_to_phone = root.querySelector('#bill_to_phone')?.value || '';
        window.DOCUBILLS_DEMO_STATE.bill_to_address = root.querySelector('#bill_to_address')?.value || '';

        if (window.DocuBillsDemo && typeof window.DocuBillsDemo.go === 'function') {
          window.DocuBillsDemo.go(2); // ✅ go to Step 2 tab
        }
      });
    }

   // ✅ Upload area (REAL UI behavior)
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

  // ✅ Demo autocomplete (no fetch)
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

  // ✅ Demo headers (replace PHP $headers loop)
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

  // ✅ Fake totals per column (to simulate your PHP validation)
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

  // ✅ Build price-column radios (like your foreach $headers)
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

  // ✅ Build include columns checkboxes (like your include_cols[])
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

  // ✅ keep selected auto column stored (helps Step 3 stay correct)
  priceColumnRadios().forEach(r => {
    r.addEventListener('change', () => {
      if (!r.checked) return;

      // Store state
      window.DOCUBILLS_DEMO_STATE = window.DOCUBILLS_DEMO_STATE || {};
      window.DOCUBILLS_DEMO_STATE.price_mode = 'column';
      window.DOCUBILLS_DEMO_STATE.price_column = r.value;

      // ✅ Lock the corresponding checkbox in column picker
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

  // ✅ Apply initial lock to default pricing column (Sub Total)
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
    
    // ✅ FIX: keep global state in sync immediately (not only on submit)
    window.DOCUBILLS_DEMO_STATE = window.DOCUBILLS_DEMO_STATE || {};
    window.DOCUBILLS_DEMO_STATE.price_mode = isManual ? 'manual' : 'column';

    if (isManual) {
      window.DOCUBILLS_DEMO_STATE.price_column = null;

      // ✅ Clear all pricing column locks when switching to manual mode
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

  // ✅ max 15 enforcement (same logic)
  const max = 15;
  function enforceColumnLimit(){
    const checks = Array.from(root.querySelectorAll('#demoColumnPicker input[type="checkbox"]'));
    const checkedCount = checks.filter(c => c.checked).length;

    checks.forEach(c => {
      // ✅ Don't override pricing column lock
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

  // ✅ submit (demo validation + Step 2 -> Step 3 navigation)
    form.addEventListener('submit', function(e){
      e.preventDefault();
      hideAlert();
    
      const mode = root.querySelector('input[name="price_mode2"]:checked')?.value || 'column';
    
      // capture included columns (optional – useful if later you want Step 3 to reflect selection)
      const includeCols = Array.from(root.querySelectorAll('#demoColumnPicker input[type="checkbox"]'))
        .filter(c => c.checked)
        .map(c => Number(c.value));
    
      if (mode === 'manual'){
        // ✅ manual mode still proceeds to Step 3 in demo
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
    
      // ✅ store state (optional - merge to preserve Step 1 data)
      window.DOCUBILLS_DEMO_STATE = Object.assign({}, window.DOCUBILLS_DEMO_STATE || {}, {
        price_mode: 'column',
        price_column: selectedCol.value,
        include_cols: includeCols
      });

      // ✅ go to Step 3
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
    GBP: "£", EUR: "€", CHF: "CHF",
    PKR: "₨", INR: "₹", BDT: "৳", LKR: "Rs",
    AED: "د.إ", SAR: "﷼", QAR: "ر.ق", KWD: "د.ك", OMR: "ر.ع.",
    JPY: "¥", CNY: "¥", KRW: "₩",
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

    // ✅ LEFT SIDE: Your company/sender information
    if (companyInfoEl) {
      companyInfoEl.innerHTML = `
        <div class="company-name">DocuBills</div>
        <div>Pakistan</div>
        <div>+92-323-8970703</div>
        <div>docubills@gmail.com</div>
        <div>(SST/HST: 987654321)</div>
      `;
    }

    // ✅ RIGHT SIDE: Bill To client information OR banking details
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
      // ✅ Show Bill To client information (from Step 1)
      billToEl.innerHTML = `
        <div style="font-weight: 700; font-size: 16px; margin-bottom: 4px;">${st.bill_to_name || ''}</div>
        ${st.bill_to_rep ? `<div style="font-size: 14px;">${st.bill_to_rep}</div>` : ''}
        ${st.bill_to_address ? `<div style="font-size: 14px;">${st.bill_to_address}</div>` : ''}
        ${st.bill_to_phone ? `<div style="font-size: 14px;">${st.bill_to_phone}</div>` : ''}
        ${st.bill_to_email ? `<div style="font-size: 14px;">${st.bill_to_email}</div>` : ''}
      `;
    }
  }

  // ✅ Keep backward compatibility - renderBankingDetails now calls renderBillToSection
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

      // ✅ lock required column like generate_invoice.php
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

    // ✅ first column = row checkbox column (always visible)
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

      // ✅ checkbox cell
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
      // In demo: disable save until ack checked (matches your real “restrict” behavior)
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
        renderBankingDetails();  // ✅ Update Bill To section when checkbox changes
      });
    }
  }

  // -----------------------------
  // Public sync() (your tab switch calls this)
  // -----------------------------
  function sync(){
    ensureDefaultDates();
    fillCurrencyOptions();
    renderCompanyInfo();         // ✅ render company details from Step 1
    applyIncludeColsFromStep2(); // reflects Step 2 choices
    renderTitleBarSwatches();
    renderColumnToggles();
    renderTable();
    syncTotalsAndStripe();
  }

  // ✅ ACCUMULATOR: Collect all Step 3 sync functions without overwriting
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

  // ✅ Reset function for Step 3 data (inside main IIFE to access variables)
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
     ✅ FIX: Manual pricing must show a textbox in Step 3
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
     ✅ FIX: Required column pill + locked checkbox
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

    // ✅ Manual pricing: no column is required for total calculation
    if (mode === 'manual') return null;

    // ✅ Auto pricing locks the selected price column
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

    // Step 3 may render toggles slightly later — retry a few frames
    let tries = 0;
    (function tick(){
      const ok = __db_applyRequiredPillAndLock();
      if (ok) return;
      tries++;
      if (tries < 120) requestAnimationFrame(tick); // ~2s max
    })();
  }

  // ✅ Master sync executor: runs all registered functions in order
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

  // ✅ Also run once on load + when Step 3 tab is clicked
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
      <a href="https://docubills.com/register.php" class="demo-signup-btn-primary">
        Sign up free <i class="fas fa-arrow-right"></i>
      </a>
      <a href="https://docubills.com/login.php" class="demo-signup-btn-secondary">
        I already have an account
      </a>
    </div>
    
    <p class="demo-signup-modal-footer">No credit card required to start.</p>
  </div>
</div>

</body>
</html>
