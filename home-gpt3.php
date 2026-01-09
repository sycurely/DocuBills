<?php
// ✅ If someone opens home.php directly, send them to /
// (But allow index.php to include it without redirect)
if (!defined('DOCUBILLS_LANDING') && basename($_SERVER['SCRIPT_NAME']) === 'home.php') {
  header("Location: /", true, 301);
  exit;
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DocuBills — The free invoice generator powered by your Excel/Sheets</title>
  <meta name="description" content="DocuBills is the world’s first free invoice generator where you upload Excel/Google Sheets, choose any column as your price, toggle rows/columns, and automate billing with Stripe & email cadences." />

  <!-- Tailwind (CDN for quick deployment) -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    /* Tiny custom polish */
    :root{
      /* ✅ Dark landing (violet + cyan) */
      color-scheme: dark;
    
      --violet: 139, 92, 246;  /* violet-500 */
      --cyan:   34, 211, 238;  /* cyan-400 */
    
      /* Core dark surfaces */
      --bg:      2, 6, 23;     /* slate-950 */
      --surface: 15, 23, 42;   /* slate-900 */
      --surface2: 17, 24, 39;  /* slate-800 */
      --line:    148, 163, 184;/* slate-400 */
      --text:    226, 232, 240;/* slate-200 */
      --primary: var(--violet);
      --muted: 148, 163, 184;
    }
    
    /* ✅ Dark glass (fixes grey-on-grey + matches violet/cyan) */
    .glass{
      background:
        linear-gradient(135deg,
          rgba(var(--surface), 0.82) 0%,
          rgba(var(--surface2), 0.66) 55%,
          rgba(var(--surface), 0.76) 100%);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border: 1px solid rgba(var(--line), 0.22);
      color: rgba(var(--text), 0.96);
    }

    /* Slightly stronger shadow on dark */
    .shadow-soft { box-shadow: 0 18px 60px rgba(0,0,0,0.35); }
    
    /* ---------------------------------------------------
         Conversion polish (CTA + section rhythm)
    --------------------------------------------------- */
      .cta-gradient{
        background-image: linear-gradient(135deg,
          rgba(var(--violet), 1) 0%,
          rgba(var(--cyan), 1) 100%);
        box-shadow:
          0 14px 40px rgba(0,0,0,.35),
          0 0 0 1px rgba(255,255,255,.06) inset;
      }
      .cta-gradient:hover{
        filter: brightness(1.05);
        transform: translateY(-1px);
      }
    
      .ring-glow{
        box-shadow:
          0 0 0 1px rgba(255,255,255,.06) inset,
          0 18px 55px rgba(0,0,0,.40),
          0 0 0 10px rgba(var(--cyan), .06);
      }
    
      .gradient-text{
        background: linear-gradient(135deg, rgba(var(--cyan),1), rgba(var(--violet),1));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
      }
    
      .section-title{
        font-weight: 900;
        letter-spacing: -0.02em;
      }
    
      .muted{
        color: rgba(var(--text), .82);
      }
    
      /* smoother entrance for big blocks */
      .fade-in{
        animation: fadeIn .35s ease both;
      }
      @keyframes fadeIn{
        from{ opacity: 0; transform: translateY(6px); }
        to  { opacity: 1; transform: translateY(0); }
      }
    
      /* Sticky nav feels more “SaaS” */
      .sticky-nav{
        position: sticky;
        top: 14px;
        z-index: 40;
      }

    /* ✅ Keyboard pill that works on dark */
    .kbd{
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: 12px;
      border: 1px solid rgba(var(--line), .30);
      padding: 2px 8px;
      border-radius: 10px;
      background: rgba(var(--bg), 0.55);
      color: rgba(var(--text), 0.95);
    }
    
    /* ✅ Step cards (How it works) — richer contrast */
    #how .rounded-2xl.border{
      background: rgba(var(--surface), 0.55) !important;
      border-color: rgba(var(--line), 0.22) !important;
    }
    
    /* keep the highlighted step on-brand */
    #how .bg-violet-500\/10{
      background: rgba(var(--violet), 0.14) !important;
      border-color: rgba(var(--cyan), 0.30) !important;
    }
    
    /* Modal */
    .modal-backdrop { display:none; }
    .modal-backdrop.open { display:flex; }
    
    /* ---------------------------------------------------
       Drawer (Landing): Interactive Mini-Demo
    --------------------------------------------------- */
    .demo-drawer-panel{
      max-height: 0;
      overflow: hidden;
      opacity: 0;
      transform: translateY(-6px);
      transition: max-height .35s ease, opacity .25s ease, transform .25s ease;
      pointer-events: none;
    }
    .demo-drawer-panel.open{
      max-height: 5000px; /* large enough to fit the demo */
      opacity: 1;
      transform: translateY(0);
      margin-top: 12px;
      pointer-events: auto;
    }

    /* ---------------------------------------------------
       Attention glow (breathing corona) for Demo CTA + Drawer
    --------------------------------------------------- */
    @keyframes dbBreath {
      0%,100% { transform: translateZ(0) scale(1); opacity: .65; filter: blur(14px); }
      50%     { transform: translateZ(0) scale(1.03); opacity: 1;  filter: blur(18px); }
    }
    @keyframes dbPing {
      0%   { box-shadow: 0 0 0 0 rgba(var(--cyan), .55); }
      70%  { box-shadow: 0 0 0 14px rgba(var(--cyan), 0); }
      100% { box-shadow: 0 0 0 0 rgba(var(--cyan), 0); }
    }
    @keyframes dbFloat {
      0%,100% { transform: translateY(-50%) translateX(0); }
      50%     { transform: translateY(-58%) translateX(0); }
    }
    
    .attention-pulse,
    .attention-drawer{
      position: relative;
      isolation: isolate; /* allows glow behind element safely */
    }
    
    /* Hero "Try the mini-demo" button */
    .attention-pulse::before{
      content:"";
      position:absolute;
      inset:-10px;
      border-radius: 18px;
      background:
        radial-gradient(60% 80% at 20% 20%, rgba(var(--cyan), .40), rgba(var(--cyan),0) 60%),
        radial-gradient(70% 90% at 80% 30%, rgba(var(--violet), .35), rgba(var(--violet),0) 62%),
        radial-gradient(60% 80% at 50% 100%, rgba(255,255,255,.10), rgba(255,255,255,0) 60%);
      z-index:-1;
      animation: dbBreath 2.6s ease-in-out infinite;
      pointer-events:none;
    }
    .attention-pulse::after{
      content:"";
      position:absolute;
      top:-6px;
      right:-6px;
      width:10px;
      height:10px;
      border-radius:999px;
      background: rgba(var(--cyan), .95);
      animation: dbPing 1.9s ease-out infinite;
      pointer-events:none;
    }
    
    /* Drawer header button glow */
    .attention-drawer::before{
      content:"";
      position:absolute;
      inset:-12px;
      border-radius: 24px;
      background:
        radial-gradient(60% 70% at 20% 20%, rgba(var(--cyan), .45), rgba(var(--cyan),0) 60%),
        radial-gradient(70% 80% at 80% 40%, rgba(var(--violet), .40), rgba(var(--violet),0) 62%);
      z-index:-1;
      animation: dbBreath 2.4s ease-in-out infinite;
      pointer-events:none;
    }
    
    /* Round arrow badge that “pops out” */
    .demo-bounce-badge{
      position:absolute;
      left:-18px;
      top:50%;
      width:46px;
      height:46px;
      border-radius:999px;
      display:flex;
      align-items:center;
      justify-content:center;
      background: rgba(var(--surface), 0.88);
      border: 1px solid rgba(var(--cyan), 0.55);
      box-shadow: 0 18px 40px rgba(0,0,0,0.35);
      color: rgba(var(--text), 0.98);
      animation: dbFloat 1.8s ease-in-out infinite;
      pointer-events:none;
    }
    
    .demo-bounce-badge{
      transform: translateY(-50%);
    }
    
    .demo-bounce-badge::before{
      content:"";
      position:absolute;
      inset:-8px;
      border-radius:999px;
      background: radial-gradient(circle, rgba(var(--cyan), .38), rgba(var(--cyan),0) 70%);
      filter: blur(12px);
      opacity: .9;
      z-index:-1;
      animation: dbBreath 2.2s ease-in-out infinite;
    }
    
    /* Hide badge on small screens (keeps layout clean) */
    @media (max-width: 640px){
      .demo-bounce-badge{ display:none; }
    }
    
    /* Respect users who prefer reduced motion */
    @media (prefers-reduced-motion: reduce){
      .attention-pulse::before,
      .attention-pulse::after,
      .attention-drawer::before,
      .demo-bounce-badge,
      .demo-bounce-badge::before,
      .demo-live-pill .dot{ animation:none !important; }
    }
    
    /* ---------------------------------------------------
       Option A — LIVE DEMO pill + soft ring (no loud animation)
    --------------------------------------------------- */
    
    /* Default (SMALL) pill — used in the Hero "Live Demo / No Signup" button */
    .demo-live-pill{
      display:inline-flex;
      align-items:center;
      gap:10px;
      padding:6px 12px;          /* ✅ smaller again */
      border-radius:999px;
      font-size:11px;            /* ✅ smaller again */
      font-weight:800;
      letter-spacing:.11em;
      text-transform:uppercase;
      background: rgba(var(--cyan), .14);
      border: 1px solid rgba(var(--cyan), .32);
      color: rgba(var(--text), .98);
      white-space: nowrap;
    }
    
    /* BIG pill — use ONLY inside the drawer header */
    .demo-live-pill-lg{
      gap:12px;
      padding:8px 14px;
      font-size:12px;
    }
    
    @keyframes dbDotBreath {
      0%,100% {
        transform: scale(1);
        opacity: .75;
        box-shadow:
          0 0 0 6px rgba(var(--cyan), .10),
          0 0 12px rgba(var(--cyan), .45);
      }
      50% {
        transform: scale(1.45);
        opacity: 1;
        box-shadow:
          0 0 0 14px rgba(var(--cyan), .18),
          0 0 24px rgba(var(--cyan), .70);
      }
    }
    
    .demo-live-pill .dot{
      width:9px;                /* ✅ bigger dot */
      height:9px;
      border-radius:999px;
      background: rgba(var(--cyan), .95);
      animation: dbDotBreath 1.15s ease-in-out infinite;
      transform-origin: center;
    }

    /* Soft ring around the element (no extra border) */
    .soft-ring{
      position: relative;
      isolation: isolate;
    }
    .soft-ring::before{
      content:"";
      position:absolute;
      inset:-6px;
      border-radius: inherit;
    
      /* ✅ IMPORTANT: no border here -> prevents the “double border” */
      box-shadow: 0 0 0 10px rgba(var(--cyan), 0.06);
    
      opacity: .95;
      pointer-events:none;
      transition: box-shadow .2s ease, opacity .2s ease;
    }
    .soft-ring:hover::before{
      box-shadow: 0 0 0 12px rgba(var(--cyan), 0.09);
      opacity: 1;
    }
    
    /* (Optional) Stop the bouncing badge animation to match "soft" */
    .demo-bounce-badge,
    .demo-bounce-badge::before{
      animation: none !important;
    }

    /* ---------------------------------------------------
       DocuBills App UI (scoped) — Mini Demo (Phase 1/2/3)
    --------------------------------------------------- */
    .miniApp {
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
        /* ✅ Fix: prevent dark-landing text from bleeding into the light mini-app */
      color: var(--dark);
      color-scheme: light;
    }
    
    /* Ensure typical text elements inside cards inherit the correct dark color */
    .miniApp .card,
    .miniApp .form-section,
    .miniApp .price-option,
    .miniApp .column-options {
      color: var(--dark);
    }
    
    /* frame inside the dark landing */
    .miniApp.demo-frame{
      background: var(--body-bg);
      border-radius: 16px;
      padding: 14px;
      border: 1px solid rgba(148,163,184,0.35);
    }
    
    /* Shared app styles */
    .miniApp .page-title { font-size: 1.3rem; font-weight: 700; color: var(--primary); }
    
    .miniApp .card {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      transition: var(--transition);
      overflow: hidden;
      padding: 1.25rem;
      margin-bottom: 1rem;
    }
    
    .miniApp .form-section {
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1rem;
      margin-bottom: 1rem;
      background: var(--card-bg);
    }
    
    .miniApp .form-section-title {
      font-weight: 600;
      color: var(--primary);
      margin-bottom: 0.75rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .miniApp .form-group { margin-bottom: 1rem; }
    
    .miniApp .form-label {
      display:block;
      margin-bottom: .5rem;
      font-weight: 500;
      color: var(--dark);
    }
    
    .miniApp .form-control {
      width: 100%;
      padding: 0.75rem 0.9rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      background: var(--card-bg);
      color: var(--dark);
      font-size: 1rem;
      transition: var(--transition);
    }
    
    .miniApp .form-control:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }
    
    .miniApp .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1rem;
    }
    
    .miniApp .required::after { content:" *"; color: var(--danger); }
    
    /* Buttons */
    .miniApp .btn {
      padding: 0.75rem 1.25rem;
      border-radius: var(--radius);
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 1rem;
    }
    .miniApp .btn-primary { background: var(--primary); color: #fff; }
    .miniApp .btn-primary:hover { background: var(--secondary); box-shadow: var(--shadow-hover); }
    .miniApp .btn-secondary { background: #e9ecef; color: #111827; border: 1px solid var(--border); }
    .miniApp .btn-secondary:hover { background: #dee2e6; }
    .miniApp .btn-sm { padding: 0.55rem 0.9rem; font-size: 0.95rem; }
    
    /* OR divider */
    .miniApp .or-divider {
      display:flex; align-items:center; text-align:center;
      margin: 1rem 0; color: var(--gray);
    }
    .miniApp .or-divider::before,
    .miniApp .or-divider::after { content:''; flex:1; border-bottom: 1px solid var(--border); }
    .miniApp .or-divider::before { margin-right: 1rem; }
    .miniApp .or-divider::after  { margin-left: 1rem; }
    
    /* Upload area */
    .miniApp .upload-container {
      border: 2px dashed var(--border);
      border-radius: var(--radius);
      padding: 1.5rem;
      text-align: center;
      margin-top: 0.75rem;
      transition: var(--transition);
      cursor: pointer;
      background: #fff;
    }
    .miniApp .upload-container:hover {
      border-color: var(--primary);
      background: rgba(67, 97, 238, 0.05);
    }
    .miniApp .upload-icon { font-size: 2.2rem; color: var(--primary); margin-bottom: .75rem; }
    .miniApp .upload-text { color: var(--gray); margin-bottom: .5rem; }
    .miniApp .upload-hint { font-size: 0.9rem; color: var(--gray); }
    .miniApp .error-text { margin-top: 0.4rem; font-size: 0.85rem; color: var(--danger); }
    
    /* Custom table builder */
    .miniApp #demo_custom_table_container table { width:100%; border-collapse: collapse; margin-top: 0.75rem; }
    .miniApp #demo_custom_table_container th,
    .miniApp #demo_custom_table_container td { border:1px solid var(--border); padding:.5rem; min-height:2rem; }
    
    /* Step 2 pricing cards */
    .miniApp .price-option {
      padding: 1rem;
      border: 2px solid var(--border);
      border-radius: var(--radius);
      margin-bottom: 1rem;
      cursor: pointer;
      transition: var(--transition);
      background: #fff;
    }
    .miniApp .price-option:hover {
      border-color: var(--primary-light);
      background-color: rgba(67, 97, 238, 0.05);
    }
    .miniApp .price-option.active {
      border-color: var(--primary);
      background-color: rgba(67, 97, 238, 0.1);
    }
    .miniApp .price-option .column-options { display:none; }
    .miniApp .price-option.active .column-options { display:block; }
    
    .miniApp .column-options {
      padding: 1rem;
      background: rgba(0,0,0,0.03);
      border-radius: var(--radius);
      margin-top: 1rem;
    }
    
    .miniApp .manual-notice {
      background-color: #fff8e6;
      border-left: 4px solid var(--warning);
      padding: 1rem;
      margin-top: 1rem;
      border-radius: 0 var(--radius) var(--radius) 0;
      color: var(--dark);
      font-size: 14px;
    }
    
    .miniApp .alert {
      padding: 0.9rem 1rem;
      border-radius: var(--radius);
      margin-bottom: 1rem;
      border-left: 4px solid var(--danger);
      background-color: #ffe5ea;
      color: #721c24;
      font-size: 0.95rem;
    }
    
/* ---------------------------------------------------
   Phase 2 (mini demo) — use the same DocuBills app UI
   (so it matches generate_invoice.php styling consistently)
--------------------------------------------------- */

/* Page header (shared look for all phases) */
.miniApp .page-header{
  display:flex;
  justify-content: space-between;
  align-items:center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.miniApp .page-actions{
  display:flex;
  gap:10px;
  align-items:center;
  flex-wrap: wrap;
}

/* Step 2 lists (price columns + include columns) */
.miniApp #demoPriceColumns,
.miniApp #demoColumnPicker{
  display:grid;
  gap: 10px;
}

/* Clickable row style (same vibe as your dashboard rows) */
.miniApp .option-row{
  display:flex;
  align-items:center;
  gap:10px;
  padding: 10px 12px;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  background:#fff;
  cursor:pointer;
  transition: var(--transition);
}

.miniApp .option-row:hover{
  border-color: var(--primary-light);
  background: rgba(67, 97, 238, 0.05);
}

.miniApp .option-row input{
  width: 16px;
  height: 16px;
  margin: 0;
}

    /* ---------------------------------------------------
       DocuBills App UI (scoped) — Phase 3: Invoice Preview
       --------------------------------------------------- */
    #miniDemo3 .invoice-box {
      max-width: 960px;
      margin: 0 auto;
      padding: 18px;
      border: 1px solid var(--border);
      background: var(--card-bg);
      border-radius: 12px;
      box-shadow: var(--shadow);
    }
    
    #miniDemo3 table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
      table-layout: fixed;
    }
    #miniDemo3 th, #miniDemo3 td {
      border: 1px solid var(--border);
      padding: 8px;
      vertical-align: top;
      text-align: left;
      font-size: 12px;
      word-break: break-word;
    }
    #miniDemo3 th {
      background: var(--primary);
      color: #fff;
      font-weight: 600;
    }
    
    #miniDemo3 .invoice-header-section {
      display: flex;
      justify-content: space-between;
      gap: 14px;
      margin: 10px 0 18px;
    }
    #miniDemo3 .company-name { font-weight: 800; font-size: 16px; margin-bottom: 4px; }
    #miniDemo3 .company-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 8px;
    }
    
   #miniDemo3 .company-logo-fallback{
      width: 44px;
      height: 44px;
      border-radius: 12px;
      flex: 0 0 44px;
    }
    
    /* ✅ Logo should not be forced into a square */
    #miniDemo3 .company-logo-img{
      height: 44px;
      width: auto;
      max-width: 180px;
      border-radius: 12px;
      flex: 0 0 auto;
      object-fit: contain;
      display: none; /* shown only if image loads */
    }
    
    /* ✅ If logo is present, hide duplicate text wordmark */
    #miniDemo3 .company-brand.has-logo .company-wordmark{
      display: none;
    }

    #miniDemo3 .company-logo-fallback {
      background: var(--primary);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
    }
    
    #miniDemo3 .company-wordmark {
      font-weight: 800;
      color: var(--primary);
    }

    #miniDemo3 .bill-to { text-align: right; }
    
    #miniDemo3 .column-toggle-wrapper{
      margin-top: 10px;
      margin-bottom: 8px;
      padding: 10px 12px;
      background: #f8f9fa;
      border: 1px solid var(--border);
      border-radius: 10px;
    }
    #miniDemo3 .column-toggle-list {
      display: flex;
      flex-wrap: wrap;
      gap: 8px 10px;
    }
    #miniDemo3 .column-toggle-item{
      font-size: 12px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 8px;
      border-radius: 8px;
      background: #fff;
      border: 1px solid #e0e0e0;
    }
    #miniDemo3 .column-toggle-item input { margin: 0; }
    #miniDemo3 .price-column-label{
      border-color: var(--primary);
      background: #e9f0ff;
    }
    #miniDemo3 .required-pill{
      font-size: 10px;
      padding: 1px 6px;
      border-radius: 999px;
      background: var(--primary);
      color: #fff;
      text-transform: uppercase;
      letter-spacing: .03em;
    }
    
    #miniDemo3 .editable-cell { background: #fff9db; }
    #miniDemo3 .row-disabled { opacity: .55; background: #f8f9fa; }
    #miniDemo3 .row-disabled td { background:#f5f5f5; color:#777; pointer-events:none; }
    #miniDemo3 .row-disabled td:first-child { pointer-events:auto; }
    
    #miniDemo3 .flex-container {
      display:flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 14px;
      gap: 10px;
      flex-wrap: wrap;
    }
    #miniDemo3 .total-display {
      font-size: 15px;
      font-weight: 700;
      text-align: right;
      padding: 10px 12px;
      background: #f8f9fa;
      border-radius: 10px;
      border: 1px solid var(--border);
      display:flex;
      align-items:center;
      gap: 8px;
    }
    #miniDemo3 .manual-total-container{
      background: #f8f9fa;
      padding: 12px;
      border-radius: 10px;
      border: 1px solid var(--border);
      min-width: 260px;
    }
    
    #miniDemo3 .stripe-warning {
      margin-top: 10px;
      padding: 10px 12px;
      border-radius: 10px;
      border: 1px solid var(--warning);
      background: #fff8e6;
      color: #7c3a00;
      font-size: 12px;
      display: flex;
      gap: 10px;
      align-items: flex-start;
    }
    #miniDemo3 .stripe-warning.hidden { display:none; }
    #miniDemo3 .btn-disabled-stripe { opacity: .6; cursor:not-allowed; }
    
    #miniDemo3 .date-section { display:flex; gap:14px; margin: 14px 0; flex-wrap: wrap; }
    #miniDemo3 .date-column { flex: 1; min-width: 240px; }
    
    #miniDemo3 .recurring-toggle{
      display:inline-flex; align-items:center; gap:8px;
      padding: 8px 14px;
      border-radius: 999px;
      border:none;
      font-size: 13px;
      font-weight: 600;
      cursor:pointer;
      color:#fff;
    }
    #miniDemo3 .recurring-on { background:#16a34a; }
    #miniDemo3 .recurring-off { background:#b91c1c; }
    
    #miniDemo3 .bank-drawer{
      max-height:0; overflow:hidden; opacity:0; transform: translateY(-4px);
      transition: max-height .3s ease, opacity .25s ease, transform .25s ease;
    }
    #miniDemo3 .bank-drawer.open{
      max-height: 800px; opacity:1; transform: translateY(0);
      margin-top: 10px;
    }
    
    .section-kicker{
      letter-spacing: .14em;
      text-transform: uppercase;
      font-weight: 800;
      font-size: 12px;
      color: rgba(var(--text), .75);
    }
    .section-title{
      font-weight: 900;
      color: rgba(var(--text), .98);
    }
    .section-sub{
      color: rgba(var(--text), .72);
    }
    
    /* FAQ accordion */
    .faq-item[aria-expanded="true"] .faq-chevron{ transform: rotate(180deg); }
    
    .grid-3{ display:grid; gap:16px; grid-template-columns: repeat(3, minmax(0,1fr)); }
    @media (max-width: 980px){ .grid-3{ grid-template-columns:1fr; } }
    
    .icon-badge{
      width:38px;height:38px;border-radius:12px;
      display:flex;align-items:center;justify-content:center;
      background: rgba(var(--primary), .12);
      border: 1px solid rgba(var(--line), .25);
      color: rgba(var(--text), .96);
    }
    
    .feature-list{ list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:10px; }
    .feature-list li{ display:flex; gap:10px; align-items:flex-start; color: rgba(var(--text), .92); }
    .feature-list i{ margin-top:2px; color: rgba(var(--cyan), .95); }
    
    .tag-row{ display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; }
    .tag{
      font-size:12px; padding:6px 10px; border-radius:999px;
      border: 1px solid rgba(var(--line), .25);
      background: rgba(var(--surface), .4);
      color: rgba(var(--muted), 1);
    }

    /* ---------------------------
       Features v2 helper classes
    ----------------------------*/
    .section{
      padding: 0 0 80px;
    }
    
    .container{
      max-width: 1536px;           /* close to max-w-screen-2xl */
      margin: 0 auto;
      padding: 0 16px;
    }
    @media (min-width: 640px){ .container{ padding: 0 32px; } }
    @media (min-width: 1024px){ .container{ padding: 0 48px; } }
    
    .section-head{
      text-align: center;
      max-width: 760px;
      margin: 0 auto 22px;
    }
    
    .pill{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 6px 12px;
      border-radius: 999px;
      border: 1px solid rgba(var(--line), .25);
      background: rgba(var(--surface), .35);
      color: rgba(var(--text), .92);
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .12em;
      text-transform: uppercase;
    }
    
    .glass-pad{
      padding: 24px;
      border-radius: 24px;
    }
    
    /* Landing buttons (NOT miniApp buttons) */
    .btn{
      display:inline-flex;
      align-items:center;
      gap:10px;
      padding: 10px 16px;
      border-radius: 16px;
      font-weight: 700;
      font-size: 14px;
      text-decoration: none;
      transition: transform .15s ease, filter .15s ease, border-color .15s ease;
    }
    
    .btn-ghost{
      border: 1px solid rgba(var(--line), .35);
      background: rgba(255,255,255,.04);
      color: rgba(var(--text), .92);
    }
    .btn-ghost:hover{
      border-color: rgba(var(--line), .55);
      filter: brightness(1.05);
      transform: translateY(-1px);
    }
    
    .btn-primary{
      background-image: linear-gradient(135deg, rgba(var(--violet), 1), rgba(var(--cyan), 1));
      color: #fff;
      box-shadow: 0 18px 55px rgba(0,0,0,.35);
    }
    .btn-primary:hover{
      filter: brightness(1.05);
      transform: translateY(-1px);
    }

  </style>

  <script>
    // ✅ Update these to match your app routes
    const LOGIN_URL = "login.php";
    const GET_STARTED_URL = "register.php";
  </script>
</head>

<body class="bg-slate-950 text-slate-100">
  <!-- Background -->
  <div class="relative min-h-screen overflow-hidden">
    <div class="absolute inset-0">
      <div class="absolute -top-48 -left-48 h-[520px] w-[520px] rounded-full bg-violet-500/30 blur-[90px]"></div>
        <div class="absolute top-32 -right-48 h-[520px] w-[520px] rounded-full bg-fuchsia-500/20 blur-[90px]"></div>
        <div class="absolute bottom-0 left-1/2 h-[520px] w-[760px] -translate-x-1/2 rounded-full bg-cyan-400/15 blur-[110px]"></div>
      <div class="absolute inset-0 bg-gradient-to-b from-slate-950 via-slate-950 to-slate-900"></div>
    </div>

    <!-- Page container -->
    <div class="relative">
        
      <!-- Header / Nav (Premium Corporate) -->
    <header class="mx-auto max-w-screen-2xl px-4 sm:px-8 lg:px-12 pt-6 sticky top-4 z-40">
      <div class="glass shadow-soft rounded-2xl px-4 sm:px-6 py-4 flex items-center justify-between">
        <a href="#" class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-xl bg-violet-600/15 border border-violet-400/30 flex items-center justify-center">
            <i class="fa-solid fa-file-invoice text-cyan-300"></i>
          </div>
          <div class="leading-tight">
            <div class="text-white font-semibold text-lg">DocuBills</div>
            <div class="text-slate-300 text-xs">Spreadsheet-first invoicing</div>
          </div>
        </a>
    
        <nav class="hidden md:flex items-center gap-6 text-sm text-slate-200">
          <a href="#how" class="hover:text-white">Workflow</a>
          <a href="#features" class="hover:text-white">Product</a>
          <a href="#security" class="hover:text-white">Admin & Security</a>
          <a href="#pricing" class="hover:text-white">Pricing</a>
          <a href="#demo" class="hover:text-white">Live Demo</a>
          <a href="#faq" class="hover:text-white">FAQ</a>
        </nav>
    
        <div class="flex items-center gap-2 sm:gap-3">
          <a id="loginBtnTop" href="login.php"
             class="px-3 sm:px-4 py-2 rounded-xl border border-slate-600/60 text-slate-200 hover:text-white hover:border-slate-400 transition">
            Login
          </a>
          <a id="ctaBtnTop" href="register.php"
             class="px-3 sm:px-4 py-2 rounded-xl cta-gradient text-white font-semibold transition ring-glow">
            Start Free
            <i class="fa-solid fa-arrow-right ml-2"></i>
          </a>
        </div>
      </div>
    </header>

      <!-- Hero -->
      <section class="mx-auto max-w-screen-2xl px-4 sm:px-8 lg:px-12 pt-10 pb-16">
        <div class="grid lg:grid-cols-2 gap-10 items-center">
          <!-- Left -->
            <div class="text-left">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-600/50 bg-white/5 text-slate-200 text-sm">
              <i class="fa-solid fa-bolt text-cyan-300"></i>
              <span>Upload your sheet → choose price → send an invoice (no templates)</span>
            </div>

            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-600/50 bg-white/5 text-slate-200 text-sm">
              <i class="fa-solid fa-bolt text-cyan-300"></i>
              <span>3 steps. No column mapping. Built for Excel & Google Sheets.</span>
            </div>
            
            <h1 class="mt-5 text-white text-4xl sm:text-5xl font-extrabold leading-tight">
              Turn your <span class="text-cyan-300">spreadsheet</span> into a client-ready invoice — <span class="text-white">without</span> rebuilding your workflow.
            </h1>
            
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
              <a id="ctaBtnHero2" href="register.php"
                 class="inline-flex justify-center items-center px-5 py-3 rounded-2xl cta-gradient text-white font-semibold transition ring-glow">
                Create your first invoice
                <i class="fa-solid fa-sparkles ml-2"></i>
              </a>
            
              <a href="#demo"
                 class="inline-flex justify-center items-center gap-3 px-5 py-3 rounded-2xl border border-slate-600/60 text-slate-100 hover:text-white hover:border-slate-400 transition soft-ring">
                <span class="demo-live-pill"><span class="dot"></span>LIVE DEMO</span>
                <span class="kbd">No Signup</span>
              </a>
            </div>
            
            <div class="mt-5 flex flex-wrap gap-2 text-xs text-slate-300">
              <span class="px-3 py-1 rounded-full border border-slate-700/60 bg-white/5">
                <i class="fa-solid fa-check text-cyan-300 mr-2"></i>No credit card to start
              </span>
              <span class="px-3 py-1 rounded-full border border-slate-700/60 bg-white/5">
                <i class="fa-solid fa-table-cells text-cyan-300 mr-2"></i>Works with messy sheets
              </span>
              <span class="px-3 py-1 rounded-full border border-slate-700/60 bg-white/5">
                <i class="fa-solid fa-toggle-on text-cyan-300 mr-2"></i>Hide rows/columns before PDF
              </span>
            </div>
            
            <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-slate-300">
              <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-600/50 bg-white/5">
                <i class="fa-solid fa-circle-check text-cyan-300"></i> No complex column mapping
              </span>
              <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-600/50 bg-white/5">
                <i class="fa-solid fa-circle-check text-cyan-300"></i> Hide rows/columns before PDF
              </span>
              <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-600/50 bg-white/5">
                <i class="fa-solid fa-circle-check text-cyan-300"></i> Reminders + recurring + payments
              </span>
            </div>
         </div>
         
          <!-- Right: Hero image (uses /assets/hero.png) -->
            <div class="w-full">
              <div class="glass shadow-soft rounded-3xl p-4 sm:p-5">
                <div class="rounded-2xl border border-slate-700/50 bg-slate-950/25 overflow-hidden
                            aspect-[14/7] min-h-[220px] sm:min-h-[280px] flex items-center justify-center">
                  <img
                    src="/assets/hero.png"
                    alt="DocuBills preview"
                    class="w-full h-full object-contain"
                  />
                </div>
              </div>
            </div>

            <!-- Trust / quick bullets -->
            <div class="lg:col-span-2 mt-8 grid sm:grid-cols-3 gap-3">
              <div class="glass rounded-2xl p-4">
                <div class="text-white font-semibold"><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>Free to start</div>
                <div class="text-slate-300 text-sm mt-1">Create invoices instantly. Upgrade only if you want premium automations.</div>
              </div>
              <div class="glass rounded-2xl p-4">
                <div class="text-white font-semibold"><i class="fa-solid fa-layer-group text-cyan-300 mr-2"></i>Built for your sheet</div>
                <div class="text-slate-300 text-sm mt-1">Use your columns as-is — no rebuilding your workflow to fit a tool.</div>
              </div>
              <div class="glass rounded-2xl p-4">
                <div class="text-white font-semibold"><i class="fa-solid fa-robot text-cyan-300 mr-2"></i>Send & follow up</div>
                <div class="text-slate-300 text-sm mt-1">Recurring invoices + smart reminders to reduce “Did you get my invoice?”</div>
              </div>
            </div>
            
            <!-- Who it's for (conversion strip) -->
            <div class="lg:col-span-2 mt-6 glass rounded-3xl p-6 sm:p-7 shadow-soft">
              <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                  <div class="text-white font-extrabold text-xl section-title">Built for people who invoice from data</div>
                  <div class="text-slate-300 mt-2 text-sm max-w-2xl">
                    Dispatch & transport, agencies, field services, operations teams, freelancers — if your billing lives in rows and columns,
                    DocuBills is the fastest way to turn it into invoices.
                  </div>
                </div>
            
                <div class="flex flex-wrap gap-2">
                  <span class="px-3 py-1.5 rounded-full border border-slate-600/50 bg-white/5 text-slate-200 text-sm">Dispatch & Transportation</span>
                  <span class="px-3 py-1.5 rounded-full border border-slate-600/50 bg-white/5 text-slate-200 text-sm">Agencies</span>
                  <span class="px-3 py-1.5 rounded-full border border-slate-600/50 bg-white/5 text-slate-200 text-sm">Field Services</span>
                  <span class="px-3 py-1.5 rounded-full border border-slate-600/50 bg-white/5 text-slate-200 text-sm">Freelancers</span>
                  <span class="px-3 py-1.5 rounded-full border border-slate-600/50 bg-white/5 text-slate-200 text-sm">Back-office Ops</span>
                </div>
              </div>
            </div>

          <!-- Right: Interactive mini-demo -->
          <div id="demo" class="lg:col-span-2">
          
          <!-- Drawer header -->
          <button id="demoDrawerBtn" type="button"
                    aria-expanded="false"
                    aria-controls="demoDrawerPanel"
                    class="w-full glass shadow-soft rounded-3xl p-4 sm:p-5 flex items-center justify-between gap-4 text-left soft-ring">
            
              <!-- Left side: pill + text -->
              <div class="flex flex-col sm:flex-row sm:items-center gap-3 min-w-0 flex-1">
                  <span class="demo-live-pill demo-live-pill-lg shrink-0">
                    <span class="dot"></span>LIVE DEMO
                  </span>
                
                  <div class="min-w-0 flex-1 text-center">
                    <div class="text-white font-semibold text-lg leading-tight">Interactive Mini-Demo</div>
                    <div class="text-slate-300 text-sm mt-1">
                      Open the real workflow (Phase 1 → 2 → 3). No signup.
                    </div>
                  </div>
              </div>

              <!-- Right side: open/close -->
              <div class="shrink-0 flex items-center gap-2 text-slate-200">
                <span id="demoDrawerLabel" class="hidden sm:inline text-sm">Open</span>
                <i id="demoDrawerIcon" class="fa-solid fa-chevron-down transition-transform duration-200"></i>
              </div>
          </button>
            
          <!-- Drawer panel -->
          <div id="demoDrawerPanel" class="demo-drawer-panel">
            <div class="glass shadow-soft rounded-3xl p-5 sm:p-6 mt-3">

            <!-- Demo header -->
            <div class="flex items-start justify-between gap-3">
              <div>
                <div class="text-white font-semibold text-lg">Interactive Mini-Demo</div>
                <div class="text-slate-300 text-sm mt-1">
                  Experience the real workflow: <span class="text-white">create-invoice → price-select → preview</span>
                </div>
              </div>
              <button id="demoReset"
                      class="px-3 py-2 rounded-xl border border-slate-600/60 text-slate-200 hover:text-white hover:border-slate-400 transition text-sm">
                Reset
              </button>
            </div>

            <!-- Demo tabs -->
            <div class="mt-4 grid grid-cols-3 gap-2 text-sm">
              <button id="tab1" class="demo-tab demo-tab-active px-3 py-2 rounded-xl border border-slate-600/50 text-slate-100 text-left">
                <div class="font-semibold">Phase 1</div>
                <div class="text-slate-300 text-xs">Bill To + Data Source</div>
              </button>
              <button id="tab2" class="demo-tab px-3 py-2 rounded-xl border border-slate-700/50 text-slate-300 text-left">
                <div class="font-semibold">Phase 2</div>
                <div class="text-slate-400 text-xs">Map Price Column</div>
              </button>
              <button id="tab3" class="demo-tab px-3 py-2 rounded-xl border border-slate-700/50 text-slate-300 text-left">
                <div class="font-semibold">Phase 3</div>
                <div class="text-slate-400 text-xs">Invoice Preview</div>
              </button>
            </div>

           <!-- Phase 1 -->
            <div id="phase1" class="fade-in">
              <div id="miniDemo" class="demo-frame miniApp">
                <div class="card">
                  <div class="page-header" style="margin-bottom: 1rem;">
                    <h1 class="page-title">Create New Invoice</h1>
                  </div>
            
                  <form id="demoInvoiceFormStep1" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="custom_table_html" id="demo_custom_table_html" value="">
            
                    <!-- Bill To Section -->
                    <div class="form-section">
                      <h2 class="form-section-title">
                        <i class="fas fa-building"></i> Bill To Information
                      </h2><br>
            
                      <div class="form-grid">
                        <div class="form-group">
                          <label class="form-label required">Company Name</label>
                          <input type="text" id="demo_bill_to_name" class="form-control" placeholder="Enter company name" required autocomplete="off">
                          <div id="demoClientSuggestions" class="autocomplete-list" style="display:none;"></div>
                        </div>
            
                        <div class="form-group">
                          <label class="form-label">Contact Name</label>
                          <input type="text" id="demo_bill_to_rep" class="form-control" placeholder="Contact person's name">
                        </div>
            
                        <div class="form-group">
                          <label class="form-label">Address</label>
                          <input type="text" id="demo_bill_to_address" class="form-control" placeholder="Full address">
                        </div>
            
                        <div class="form-group">
                          <label class="form-label">Phone</label>
                          <input type="text" id="demo_bill_to_phone" class="form-control" placeholder="Phone number">
                        </div>
            
                        <div class="form-group">
                          <label class="form-label required">Email</label>
                          <input type="email" id="demo_bill_to_email" class="form-control" placeholder="Email address" required>
                        </div>
                      </div>
                    </div>
            
                    <!-- Data Source Section -->
                    <div class="form-section">
                      <h2 class="form-section-title">
                        <i class="fas fa-database"></i> Invoice Data Source
                      </h2>
            
                      <div class="form-group">
                          <label class="form-label">Choose Invoice Source:</label>
                        
                          <div class="upload-hint" style="margin:8px 0 10px;">
                            Demo uses pre-loaded sample bookings data so you can try the full workflow instantly.
                          </div>
                        
                          <label style="margin-right:1em;">
                            <input type="radio" name="invoice_source" value="google" checked>
                            Google Sheet URL
                          </label>
                        
                          <label style="margin-right:1em;">
                            <input type="radio" name="invoice_source" value="upload">
                            Upload Excel File
                          </label>
                        </div>
                        
                        <!-- Google Section -->
                        <div id="demo_google_section">
                          <div class="form-group">
                            <label class="form-label">Google Sheet URL</label>
                            <input type="text" id="demo_google_sheet_url" class="form-control" value="🔒 Demo Locked" disabled>
                            <p class="upload-hint">This field is disabled in demo mode.</p>
                          </div>
                        </div>
            
                      <div class="or-divider" style="display:none;">OR</div>
            
                      <!-- Upload Section -->
                        <div id="demo_upload_section" style="display:none;">
                          <div class="form-group">
                            <label class="form-label">Upload Excel File</label>
                        
                            <div class="upload-container" id="demoUploadArea" style="cursor:not-allowed; opacity:.9;">
                              <div class="upload-icon"><i class="fas fa-lock"></i></div>
                              <p class="upload-text"><strong>🔒 Demo Locked</strong></p>
                              <p class="upload-hint">This field is disabled in demo mode.</p>
                              <input type="file" id="demo_excel_file" accept=".xls,.xlsx" style="display:none;" disabled>
                            </div>

                            <div id="demo_fileName" style="margin-top:10px; font-size:0.9rem; color:var(--primary); display:none;"></div>
                            <div id="demo_fileError" class="error-text" style="display:none;"></div>
                          </div>
                        </div>
            
                      <!-- Custom Table Builder -->
                      <div id="demo_custom_table_builder" style="display:none; margin-top:1.5rem;">
                        <div class="form-group">
                          <label class="form-label">Number of columns (1–7):</label>
                          <select id="demo_custom_col_count" class="form-control" style="width:auto; display:inline-block; margin-left:0.5rem;">
                            <option value="1">1</option><option value="2">2</option><option value="3">3</option>
                            <option value="4">4</option><option value="5">5</option><option value="6">6</option>
                            <option value="7">7</option>
                          </select>
            
                          <button type="button" id="demo_generate_custom_table" class="btn btn-secondary btn-sm" style="margin-left:1rem;">
                            Generate Table
                          </button>
                        </div>
            
                        <div id="demo_custom_table_container"></div>
                      </div>
                    </div>
            
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-file-invoice"></i> Create Invoice
                    </button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Phase 2 -->
                <div id="phase2" class="hidden fade-in">
                  <div id="miniDemo2" class="demo-frame miniApp">
                
                    <div class="page-header">
                      <div class="page-title">Configure Invoice Pricing</div>
                      <div class="page-actions"></div>
                    </div>
                
                    <div class="card">

                  <!-- Error (simulated) -->
                  <div id="demo_step2_error" class="alert" style="display:none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="demo_step2_error_text"></span>
                  </div>
            
                  <form id="demoPriceSelectForm" method="post" action="">
                    <div class="form-section">
                      <h2 class="form-section-title"><i class="fas fa-money-bill-wave"></i> Pricing Method</h2>
            
                      <div class="price-option active" id="demoAutoPriceOption">
                        <label>
                          <input type="radio" name="price_mode" value="column" checked>
                          <strong>Automatic Pricing</strong> - Use a column from my data
                        </label>
            
                        <div class="column-options">
                          <p style="margin-bottom: 1.5rem;">Select which column contains item prices:</p>
            
                          <!-- Filled by JS -->
                          <div id="demoPriceColumns"></div>
                        </div>
                      </div>
            
                      <div class="price-option" id="demoManualPriceOption">
                        <label>
                          <input type="radio" name="price_mode" value="manual">
                          <strong>Manual Pricing</strong> - I'll enter the total invoice amount myself
                        </label>
            
                        <div class="manual-notice">
                          <i class="fas fa-info-circle"></i> You'll enter the total amount on the next screen
                        </div>
                      </div>
                    </div>
            
                    <div class="form-section">
                      <h2 class="form-section-title">
                        <i class="fas fa-columns"></i> Columns to Include
                        <small style="font-weight:400; margin-left:1rem; color:var(--gray);">(max 15)</small>
                      </h2>
                    
                      <div id="demoColumnPicker" class="form-group" style="max-height:300px; overflow:auto;">
                        <!-- Filled by JS -->
                      </div>
                    </div>
            
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-arrow-right"></i>
                      Continue to Invoice Preview
                    </button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Phase 3 -->
            <div id="phase3" class="hidden fade-in">
              <div id="miniDemo3" class="demo-frame miniApp">
                <div class="card">
                  <div class="page-header" style="margin-bottom: 1rem;">
                    <div class="page-title">Invoice Preview</div>
                    <div class="page-actions" style="display:flex; gap:10px; align-items:center;">
                      <button type="button" class="btn btn-primary" id="demoSaveInvoiceBtn">
                        Save Invoice
                      </button>
                    </div>
                  </div>
            
                  <div class="invoice-box">
            
                    <!-- Row 1 – Bill To -->
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                      <div></div>
                      <div style="font-size:16px;font-weight:800;text-align:right;">Bill&nbsp;To:</div>
                    </div>
            
                    <!-- Row 2 – company vs bill-to -->
                    <div class="invoice-header-section">
                      <div class="company-info">

                      <!-- ✅ Logo inside Company Info (left block) -->
                      <div class="company-brand" id="demoCompanyBrand">
                          <img
                            id="demoCompanyLogoImg"
                            class="company-logo-img"
                            src=""
                            data-candidates="/assets/docubills-logo.png,/assets/logo.png,/assets/logo.svg,/assets/docubills.png,/assets/logo.webp,/assets/logo.jpg"
                            alt="DocuBills Logo">
                          <div id="demoCompanyLogoFallback" class="company-logo-fallback">DB</div>
                          <div class="company-wordmark" id="demoCompanyWordmark">DocuBills</div>
                        </div>
                    
                      <div class="company-name">DocuBills Inc.</div>
                      <div>Billing Street 101, Toronto, ON</div>
                      <div>(000) 000-0000</div>
                      <div>billing@docubills.com</div>
                      <div>GST/HST: 123456789</div>
                    </div>
            
                      <div class="bill-to" id="demoBillToBlock"></div>
                    </div>
            
                    <!-- Column selector (above table) -->
                    <div class="column-toggle-wrapper">
                      <div class="form-label" style="margin-bottom:6px;"><strong>Columns to include:</strong></div>
                      <div class="column-toggle-list" id="demoColToggleList"></div>
                    </div>
            
                    <!-- Invoice Table -->
                    <table id="demoInvoiceTable">
                      <thead>
                        <tr id="demoInvoiceHeadRow"></tr>
                      </thead>
                      <tbody id="demoInvoiceBody"></tbody>
                    </table>
            
                    <!-- Total Amount -->
                    <div class="flex-container">
                      <div id="demoTotalLeftNote" style="font-size:12px;color:var(--gray);">
                        Tip: edit cells to see total update (auto mode).
                      </div>
            
                      <div id="demoTotalRight"></div>
                    </div>
            
                    <!-- Stripe warning -->
                    <div id="demoStripeLimitWarning" class="stripe-warning hidden">
                      <i class="fas fa-exclamation-triangle"></i>
                      <div>
                        <div><strong>Online payment limit reached</strong></div>
                        <div style="margin-top:4px;">
                          Stripe Checkout limit:
                          <strong><span class="currencyPrefix" id="demoStripePrefix1">CA$</span>999,999.99</strong><br>
                          This invoice total is
                          <strong><span class="currencyPrefix" id="demoStripePrefix2">CA$</span><span id="demoStripeLimitDisplay">0.00</span></strong>
                        </div>
            
                        <label style="display:block; margin-top:8px; font-size:12px;">
                          <input type="checkbox" id="demoManualOnlyAck">
                          I understand Stripe won’t be available. Create it for manual payment only.
                        </label>
                      </div>
                    </div>
            
                    <!-- Date Pickers -->
                    <div class="date-section">
                      <div class="date-column">
                        <div class="form-group">
                          <label class="form-label"><strong>Invoice Date:</strong></label>
                          <input type="date" class="form-control" id="demo_invoice_date">
                        </div>
                        <div class="form-group">
                          <label class="form-label"><strong>Invoice Time:</strong></label>
                          <input type="time" class="form-control" id="demo_invoice_time" step="60">
                        </div>
                      </div>
            
                      <div class="date-column">
                        <div class="form-group">
                          <label class="form-label"><strong>Due Date:</strong></label>
                          <input type="date" class="form-control" id="demo_due_date">
                        </div>
                        <div class="form-group">
                          <label style="font-size:13px; cursor:pointer;">
                            <input type="checkbox" id="demo_toggle_due_time">
                            Include Due Time
                          </label>
                          <div id="demo_due_time_container" style="display:none; margin-top:8px;">
                            <label class="form-label"><strong>Due Time:</strong></label>
                            <input type="time" class="form-control" id="demo_due_time" step="60">
                          </div>
                        </div>
                      </div>
                    </div>
            
                    <!-- Recurring -->
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:8px;">
                      <div style="font-size:13px;color:var(--gray);">
                        <strong>Recurring Invoice:</strong> Monthly on this invoice date.
                      </div>
                      <button type="button" id="demoRecurringToggle" class="recurring-toggle recurring-off">
                        <i class="fas fa-sync-alt"></i>
                        <span id="demoRecurringText">Disabled (One-time)</span>
                      </button>
                    </div>
            
                    <!-- Banking -->
                    <div class="form-group" style="margin-top:14px;display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                      <div>
                        <label class="form-label"><strong>Banking Details (for this invoice)</strong></label>
                        <p style="font-size:12px;color:var(--gray);margin:4px 0 10px;">
                          Pre-filled from Settings → Payment Methods (demo values shown here).
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
                            <input class="form-control" value="DocuBills Inc.">
                          </div>
                          <div class="form-group">
                            <label class="form-label">Bank Name</label>
                            <input class="form-control" value="Demo Bank">
                          </div>
                          <div class="form-group">
                            <label class="form-label">Account Number</label>
                            <input class="form-control" value="123456789">
                          </div>
                        </div>
            
                        <div class="date-column">
                          <div class="form-group">
                            <label class="form-label">IBAN</label>
                            <input class="form-control" value="DE00 0000 0000 0000 0000 00">
                          </div>
                          <div class="form-group">
                            <label class="form-label">SWIFT / BIC</label>
                            <input class="form-control" value="ABCDEFXX">
                          </div>
                          <div class="form-group">
                            <label class="form-label">Routing / Sort Code</label>
                            <input class="form-control" value="000111">
                          </div>
                        </div>
                      </div>
            
                      <div class="form-group">
                        <label class="form-label">Additional Payment Instructions</label>
                        <textarea class="form-control" rows="3">Please include invoice number in the transfer reference.</textarea>
                      </div>
                    </div>
            
                  </div>
                </div>
              </div>
            </div>

            <!-- Under demo: micro CTA -->
            <div class="mt-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
              <div class="text-slate-300 text-sm">
                Want this workflow live? Create your account in under 60 seconds.
              </div>
              <a id="ctaBtnDemoBottom" href="register.php"
                 class="px-4 py-2 rounded-2xl bg-white text-slate-900 font-semibold hover:bg-slate-100 transition">
                Sign up free <i class="fa-solid fa-arrow-right ml-2"></i>
              </a>
            </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</section>


      <!-- 4-step process flow -->
      <section id="how" class="mx-auto max-w-screen-2xl px-4 sm:px-8 lg:px-12 pb-16">
        <div class="glass rounded-3xl p-6 sm:p-8 shadow-soft">
          <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
            <div>
              <div class="text-white font-extrabold text-2xl">How it works</div>
              <div class="text-slate-300 mt-2">A fast 4-step workflow: keep your spreadsheet, generate invoices, and get paid faster.</div>
            </div>
            <a href="#demo" class="text-cyan-200 hover:text-white text-sm font-semibold">
              Try the demo again <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
          </div>

          <div class="mt-6 grid grid-cols-1 gap-12">
            <!-- Step 01 -->
                  <div class="grid grid-cols-1 md:grid-cols-[1fr_660px] gap-6 items-center">
                    <div class="rounded-2xl border border-slate-700/50 bg-slate-950/25 p-5">
                      <div class="text-cyan-200 font-semibold"><span class="mr-2">01</span>Bill To</div>
                      <div class="text-white font-semibold mt-2">Add the client</div>
                      <div class="text-slate-300 text-sm mt-2">Enter (or reuse) billing details once — DocuBills remembers it.</div>
                    </div>
                
                    <div class="md:justify-self-end w-full max-w-[660px]">
                      <!-- thinner hero-like frame -->
                      <div class="glass shadow-soft rounded-3xl p-3">
                        <div class="rounded-2xl border border-slate-700/35 bg-slate-950/20 overflow-hidden">
                          <img
                            src="/assets/how-01.png"
                            alt="Step 1 - Bill To"
                            class="block w-full h-auto max-h-[620px] object-contain"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                
                  <!-- Step 02 -->
                  <div class="grid grid-cols-1 md:grid-cols-[1fr_660px] gap-6 items-center">
                    <div class="rounded-2xl border border-slate-700/50 bg-slate-950/25 p-5">
                      <div class="text-cyan-200 font-semibold"><span class="mr-2">02</span>Connect Data</div>
                      <div class="text-white font-semibold mt-2">Bring your data</div>
                      <div class="text-slate-300 text-sm mt-2">Upload Excel or link Google Sheets — keep the same columns you already use.</div>
                    </div>
                
                    <div class="md:justify-self-end w-full max-w-[660px]">
                      <!-- thinner hero-like frame -->
                      <div class="glass shadow-soft rounded-3xl p-3">
                        <div class="rounded-2xl border border-slate-700/35 bg-slate-950/20 overflow-hidden">
                          <img
                            src="/assets/how-02.png"
                            alt="Step 1 - Bill To"
                            class="block w-full h-auto max-h-[620px] object-contain"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                
                  <!-- Step 03 (highlighted) -->
                  <div class="grid grid-cols-1 md:grid-cols-[1fr_660px] gap-6 items-center">
                    <div class="rounded-2xl border border-violet-400/30 bg-violet-500/10 p-5">
                      <div class="text-cyan-200 font-semibold"><span class="mr-2">03</span>Map Price</div>
                      <div class="text-white font-semibold mt-2">Choose your “Price” column</div>
                      <div class="text-slate-200 text-sm mt-2">
                        Rate, Cost, Amount Paid, Dispatcher Price — whatever your sheet uses becomes the invoice total logic.
                      </div>
                    </div>
                
                    <div class="md:justify-self-end w-full max-w-[660px]">
                      <!-- thinner hero-like frame -->
                      <div class="glass shadow-soft rounded-3xl p-3">
                        <div class="rounded-2xl border border-slate-700/35 bg-slate-950/20 overflow-hidden">
                          <img
                            src="/assets/how-03.png"
                            alt="Step 1 - Bill To"
                            class="block w-full h-auto max-h-[620px] object-contain"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                
                  <!-- Step 04 -->
                  <div class="grid grid-cols-1 md:grid-cols-[1fr_660px] gap-6 items-center">
                    <div class="rounded-2xl border border-slate-700/50 bg-slate-950/25 p-5">
                      <div class="text-cyan-200 font-semibold"><span class="mr-2">04</span>Launch & Automate</div>
                      <div class="text-white font-semibold mt-2">Send & automate</div>
                      <div class="text-slate-300 text-sm mt-2">Turn on Stripe links, reminders, and recurring invoices when you’re ready.</div>
                    </div>
                
                    <div class="md:justify-self-end w-full max-w-[660px]">
                      <!-- thinner hero-like frame -->
                      <div class="glass shadow-soft rounded-3xl p-3">
                        <div class="rounded-2xl border border-slate-700/35 bg-slate-950/20 overflow-hidden">
                          <img
                            src="/assets/how-04.png"
                            alt="Step 1 - Bill To"
                            class="block w-full h-auto max-h-[620px] object-contain"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
      </section>

      <!-- Features v2 -->
    <section id="features" class="section">
      <div class="container">
        <div class="section-head">
          <span class="pill"><i class="fa-solid fa-gem"></i> Features</span>
          <h2>Built for individuals — ready for teams</h2>
          <p class="muted">
            A spreadsheet-first invoice builder, with automations and governance that feels enterprise — without the bloat.
          </p>
        </div>
    
        <div class="grid-3">
          <!-- Bucket 1 -->
          <div class="glass glass-pad">
            <div style="display:flex;gap:12px;align-items:flex-start;">
              <div class="icon-badge">
                <i class="fa-solid fa-file-invoice"></i>
              </div>
              <div>
                <h3 style="margin:0 0 6px;font-size:18px;">Invoice Builder</h3>
                <p class="muted" style="margin:0 0 12px;">
                  Keep your Excel/Sheets. Choose any column as Price. Toggle rows/columns before sending.
                </p>
              </div>
            </div>
    
            <ul class="feature-list">
              <li><i class="fa-solid fa-check"></i> Upload Excel / paste Sheets data</li>
              <li><i class="fa-solid fa-check"></i> Map columns → invoice layout instantly</li>
              <li><i class="fa-solid fa-check"></i> Hide columns/rows without changing source file</li>
              <li><i class="fa-solid fa-check"></i> Clean PDF output, branded and consistent</li>
            </ul>
    
            <div class="tag-row">
              <span class="tag">Spreadsheet-first</span>
              <span class="tag">Fast setup</span>
              <span class="tag">PDF-ready</span>
            </div>
          </div>
    
          <!-- Bucket 2 -->
          <div class="glass glass-pad">
            <div style="display:flex;gap:12px;align-items:flex-start;">
              <div class="icon-badge">
                <i class="fa-solid fa-robot"></i>
              </div>
              <div>
                <h3 style="margin:0 0 6px;font-size:18px;">Automation</h3>
                <p class="muted" style="margin:0 0 12px;">
                  Send invoices, follow-ups, and recurring renewals automatically — with optional Stripe payments.
                </p>
              </div>
            </div>
    
            <ul class="feature-list">
              <li><i class="fa-solid fa-check"></i> Email cadences (before / due / after)</li>
              <li><i class="fa-solid fa-check"></i> Recurring invoices (daily / monthly / custom)</li>
              <li><i class="fa-solid fa-check"></i> “Pay Now” link and status updates</li>
              <li><i class="fa-solid fa-check"></i> Proof upload + payment method tracking</li>
            </ul>
    
            <div class="tag-row">
              <span class="tag">Cadences</span>
              <span class="tag">Recurring</span>
              <span class="tag">Stripe-ready</span>
            </div>
          </div>
    
          <!-- Bucket 3 -->
          <div class="glass glass-pad">
            <div style="display:flex;gap:12px;align-items:flex-start;">
              <div class="icon-badge">
                <i class="fa-solid fa-shield-halved"></i>
              </div>
              <div>
                <h3 style="margin:0 0 6px;font-size:18px;">Governance</h3>
                <p class="muted" style="margin:0 0 12px;">
                  Roles, permissions, and admin controls — so teams can operate safely and cleanly.
                </p>
              </div>
            </div>
    
            <ul class="feature-list">
              <li><i class="fa-solid fa-check"></i> Role-based access (Admin/Manager/Viewer)</li>
              <li><i class="fa-solid fa-check"></i> Permission toggles per feature/page</li>
              <li><i class="fa-solid fa-check"></i> Audit-friendly status history (paid/unpaid)</li>
              <li><i class="fa-solid fa-check"></i> Exports + team visibility controls</li>
            </ul>
    
            <div class="tag-row">
              <span class="tag">Roles</span>
              <span class="tag">Permissions</span>
              <span class="tag">Controls</span>
            </div>
          </div>
        </div>
    
        <!-- CTA strip (unique IDs) -->
        <div class="glass glass-pad" style="margin-top:18px;">
          <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;">
            <div>
              <div class="pill" style="display:inline-flex;gap:8px;align-items:center;">
                <i class="fa-solid fa-bolt"></i> Ready to try the workflow?
              </div>
              <div class="muted" style="margin-top:8px;">Open the real flow (upload → preview → send/pay).</div>
            </div>
    
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
              <a id="loginBtnFeatures" class="btn btn-ghost" href="login.php">
                <i class="fa-solid fa-right-to-bracket"></i> Login
              </a>
              <a id="ctaBtnFeatures" class="btn btn-primary" href="register.php">
                <i class="fa-solid fa-rocket"></i> Get Started
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

        <!-- Why DocuBills wins (comparison) -->
        <section class="mx-auto max-w-screen-2xl px-4 sm:px-8 lg:px-12 pb-20">
          <div class="glass rounded-3xl p-6 sm:p-8 shadow-soft">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
              <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-600/50 bg-white/5 text-slate-200 text-sm">
                  <i class="fa-solid fa-shield-halved text-cyan-300"></i>
                  <span class="font-semibold tracking-wide">Why it converts better</span>
                </div>
                <div class="mt-4 text-white font-extrabold text-3xl sm:text-4xl section-title">
                  Your sheet stays the source of truth.
                </div>
                <div class="text-slate-300 mt-3 max-w-2xl">
                  Most tools force you into templates. DocuBills lets you keep your spreadsheet workflow and generate invoices without the usual pain.
                </div>
              </div>
            </div>
        
            <div class="mt-8 grid lg:grid-cols-2 gap-4">
              <div class="rounded-3xl border border-slate-700/40 bg-slate-950/20 p-6">
                <div class="text-white font-semibold text-lg">Typical invoice tools</div>
                <ul class="mt-4 space-y-3 text-slate-300 text-sm">
                  <li class="flex gap-3"><i class="fa-solid fa-xmark text-rose-400 mt-0.5"></i> You reformat your sheet to match their template</li>
                  <li class="flex gap-3"><i class="fa-solid fa-xmark text-rose-400 mt-0.5"></i> Column mapping feels technical & slow</li>
                  <li class="flex gap-3"><i class="fa-solid fa-xmark text-rose-400 mt-0.5"></i> Removing extra rows/columns breaks your workflow</li>
                  <li class="flex gap-3"><i class="fa-solid fa-xmark text-rose-400 mt-0.5"></i> Follow-ups stay manual or require extra tools</li>
                </ul>
              </div>
        
              <div class="rounded-3xl border border-cyan-400/25 bg-cyan-400/5 p-6">
                <div class="text-white font-semibold text-lg">DocuBills</div>
                <ul class="mt-4 space-y-3 text-slate-200 text-sm">
                  <li class="flex gap-3"><i class="fa-solid fa-circle-check text-cyan-300 mt-0.5"></i> Upload Excel / paste Google Sheets URL</li>
                  <li class="flex gap-3"><i class="fa-solid fa-circle-check text-cyan-300 mt-0.5"></i> Pick any column as <strong>Price</strong> (no hard mapping)</li>
                  <li class="flex gap-3"><i class="fa-solid fa-circle-check text-cyan-300 mt-0.5"></i> Toggle off rows/columns before PDF export</li>
                  <li class="flex gap-3"><i class="fa-solid fa-circle-check text-cyan-300 mt-0.5"></i> Add recurring invoices + reminder cadence inside the app</li>
                </ul>
        
                <div class="mt-6 flex flex-col sm:flex-row gap-2">
                  <a href="register.php" class="flex-1 inline-flex items-center justify-center px-5 py-3 rounded-2xl cta-gradient text-white font-semibold transition ring-glow">
                    Start free <i class="fa-solid fa-arrow-right ml-2"></i>
                  </a>
                  <a href="#demo" class="flex-1 inline-flex items-center justify-center px-5 py-3 rounded-2xl border border-slate-600/60 text-slate-100 hover:text-white hover:border-slate-400 transition soft-ring">
                    View live demo
                  </a>
                </div>
        
                <div class="mt-3 text-slate-400 text-xs">No credit card required to try it.</div>
              </div>
            </div>
          </div>
        </section>
        
        <!-- Admin controls -->
        <section class="mx-auto max-w-screen-2xl px-4 sm:px-8 lg:px-12 pb-20">
          <div class="glass rounded-3xl p-6 sm:p-8 shadow-soft">
            <div class="grid lg:grid-cols-2 gap-8 items-center">
              <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-600/50 bg-white/5 text-slate-200 text-sm">
                  <i class="fa-solid fa-user-gear text-cyan-300"></i>
                  <span class="font-semibold tracking-wide">Admin-grade control</span>
                </div>
        
                <div class="mt-4 text-white font-extrabold text-3xl sm:text-4xl section-title">
                  Control every feature with roles & permissions.
                </div>
        
                <div class="text-slate-300 mt-3">
                  Give teams exactly what they need: create invoices, approve, export, manage clients, change templates, or view-only access.
                  Perfect for agencies and teams.
                </div>
        
                <div class="mt-5 grid sm:grid-cols-2 gap-3">
                  <div class="rounded-2xl border border-slate-700/40 bg-slate-950/20 p-4">
                    <div class="text-white font-semibold"><i class="fa-solid fa-lock text-cyan-300 mr-2"></i>Least-privilege access</div>
                    <div class="text-slate-300 text-sm mt-1">Reduce mistakes by limiting what each user can do.</div>
                  </div>
                  <div class="rounded-2xl border border-slate-700/40 bg-slate-950/20 p-4">
                    <div class="text-white font-semibold"><i class="fa-solid fa-users text-cyan-300 mr-2"></i>Team-ready</div>
                    <div class="text-slate-300 text-sm mt-1">Managers, assistants, viewers — all supported.</div>
                  </div>
                </div>
        
                <div class="mt-6 flex gap-2">
                  <a href="register.php" class="px-5 py-3 rounded-2xl cta-gradient text-white font-semibold transition ring-glow">
                    Create an account <i class="fa-solid fa-arrow-right ml-2"></i>
                  </a>
                  <a href="#demo" class="px-5 py-3 rounded-2xl border border-slate-600/60 text-slate-100 hover:text-white hover:border-slate-400 transition soft-ring">
                    See it in action
                  </a>
                </div>
              </div>
        
              <div class="rounded-3xl border border-slate-700/40 bg-slate-950/20 p-6">
                <div class="text-slate-200 font-semibold">What admins can control</div>
                <div class="mt-4 grid gap-3 text-sm">
                  <div class="flex items-start gap-3"><i class="fa-solid fa-circle-check text-cyan-300 mt-0.5"></i><span class="text-slate-300"><span class="text-white font-semibold">Invoice access:</span> create, edit, export, delete, restore</span></div>
                  <div class="flex items-start gap-3"><i class="fa-solid fa-circle-check text-cyan-300 mt-0.5"></i><span class="text-slate-300"><span class="text-white font-semibold">Clients:</span> view-only vs full management</span></div>
                  <div class="flex items-start gap-3"><i class="fa-solid fa-circle-check text-cyan-300 mt-0.5"></i><span class="text-slate-300"><span class="text-white font-semibold">Email templates:</span> edit content + assign notification types</span></div>
                  <div class="flex items-start gap-3"><i class="fa-solid fa-circle-check text-cyan-300 mt-0.5"></i><span class="text-slate-300"><span class="text-white font-semibold">Payments:</span> bank details, Stripe toggles, currencies</span></div>
                  <div class="flex items-start gap-3"><i class="fa-solid fa-circle-check text-cyan-300 mt-0.5"></i><span class="text-slate-300"><span class="text-white font-semibold">Cadences:</span> reminders before/after due date</span></div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Admin & Security -->
        <section id="security" class="mx-auto max-w-screen-2xl px-4 sm:px-8 lg:px-12 pb-20">
          <div class="glass rounded-3xl p-6 sm:p-8 shadow-soft">
            <div class="section-kicker">Admin & Security</div>
            <div class="mt-2 section-title text-3xl sm:text-4xl">Control every feature — like an enterprise app</div>
            <div class="section-sub mt-3 max-w-3xl">
              DocuBills isn’t just an invoice generator. It’s a controlled workflow: admins can enable/disable features,
              assign roles, and manage granular permissions across the app.
            </div>
        
            <div class="mt-8 grid lg:grid-cols-3 gap-4">
              <div class="rounded-3xl border border-slate-700/50 bg-slate-950/25 p-6">
                <div class="text-white font-semibold flex items-center gap-2">
                  <i class="fa-solid fa-user-shield text-cyan-300"></i> Roles & Permissions
                </div>
                <div class="text-slate-300 text-sm mt-2">
                  Assign roles and fine-grained permissions (who can create invoices, edit templates, manage settings, etc.).
                </div>
              </div>
        
              <div class="rounded-3xl border border-slate-700/50 bg-slate-950/25 p-6">
                <div class="text-white font-semibold flex items-center gap-2">
                  <i class="fa-solid fa-envelope-open-text text-cyan-300"></i> Controlled Reminders
                </div>
                <div class="text-slate-300 text-sm mt-2">
                  Configure email cadence timing and content inside the app — consistent client communication, less manual chasing.
                </div>
              </div>
        
              <div class="rounded-3xl border border-slate-700/50 bg-slate-950/25 p-6">
                <div class="text-white font-semibold flex items-center gap-2">
                  <i class="fa-solid fa-shield-halved text-cyan-300"></i> Workflow Guardrails
                </div>
                <div class="text-slate-300 text-sm mt-2">
                  Keep outputs clean: choose price logic, hide irrelevant columns/rows, preview edits, currency selection, and banking details per invoice.
                </div>
              </div>
            </div>
        
            <div class="mt-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div class="text-slate-300 text-sm">
                Want the full workflow + automation? Start free and upgrade only if/when you need premium features.
              </div>
              <div class="flex gap-2">
                <a id="loginBtnSecurity" href="login.php"
                   class="px-4 py-2 rounded-2xl border border-slate-600/60 text-slate-100 hover:text-white hover:border-slate-400 transition">
                  Login
                </a>
                <a id="ctaBtnSecurity" href="register.php"
                   class="px-5 py-2 rounded-2xl cta-gradient text-white font-semibold transition ring-glow">
                  Start Free <i class="fa-solid fa-arrow-right ml-2"></i>
                </a>
              </div>
            </div>
          </div>
        </section>
        
        <!-- Pricing -->
        <section id="pricing" class="mx-auto max-w-screen-2xl px-4 sm:px-8 lg:px-12 pb-20">
          <div class="glass rounded-3xl p-6 sm:p-8 shadow-soft text-center">
            <div class="section-kicker">Pricing</div>
            <div class="mt-2 section-title text-3xl sm:text-4xl">Start free. Upgrade when you want automation.</div>
            <div class="section-sub mt-3 max-w-2xl mx-auto">
              Perfect for spreadsheet-first teams. Keep your workflow — just invoice faster and follow up automatically.
            </div>
          </div>
        
          <div class="mt-8 grid lg:grid-cols-3 gap-4">
            <div class="glass rounded-3xl p-6 shadow-soft text-left">
              <div class="text-white font-semibold text-lg">Free</div>
              <div class="text-slate-300 text-sm mt-1">For trying the workflow</div>
              <ul class="mt-4 text-slate-200 text-sm space-y-2">
                <li><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>3-step invoice creation</li>
                <li><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>Choose any Price column</li>
                <li><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>Hide columns/rows before PDF</li>
              </ul>
              <a href="register.php" class="mt-5 inline-flex w-full justify-center items-center px-4 py-2 rounded-2xl border border-slate-600/60 text-slate-100 hover:text-white hover:border-slate-400 transition">
                Start Free
              </a>
            </div>
        
            <div class="glass rounded-3xl p-6 shadow-soft text-left border border-cyan-400/30">
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-400/10 border border-cyan-400/30 text-cyan-200 text-xs font-bold">
                Most Popular
              </div>
              <div class="mt-3 text-white font-semibold text-lg">Pro</div>
              <div class="text-slate-300 text-sm mt-1">For teams that invoice weekly</div>
              <ul class="mt-4 text-slate-200 text-sm space-y-2">
                <li><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>Recurring invoices</li>
                <li><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>Email cadence reminders</li>
                <li><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>Saved banking + per-invoice edits</li>
                <li><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>Multi-currency selection</li>
              </ul>
              <a href="register.php" class="mt-5 inline-flex w-full justify-center items-center px-4 py-2 rounded-2xl cta-gradient text-white font-semibold transition ring-glow">
                Start Pro Trial <i class="fa-solid fa-arrow-right ml-2"></i>
              </a>
            </div>
        
            <div class="glass rounded-3xl p-6 shadow-soft text-left">
              <div class="text-white font-semibold text-lg">Business</div>
              <div class="text-slate-300 text-sm mt-1">Admin control + governance</div>
              <ul class="mt-4 text-slate-200 text-sm space-y-2">
                <li><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>Roles + permissions matrix</li>
                <li><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>Feature-level admin control</li>
                <li><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>Stripe Pay Now + status tracking</li>
              </ul>
              <a href="register.php" class="mt-5 inline-flex w-full justify-center items-center px-4 py-2 rounded-2xl border border-slate-600/60 text-slate-100 hover:text-white hover:border-slate-400 transition">
                Start Business
              </a>
            </div>
          </div>
        </section>

        <!-- FAQ (Premium Accordion) -->
        <section id="faq" class="mx-auto max-w-screen-2xl px-4 sm:px-8 lg:px-12 pb-20">
          <div class="glass rounded-3xl p-6 sm:p-8 shadow-soft text-center">
            <div class="section-kicker">FAQ</div>
            <div class="mt-2 section-title text-3xl sm:text-4xl">Frequently Asked Questions</div>
            <div class="section-sub mt-3 max-w-2xl mx-auto">
              Quick answers before you start.
            </div>
          </div>
        
          <div class="mt-8 space-y-3">
            <!-- item -->
            <div class="glass rounded-3xl p-5 shadow-soft">
              <button class="faq-item w-full flex items-center justify-between text-left"
                      aria-expanded="false"
                      type="button">
                <div class="text-white font-semibold">Is DocuBills really free?</div>
                <i class="fa-solid fa-chevron-down faq-chevron transition-transform duration-200 text-slate-300"></i>
              </button>
              <div class="faq-panel hidden mt-3 text-slate-300 text-sm">
                Yes. You can create invoices for free. Premium features (automation, cadences, advanced controls) are optional.
              </div>
            </div>
        
            <div class="glass rounded-3xl p-5 shadow-soft">
              <button class="faq-item w-full flex items-center justify-between text-left" aria-expanded="false" type="button">
                <div class="text-white font-semibold">What makes it different from other generators?</div>
                <i class="fa-solid fa-chevron-down faq-chevron transition-transform duration-200 text-slate-300"></i>
              </button>
              <div class="faq-panel hidden mt-3 text-slate-300 text-sm">
                You don’t rebuild your spreadsheet to fit a template. You upload your sheet and simply choose the Price column — no mapping headache.
              </div>
            </div>
        
            <div class="glass rounded-3xl p-5 shadow-soft">
              <button class="faq-item w-full flex items-center justify-between text-left" aria-expanded="false" type="button">
                <div class="text-white font-semibold">Can I hide columns/rows before sending?</div>
                <i class="fa-solid fa-chevron-down faq-chevron transition-transform duration-200 text-slate-300"></i>
              </button>
              <div class="faq-panel hidden mt-3 text-slate-300 text-sm">
                Yes. Deselect any columns/rows you don’t want printed — the PDF invoice stays clean without changing your original file.
              </div>
            </div>
        
            <div class="glass rounded-3xl p-5 shadow-soft">
              <button class="faq-item w-full flex items-center justify-between text-left" aria-expanded="false" type="button">
                <div class="text-white font-semibold">Does it support recurring invoices and reminders?</div>
                <i class="fa-solid fa-chevron-down faq-chevron transition-transform duration-200 text-slate-300"></i>
              </button>
              <div class="faq-panel hidden mt-3 text-slate-300 text-sm">
                Yes. You can enable recurring invoices from the preview and set your email cadence reminders inside the app.
              </div>
            </div>
          </div>
        </section>

      <!-- Footer -->
      <footer class="mx-auto max-w-screen-2xl px-4 sm:px-8 lg:px-12 pb-10">
        <div class="glass rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
          <div class="text-slate-300 text-sm">
            © <span id="yr"></span> DocuBills. All rights reserved.
          </div>
          <div class="flex gap-3 text-slate-300 text-sm">
            <a href="#how" class="hover:text-white">How it works</a>
            <a href="#features" class="hover:text-white">Features</a>
            <a href="#demo" class="hover:text-white">Demo</a>
          </div>
        </div>
      </footer>
    </div>
  </div>

  <!-- Signup Modal (Phase 3 Hook) -->
  <div id="modalBackdrop" class="modal-backdrop fixed inset-0 z-50 items-center justify-center bg-black/60 p-4">
    <div class="glass shadow-soft rounded-3xl max-w-lg w-full p-6 sm:p-7 fade-in">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="text-white font-extrabold text-2xl">
            To finalize your first invoice, sign up for free!
          </div>
          <div class="text-slate-300 mt-2 text-sm leading-relaxed">
            You’re one step away from generating a PDF, adding Stripe “Pay Now”, and enabling automated reminders.
          </div>
        </div>
        <button id="modalClose" class="h-10 w-10 rounded-xl border border-slate-600/60 text-slate-200 hover:text-white hover:border-slate-400 transition">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="mt-5 grid gap-3">
        <div class="rounded-2xl border border-violet-400/30 bg-violet-500/10 p-4">
          <div class="text-cyan-200 font-semibold"><i class="fa-solid fa-star mr-2"></i>Unlocked after signup</div>
          <ul class="mt-2 text-slate-200 text-sm space-y-1">
            <li><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>Generate invoice PDF</li>
            <li><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>Stripe payment links</li>
            <li><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>Email cadences + reminders</li>
            <li><i class="fa-solid fa-circle-check text-cyan-300 mr-2"></i>Recurring invoices</li>
          </ul>
        </div>

        <div class="flex flex-col sm:flex-row gap-2">
          <a id="modalGetStarted" href="register.php"
             class="flex-1 inline-flex items-center justify-center px-5 py-3 rounded-2xl cta-gradient text-white font-semibold transition ring-glow">
            Sign up free <i class="fa-solid fa-arrow-right ml-2"></i>
          </a>
          <a id="modalLogin" href="login.php"
             class="flex-1 inline-flex items-center justify-center px-5 py-3 rounded-2xl border border-slate-600/60 text-slate-100 hover:text-white hover:border-slate-400 transition">
            I already have an account
          </a>
        </div>

        <div class="text-slate-400 text-xs">
          No credit card required to start.
        </div>
      </div>
    </div>
  </div>

  <script>
    // Link buttons to constants (safe with missing elements)
    const setHref = (id, url) => {
      const a = document.getElementById(id);
      if (a) a.href = url;
    };
    
    // Login links
    ["loginBtnTop", "loginBtnFeatures", "loginBtnSecurity", "modalLogin"].forEach(id => {
      setHref(id, LOGIN_URL);
    });
    
    // Start / Register links
    [
      "ctaBtnTop",
      "ctaBtnHero",
      "ctaBtnHero2",
      "ctaBtnFeatures",
      "ctaBtnSecurity",
      "ctaBtnDemoBottom",
      "modalGetStarted"
    ].forEach(id => {
      setHref(id, GET_STARTED_URL);
    });

      // ---------------------------
      // Mini Demo: Step 1 + Step 2
      // ---------------------------
    
      const el = (id) => document.getElementById(id);
      
      // ---------------------------
      // Drawer: demo open / close
      // ---------------------------
        const demoDrawerBtn   = el("demoDrawerBtn");
        const demoDrawerPanel = el("demoDrawerPanel");
        const demoDrawerIcon  = el("demoDrawerIcon");
        const demoDrawerLabel = el("demoDrawerLabel");
        
        function setDemoDrawer(open) {
          if (!demoDrawerBtn || !demoDrawerPanel) return;
        
          demoDrawerPanel.classList.toggle("open", open);
          demoDrawerBtn.setAttribute("aria-expanded", open ? "true" : "false");
        
          if (demoDrawerIcon) demoDrawerIcon.classList.toggle("rotate-180", open);
          if (demoDrawerLabel) demoDrawerLabel.textContent = open ? "Close" : "Open";
        }
        
        demoDrawerBtn?.addEventListener("click", () => {
          setDemoDrawer(!demoDrawerPanel.classList.contains("open"));
        });
        
        // Open drawer automatically if user navigates to #demo
        document.querySelectorAll('a[href="#demo"]').forEach(a => {
          a.addEventListener("click", () => setDemoDrawer(true));
        });
        
        if (location.hash === "#demo") {
          setDemoDrawer(true);
        }

    
      // Tabs and phases (these already exist in your landing file)
      const tab1 = el("tab1"), tab2 = el("tab2"), tab3 = el("tab3");
      const phase1 = el("phase1"), phase2 = el("phase2"), phase3 = el("phase3");
    
      const demoDefaults = JSON.parse(JSON.stringify({
          billTo: {
            company: "Odyssey House",
            rep: "Accounts Payable",
            address: "9913 93 Ave, Grande Prairie, AB, Canada",
            phone: "+1 (780) 532-2672",
            email: "billing@odysseyhouse.ca"
          },
          priceMode: "column",
          priceColumn: "Amount Paid"
        }));

      // Simple demo state
      const demoState = {
      phaseReached: 1,
      billTo: {
        company: "Odyssey House",
        rep: "Accounts Payable",
        address: "9913 93 Ave, Grande Prairie, AB, Canada",
        phone: "+1 (780) 532-2672",
        email: "billing@odysseyhouse.ca"
      },
      headers: ["Booking ID","Customer","Car Type","Pick-up","Drop-off","Booking Time","Est.Fare","Amount Paid","Payment Method","Assigned Driver","Status"],
      items: [
        {
          "Booking ID":"2183",
          "Customer":"Odyssey House\n+17805322672",
          "Car Type":"Economy",
          "Pick-up":"9913 93 Ave, Grande Prairie, AB T8V 0J6, Canada",
          "Drop-off":"10026 103 Ave, Grande Prairie, AB T8V 1B8, Canada",
          "Booking Time":"Monday, Apr 28, 2025 15:37:22",
          "Est.Fare":"$8.69",
          "Amount Paid":"$10.20",
          "Payment Method":"WALLET",
          "Assigned Driver":"Tanya Cadger",
          "Status":"Completed"
        },
        {
          "Booking ID":"1344",
          "Customer":"Odyssey House\n+17805381332",
          "Car Type":"Economy",
          "Pick-up":"Grande Prairie Regional Hospital (GPRH), 110 Street, Grande Prairie, AB, Canada",
          "Drop-off":"9913 93 Ave, Grande Prairie, AB, Canada",
          "Booking Time":"Sunday, Apr 13, 2025 20:17:06",
          "Est.Fare":"$20.14",
          "Amount Paid":"$0.00",
          "Payment Method":"WALLET",
          "Assigned Driver":"Tricia Blackmore",
          "Status":"Driver Cancelled\nReason: Rider was not available"
        },
        {
          "Booking ID":"1042",
          "Customer":"Odyssey House\n+17805381332",
          "Car Type":"SUV",
          "Pick-up":"10118 101 Ave, Grande Prairie, AB T8V 0Y4, Canada",
          "Drop-off":"9913 93 Avenue, Grande Prairie, AB, Canada",
          "Booking Time":"Tuesday, Apr 8, 2025 14:22:59",
          "Est.Fare":"$8.92",
          "Amount Paid":"$9.34",
          "Payment Method":"WALLET",
          "Assigned Driver":"Marina Urich",
          "Status":"Completed"
        }
      ],
      priceMode: "column",
      priceColumn: "Amount Paid",
      includeCols: [0,1,2,3,4,5,6,7,8,9,10],
      currencyCode: "USD"
    };
    
      function setActiveTabUI(n) {
        [tab1, tab2, tab3].forEach(t => {
          t.classList.remove("demo-tab-active");
          t.classList.add("border-slate-700/50", "text-slate-300");
        });
        const active = n === 1 ? tab1 : n === 2 ? tab2 : tab3;
        active.classList.add("demo-tab-active");
        active.classList.remove("text-slate-300");
        active.classList.add("text-slate-100");
    
        tab1.disabled = false;
        tab2.disabled = demoState.phaseReached < 2;
        tab3.disabled = demoState.phaseReached < 3;
    
        tab2.classList.toggle("opacity-60", tab2.disabled);
        tab3.classList.toggle("opacity-60", tab3.disabled);
      }
    
      function showPhase(n) {
        demoState.phaseReached = Math.max(demoState.phaseReached, n);
        phase1.classList.toggle("hidden", n !== 1);
        phase2.classList.toggle("hidden", n !== 2);
        phase3.classList.toggle("hidden", n !== 3);
        setActiveTabUI(n);
    
        if (n === 2) {
          renderStep2HeadersUI();
          initStep2Behavior();
        }
        if (n === 3) {
          renderPhase3UI();
          initPhase3Behavior();
        }
      }
    
      // Tabs click
      tab1.addEventListener("click", () => showPhase(1));
      tab2.addEventListener("click", () => { if (!tab2.disabled) showPhase(2); });
      tab3.addEventListener("click", () => { if (!tab3.disabled) showPhase(3); });
    
      // ---------------------------
      // Step 1: create-invoice demo
      // ---------------------------
      const step1Form = el("demoInvoiceFormStep1");
    
      function clearFileError() {
        const box = el("demo_fileError");
        if (!box) return;
        box.textContent = "";
        box.style.display = "none";
      }
    
      function showFileError(msg) {
        const box = el("demo_fileError");
        if (!box) return alert(msg);
        box.textContent = msg;
        box.style.display = "block";
      }
    
      function isValidExcelFile(file) {
        if (!file) return false;
        const ext = (file.name || "").split(".").pop().toLowerCase();
        return ["xls", "xlsx"].includes(ext);
      }
    
      // Step 1: source toggle
      function syncStep1SourceUI() {
        const source = step1Form.querySelector('input[name="invoice_source"]:checked')?.value || "google";
    
        const google = el("demo_google_section");
        const upload = el("demo_upload_section");
        const custom = el("demo_custom_table_builder");
    
        if (google) google.style.display = (source === "google") ? "block" : "none";
        if (upload) upload.style.display = (source === "upload") ? "block" : "none";
        if (custom) custom.style.display = (source === "custom") ? "block" : "none";
      }
    
      step1Form.querySelectorAll('input[name="invoice_source"]').forEach(r => {
        r.addEventListener("change", () => {
          clearFileError();
          syncStep1SourceUI();
        });
      });
      syncStep1SourceUI();
      
    // ✅ Demo prefills (Bill To + locked fields)
        document.addEventListener("DOMContentLoaded", () => {
      if (el("demo_bill_to_name"))    el("demo_bill_to_name").value    = demoState.billTo.company || "";
      if (el("demo_bill_to_rep"))     el("demo_bill_to_rep").value     = demoState.billTo.rep || "";
      if (el("demo_bill_to_address")) el("demo_bill_to_address").value = demoState.billTo.address || "";
      if (el("demo_bill_to_phone"))   el("demo_bill_to_phone").value   = demoState.billTo.phone || "";
      if (el("demo_bill_to_email"))   el("demo_bill_to_email").value   = demoState.billTo.email || "";
      if (el("demo_google_sheet_url")) el("demo_google_sheet_url").value = "🔒 Demo Locked";
    });

// Upload interactions (DEMO LOCKED)
const uploadArea = el("demoUploadArea");
const fileInput = el("demo_excel_file");
const fileNameDisplay = el("demo_fileName");

if (fileInput) {
  fileInput.disabled = true;
  fileInput.value = "";
}
if (fileNameDisplay) fileNameDisplay.style.display = "none";

if (uploadArea) {
  const lockedMsg = "🔒 Demo Locked: File upload is disabled in the mini-demo.";

  uploadArea.addEventListener("click", (e) => {
    e.preventDefault();
    showFileError(lockedMsg);
  });

  uploadArea.addEventListener("dragover", (e) => e.preventDefault());
  uploadArea.addEventListener("drop", (e) => {
    e.preventDefault();
    showFileError(lockedMsg);
  });
}

// ---------------------------
// Custom Table Builder (safe)
// ---------------------------
const genBtn = el("demo_generate_custom_table");
const colCount = el("demo_custom_col_count");
const customContainer = el("demo_custom_table_container");

function buildCustomTable(cols) {
  if (!customContainer) return;

  const table = document.createElement("table");

  // head
  const thead = document.createElement("thead");
  const headRow = document.createElement("tr");
  for (let i = 1; i <= cols; i++) {
    const th = document.createElement("th");
    th.contentEditable = "true";
    th.textContent = `Column ${i}`;
    headRow.appendChild(th);
  }
  thead.appendChild(headRow);
  table.appendChild(thead);

  // one editable row
  const tbody = document.createElement("tbody");
  const tr = document.createElement("tr");
  for (let i = 1; i <= cols; i++) {
    const td = document.createElement("td");
    td.contentEditable = "true";
    td.textContent = "";
    tr.appendChild(td);
  }
  tbody.appendChild(tr);
  table.appendChild(tbody);

  customContainer.innerHTML = "";
  customContainer.appendChild(table);

  const hidden = el("demo_custom_table_html");
  if (hidden) hidden.value = table.outerHTML;
}

if (genBtn && colCount) {
  genBtn.addEventListener("click", () => {
    const c = Math.max(1, Math.min(7, parseInt(colCount.value || "1", 10)));
    buildCustomTable(c);
  });
}

      // Step 1 submit → go Step 2
      step1Form.addEventListener("submit", (e) => {
        e.preventDefault();
        clearFileError();
    
        const source = step1Form.querySelector('input[name="invoice_source"]:checked')?.value || "google";
    
        // Save Bill To
        demoState.billTo.company = (el("demo_bill_to_name")?.value || "").trim();
        demoState.billTo.rep     = (el("demo_bill_to_rep")?.value || "").trim();
        demoState.billTo.address = (el("demo_bill_to_address")?.value || "").trim();
        demoState.billTo.phone   = (el("demo_bill_to_phone")?.value || "").trim();
        demoState.billTo.email   = (el("demo_bill_to_email")?.value || "").trim();
    
        // Validate required fields like your page
        if (!demoState.billTo.company || !demoState.billTo.email) {
          showFileError("Please fill in Company Name and Email before continuing.");
          return;
        }
    
        // Upload is demo-locked: allow continuing with preloaded sample data
        if (source === "upload") {
          // no-op
        }

    
        showPhase(2);
      });
    
      // ---------------------------
      // Step 2: price-select demo
      // ---------------------------
      function renderStep2HeadersUI() {
      const priceColsWrap = el("demoPriceColumns");
      const includeWrap   = el("demoColumnPicker");
      if (!priceColsWrap || !includeWrap) return;
    
      priceColsWrap.innerHTML = "";
      includeWrap.innerHTML   = "";
    
      const defaultInclude = demoState.headers.map((_, i) => i);
      const includeSet = new Set(
        (demoState.includeCols && demoState.includeCols.length)
          ? demoState.includeCols
          : defaultInclude
      );
    
      demoState.headers.forEach((col, idx) => {
        // Price column radio (app-like row)
        const radioLabel = document.createElement("label");
        radioLabel.className = "option-row";
        radioLabel.innerHTML = `
          <input type="radio" name="price_column" value="${escapeHtml(col)}" ${demoState.priceColumn === col ? "checked" : ""}>
          <span>Column: <strong>${escapeHtml(col)}</strong></span>
        `;
        priceColsWrap.appendChild(radioLabel);
    
        // Include columns checkbox (app-like row)
        const chkLabel = document.createElement("label");
        chkLabel.className = "option-row";
        chkLabel.innerHTML = `
          <input type="checkbox" name="include_cols[]" value="${idx}" ${includeSet.has(idx) ? "checked" : ""}>
          <span>${escapeHtml(col)}</span>
        `;
        includeWrap.appendChild(chkLabel);
      });
    }
    
      function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, m => ({ "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;" }[m]));
      }
    
      function parseAmountJS(value) {
        if (value === null || value === undefined || value === "") return 0;
        if (typeof value === "number") return value;
        let str = String(value);
    
        // remove spaces + NBSP
        str = str.replace(/\u00A0/g, "").replace(/\s/g, "");
    
        // comma+dot => comma thousand
        if (str.includes(",") && str.includes(".")) {
          str = str.replace(/,/g, "");
        } else {
          str = str.replace(/,/g, ".");
        }
        str = str.replace(/[^0-9.\-]/g, "");
        const n = Number(str);
        return Number.isFinite(n) ? n : 0;
      }
    
    function initStep2Behavior() {
      const autoOption   = el("demoAutoPriceOption");
      const manualOption = el("demoManualPriceOption");
      const form         = el("demoPriceSelectForm");
      if (!autoOption || !manualOption || !form) return;
    
      const errBox  = el("demo_step2_error");
      const errText = el("demo_step2_error_text");
    
      const setModeUI = (mode) => {
        const isManual = (mode === "manual");
    
        autoOption.classList.toggle("active", !isManual);
        manualOption.classList.toggle("active", isManual);
    
        // enable/disable price column radios
        form.querySelectorAll('input[name="price_column"]').forEach(r => {
          r.required = !isManual;
          r.disabled = isManual;
        });
      };
    
    const enforceColumnLimit = () => {
      const max = 15;
      const lockedIdx = form.dataset.lockedPriceIdx;
    
      const boxes = Array.from(form.querySelectorAll('#demoColumnPicker input[type="checkbox"]'));
      if (!boxes.length) return;
    
      // If somehow more than max are checked, uncheck extras (but never unlock the locked price col)
      let checked = boxes.filter(cb => cb.checked);
      if (checked.length > max) {
        let over = checked.length - max;
        for (let i = checked.length - 1; i >= 0 && over > 0; i--) {
          if (String(checked[i].value) === String(lockedIdx)) continue;
          checked[i].checked = false;
          over--;
        }
      }
    
      checked = boxes.filter(cb => cb.checked);
    
      // Disable unchecked boxes once we hit max (but keep price column locked)
      boxes.forEach(cb => {
        if (String(cb.value) === String(lockedIdx)) {
          cb.checked = true;
          cb.disabled = true;
          return;
        }
        cb.disabled = (!cb.checked && checked.length >= max);
      });
    };

      const lockPriceInclude = () => {
      const mode = form.querySelector('input[name="price_mode"]:checked')?.value || "column";
    
      // unlock previously locked checkbox (if any)
      const prevIdx = form.dataset.lockedPriceIdx;
      if (prevIdx !== undefined && prevIdx !== null && prevIdx !== "") {
        const prevCb = form.querySelector(`#demoColumnPicker input[type="checkbox"][value="${prevIdx}"]`);
        if (prevCb) prevCb.disabled = false;
      }
      form.dataset.lockedPriceIdx = "";
    
      // only lock in Automatic Pricing mode
      if (mode !== "column") {
        enforceColumnLimit();
        return;
      }
    
      const selectedCol = form.querySelector('input[name="price_column"]:checked')?.value || "";
      const priceIdx = demoState.headers.indexOf(selectedCol);
    
      if (priceIdx < 0) {
        enforceColumnLimit();
        return;
      }
    
      const cb = form.querySelector(`#demoColumnPicker input[type="checkbox"][value="${priceIdx}"]`);
      if (cb) {
        cb.checked = true;
        cb.disabled = true; // 🔒 required
        form.dataset.lockedPriceIdx = String(priceIdx);
      }
    
      enforceColumnLimit();
    };
    
      // Bind once (safe even if you re-enter Phase 2)
      if (form.dataset.bound !== "1") {
        form.dataset.bound = "1";
    
        autoOption.addEventListener("click", (e) => {
          if (e.target.tagName !== "INPUT") {
            const r = form.querySelector('input[name="price_mode"][value="column"]');
            if (r) r.checked = true;
            setModeUI("column");
          }
        });
    
        manualOption.addEventListener("click", (e) => {
          if (e.target.tagName !== "INPUT") {
            const r = form.querySelector('input[name="price_mode"][value="manual"]');
            if (r) r.checked = true;
            setModeUI("manual");
          }
        });
    
        form.addEventListener("change", (e) => {
          if (e.target && e.target.name === "price_mode") {
            setModeUI(e.target.value);
            lockPriceInclude();
          }
        
          // when user changes price column radio, re-lock required include checkbox
          if (e.target && e.target.name === "price_column") {
            lockPriceInclude();
          }
        
          // when user checks/unchecks include columns, re-apply lock + limit
          if (e.target && e.target.matches('#demoColumnPicker input[type="checkbox"]')) {
            lockPriceInclude();
          }
        });
    
        form.addEventListener("submit", (e) => {
          e.preventDefault();
    
          if (errBox) errBox.style.display = "none";
          if (errText) errText.textContent = "";
    
          const selectedMode = form.querySelector('input[name="price_mode"]:checked')?.value || "column";
          const selectedCol  = form.querySelector('input[name="price_column"]:checked')?.value || null;
    
          if (selectedMode === "column" && !selectedCol) {
            if (errBox && errText) {
              errText.textContent = "Please select a price column for automatic pricing.";
              errBox.style.display = "block";
            } else {
              alert("Please select a price column for automatic pricing.");
            }
            autoOption.scrollIntoView({ behavior:"smooth", block:"center" });
            return;
          }
    
          if (selectedMode === "column" && selectedCol) {
            let sum = 0;
            demoState.items.forEach(row => sum += parseAmountJS(row[selectedCol] ?? ""));
            if (sum <= 0) {
              if (errBox && errText) {
                errText.textContent =
                  'The selected price column did not produce a valid total amount. Please choose a different column (for example, "Sub Total") or verify your data before continuing.';
                errBox.style.display = "block";
              }
              return;
            }
          }
    
          demoState.priceMode = selectedMode;
          demoState.priceColumn = (selectedMode === "column") ? selectedCol : null;
    
          demoState.includeCols = Array.from(form.querySelectorAll('input[name="include_cols[]"]:checked'))
            .map(i => parseInt(i.value, 10))
            .filter(n => Number.isFinite(n));
    
          showPhase(3);
        });
      }
    
      // Sync UI every time you enter Phase 2
      const desiredMode = demoState.priceMode || "column";
      const modeRadio = form.querySelector(`input[name="price_mode"][value="${desiredMode}"]`);
      if (modeRadio) modeRadio.checked = true;
    
      form.querySelectorAll('input[name="price_column"]').forEach(r => {
        r.checked = (r.value === demoState.priceColumn);
      });
    
      setModeUI(desiredMode);
      lockPriceInclude();
    }
    
    // ---------------------------
    // Phase 3: generate_invoice demo
    // ---------------------------
    const STRIPE_MAX_TOTAL = 999999.99;
    const currencyMap = { CAD:'CA$', USD:'US$', AUD:'A$', GBP:'£', EUR:'€', PKR:'₨', SAR:'﷼', AED:'د.إ' };
    
    function parseAmountJS(value) {
      if (value === null || value === undefined || value === "") return 0;
      if (typeof value === "number") return value;
      let str = String(value).replace(/\u00A0/g, "").replace(/\s/g, "");
      if (str.includes(",") && str.includes(".")) str = str.replace(/,/g, "");
      else str = str.replace(/,/g, ".");
      str = str.replace(/[^0-9.\-]/g, "");
      const n = Number(str);
      return Number.isFinite(n) ? n : 0;
    }
    
    function renderPhase3UI() {
      // Bill-to block
      const b = document.getElementById("demoBillToBlock");
      if (b) {
        const parts = [];
        if (demoState.billTo.company) parts.push(demoState.billTo.company);
        if (demoState.billTo.rep)     parts.push(demoState.billTo.rep);
        if (demoState.billTo.address) parts.push(demoState.billTo.address);
        if (demoState.billTo.phone)   parts.push(demoState.billTo.phone);
        if (demoState.billTo.email)   parts.push(demoState.billTo.email);
        b.innerHTML = parts.map(x => `<div>${escapeHtml(x)}</div>`).join("");
      }
    
      const tableHead = document.getElementById("demoInvoiceHeadRow");
      const tableBody = document.getElementById("demoInvoiceBody");
      const colToggle = document.getElementById("demoColToggleList");
      if (!tableHead || !tableBody || !colToggle) return;
    
      // Which headers are included from Phase 2
      const includeIdx = (demoState.includeCols && demoState.includeCols.length)
        ? demoState.includeCols.slice(0, 15)
        : demoState.headers.map((_, i) => i).slice(0, 15);
    
      const includedHeaders = includeIdx
        .map(i => demoState.headers[i])
        .filter(Boolean);
    
      const isManual = demoState.priceMode === "manual";
      const priceColKey = isManual ? null : demoState.priceColumn; // keep key stable (even if header text changes)
    
      // Currency setup (default CAD)
      demoState.currencyCode = demoState.currencyCode || "USD";
      demoState.currencyDisplay = currencyMap[demoState.currencyCode] || "$";
    
      // Build column toggle chips
      colToggle.innerHTML = "";
      includedHeaders.forEach((keyLabel, idx) => {
        const isPrice = (!isManual && priceColKey && keyLabel === priceColKey);
        const domColIndex = idx + 1; // because we always include row-checkbox col at index 0
    
        const wrap = document.createElement("label");
        wrap.className = "column-toggle-item" + (isPrice ? " price-column-label" : "");
        wrap.innerHTML = `
          <input type="checkbox"
                 class="demo-col-toggle"
                 data-col-idx="${domColIndex}"
                 ${isPrice ? 'data-price-col="1" checked disabled' : 'checked'}>
          <span class="demo-col-label-text">${escapeHtml(keyLabel)}</span>
          ${isPrice ? '<span class="required-pill">Required</span>' : ''}
        `;
        colToggle.appendChild(wrap);
      });
    
      // Build table head (editable headers like generate_invoice.php)
      tableHead.innerHTML = `
        <th style="width:34px;" contenteditable="false">
          <input type="checkbox" id="demoSelectAll" checked>
        </th>
        ${includedHeaders.map(h => `
          <th contenteditable="true" data-key="${escapeHtml(h)}">${escapeHtml(h)}</th>
        `).join("")}
      `;
    
      // Build table rows
      tableBody.innerHTML = "";
      demoState.items.forEach(rowObj => {
        const tr = document.createElement("tr");
        tr.className = "demo-data-row";
        tr.innerHTML = `
          <td><input type="checkbox" class="demo-rowCheckbox" checked></td>
          ${includedHeaders.map(h => {
            const raw = rowObj[h] ?? "";
            const isPrice = (!isManual && priceColKey && h === priceColKey);
            const v = isPrice ? (parseAmountJS(raw) ? parseAmountJS(raw).toFixed(2) : "") : String(raw);
            const cls = isPrice ? "amount editable-cell" : "editable-cell";
            return `<td class="${cls}" contenteditable="true">${escapeHtml(v)}</td>`;
          }).join("")}
        `;
        tableBody.appendChild(tr);
      });
    
      // Total section UI
      const right = document.getElementById("demoTotalRight");
      const leftNote = document.getElementById("demoTotalLeftNote");
    
      // ✅ Move Add Field under first column (left side), replacing the old tip text
      if (leftNote) {
        leftNote.innerHTML = `
          <button type="button" class="btn btn-secondary btn-sm" id="demoAddFieldBtn">
            <i class="fas fa-plus"></i> Add Field
          </button>
        `;
      }
    
      if (right) {
        if (isManual) {
          right.innerHTML = `
            <div class="manual-total-container">
              <label class="form-label">Invoice Amount:</label>
              <div style="display:flex; gap:10px; align-items:center;">
                <select id="demoCurrencyCode" class="form-control" style="width:110px;">
                  ${Object.keys(currencyMap).map(code => `
                    <option value="${code}" ${demoState.currencyCode === code ? "selected":""}>
                      ${escapeHtml(currencyMap[code])}
                    </option>`).join("")}
                </select>
                <input id="demoManualTotal" class="form-control" type="number" step="0.01" min="0" placeholder="Enter invoice total">
              </div>
            </div>
          `;
        } else {
          right.innerHTML = `
            <div class="total-display">
              <div style="font-weight:800;">Total Amount:</div>
              <select id="demoCurrencyCode" class="form-control" style="width:auto;min-width:70px;">
                ${Object.keys(currencyMap).map(code => `
                  <option value="${code}" ${demoState.currencyCode === code ? "selected":""}>
                    ${escapeHtml(currencyMap[code])}
                  </option>`).join("")}
              </select>
              <span id="demoTotalAmount">0.00</span>
            </div>
          `;
        }
      }
    
      // Dates
      const now = new Date();
      const pad = (n) => String(n).padStart(2,"0");
      const yyyy = now.getFullYear();
      const mm = pad(now.getMonth()+1);
      const dd = pad(now.getDate());
      const today = `${yyyy}-${mm}-${dd}`;
    
      const due = new Date(now);
      due.setDate(due.getDate() + 14);
      const dueStr = `${due.getFullYear()}-${pad(due.getMonth()+1)}-${pad(due.getDate())}`;
    
      const invDate = document.getElementById("demo_invoice_date");
      const invTime = document.getElementById("demo_invoice_time");
      const dueDate = document.getElementById("demo_due_date");
      if (invDate) invDate.value = today;
      if (invTime) invTime.value = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
      if (dueDate) dueDate.value = dueStr;
    
      // Reset stripe warning
      const warn = document.getElementById("demoStripeLimitWarning");
      if (warn) warn.classList.add("hidden");
    }
        
    function initPhase3Behavior() {
      if (demoState._phase3Initialized) return;
      demoState._phase3Initialized = true;
      
      // ✅ Company logo: try multiple candidates from /assets, then fallback to DB badge
        const logoImg = document.getElementById("demoCompanyLogoImg");
        const logoFallback = document.getElementById("demoCompanyLogoFallback");
        const brand = document.getElementById("demoCompanyBrand");
        const wordmark = document.getElementById("demoCompanyWordmark");
        
        if (logoImg && logoFallback && !logoImg.dataset.bound) {
          logoImg.dataset.bound = "1";
        
          const showImg = () => {
          logoImg.style.display = "block";
          logoFallback.style.display = "none";
          brand && brand.classList.add("has-logo");
          if (wordmark) wordmark.style.display = "none";
        };
        
        const showFallback = () => {
          logoImg.style.display = "none";
          logoFallback.style.display = "flex";
          brand && brand.classList.remove("has-logo");
          if (wordmark) wordmark.style.display = "block";
        };
        
          const candidates = (logoImg.dataset.candidates || "")
            .split(",")
            .map(s => s.trim())
            .filter(Boolean);
        
          const tryNext = (i) => {
            if (i >= candidates.length) return showFallback();
        
            const test = new Image();
            test.onload = () => {
              logoImg.src = candidates[i];
              showImg();
            };
            test.onerror = () => tryNext(i + 1);
            test.src = candidates[i];
          };
        
          tryNext(0);
        }
    
      const phase3Root = document.getElementById("miniDemo3");
      const table = document.getElementById("demoInvoiceTable");
      if (!table || !phase3Root) return;
    
      const isManualMode = () => demoState.priceMode === "manual";
    
      const setRowEnabled = (tr, enabled) => {
        tr.classList.toggle("row-disabled", !enabled);
      };
    
      const syncSelectAllState = () => {
        const selectAll = document.getElementById("demoSelectAll");
        if (!selectAll) return;
    
        const cbs = Array.from(document.querySelectorAll(".demo-rowCheckbox"));
        if (!cbs.length) {
          selectAll.checked = true;
          selectAll.indeterminate = false;
          return;
        }
    
        const checkedCount = cbs.filter(x => x.checked).length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < cbs.length;
        selectAll.checked = checkedCount === cbs.length;
      };
    
      const applyColumnVisibilityToRow = (tr) => {
        for (let i = 1; i < tr.cells.length; i++) {
          const toggle = document.querySelector(`.demo-col-toggle[data-col-idx="${i}"]`);
          tr.cells[i].style.display = (toggle && !toggle.checked) ? "none" : "";
        }
      };
    
      const refreshAllRowStates = () => {
        document.querySelectorAll("#demoInvoiceBody tr").forEach(tr => {
          const cb = tr.querySelector(".demo-rowCheckbox");
          setRowEnabled(tr, cb ? cb.checked : true);
          applyColumnVisibilityToRow(tr);
        });
        syncSelectAllState();
      };
    
      const updateTotal = () => {
        let total = 0;
    
        if (isManualMode()) {
          const manual = document.getElementById("demoManualTotal");
          total = parseFloat(manual?.value || "0") || 0;
        } else {
          const rows = Array.from(document.querySelectorAll("#demoInvoiceBody tr"));
          rows.forEach(r => {
            const cb = r.querySelector(".demo-rowCheckbox");
            if (cb && !cb.checked) return;
    
            const amt = r.querySelector(".amount");
            if (!amt) return;
    
            const v = parseAmountJS(amt.textContent);
            total += (Number.isFinite(v) ? v : 0);
          });
        }
    
        const totalEl = document.getElementById("demoTotalAmount");
        if (totalEl) totalEl.textContent = total.toFixed(2);
    
        checkStripeLimit(total);
      };
    
      const checkStripeLimit = (total) => {
        const warning = document.getElementById("demoStripeLimitWarning");
        const ack = document.getElementById("demoManualOnlyAck");
        const saveBtn = document.getElementById("demoSaveInvoiceBtn");
        const display = document.getElementById("demoStripeLimitDisplay");
    
        const prefix1 = document.getElementById("demoStripePrefix1");
        const prefix2 = document.getElementById("demoStripePrefix2");
        if (prefix1) prefix1.textContent = demoState.currencyDisplay;
        if (prefix2) prefix2.textContent = demoState.currencyDisplay;
    
        if (!warning || !saveBtn || !ack) return;
    
        if (total > STRIPE_MAX_TOTAL) {
          warning.classList.remove("hidden");
          if (display) display.textContent = total.toFixed(2);
    
          if (!ack.checked) {
            saveBtn.disabled = true;
            saveBtn.classList.add("btn-disabled-stripe");
          } else {
            saveBtn.disabled = false;
            saveBtn.classList.remove("btn-disabled-stripe");
          }
        } else {
          warning.classList.add("hidden");
          ack.checked = false;
          saveBtn.disabled = false;
          saveBtn.classList.remove("btn-disabled-stripe");
        }
      };
    
      // ✅ Editable header → also update the column-toggle chip label
      table.addEventListener("focusout", (e) => {
        const th = e.target.closest("th");
        if (!th) return;
        if (th.cellIndex === 0) return;
        if (th.parentElement?.id !== "demoInvoiceHeadRow") return;
    
        const key = th.dataset.key || "";
        const text = (th.textContent || "").trim();
        if (!text) th.textContent = key || "Column";
    
        const colIdx = th.cellIndex; // matches data-col-idx in toggles
        const toggle = document.querySelector(`.demo-col-toggle[data-col-idx="${colIdx}"]`);
        const span = toggle?.closest("label")?.querySelector(".demo-col-label-text");
        if (span) span.textContent = (th.textContent || "").trim() || key;
      });
    
      // ✅ Row checkbox behavior + Select All sync + row disable styling
      table.addEventListener("change", (e) => {
        const t = e.target;
    
        if (t && t.id === "demoSelectAll") {
          const checked = !!t.checked;
          document.querySelectorAll("#demoInvoiceBody tr").forEach(tr => {
            const cb = tr.querySelector(".demo-rowCheckbox");
            if (cb) cb.checked = checked;
            setRowEnabled(tr, checked);
          });
          syncSelectAllState();
          updateTotal();
          return;
        }
    
        if (t && t.classList && t.classList.contains("demo-rowCheckbox")) {
          const tr = t.closest("tr");
          if (tr) setRowEnabled(tr, t.checked);
          syncSelectAllState();
          updateTotal();
          return;
        }
      });
    
      // ✅ Inline edit recalculation (auto mode)
      table.addEventListener("input", (e) => {
        if (!isManualMode() && e.target.classList && e.target.classList.contains("amount")) {
          updateTotal();
        }
      });
    
      // ✅ Column toggles (delegated)
      phase3Root.addEventListener("change", (e) => {
        const cb = e.target.closest(".demo-col-toggle");
        if (!cb) return;
    
        if (cb.dataset.priceCol === "1") { cb.checked = true; return; }
    
        const colIdx = parseInt(cb.dataset.colIdx, 10);
        Array.from(table.rows).forEach(r => {
          const cell = r.cells[colIdx];
          if (cell) cell.style.display = cb.checked ? "" : "none";
        });
    
        updateTotal();
      });
    
      // ✅ Manual total input live update
      phase3Root.addEventListener("input", (e) => {
        if (e.target && e.target.id === "demoManualTotal") updateTotal();
      });
    
      // ✅ Currency change (delegated)
      phase3Root.addEventListener("change", (e) => {
        if (e.target && e.target.id === "demoCurrencyCode") {
          demoState.currencyCode = e.target.value;
          demoState.currencyDisplay = currencyMap[demoState.currencyCode] || "$";
          document.querySelectorAll("#miniDemo3 .currencyPrefix").forEach(x => x.textContent = demoState.currencyDisplay);
          updateTotal();
        }
      });
    
      // Due time toggle
      const dueToggle = document.getElementById("demo_toggle_due_time");
      const dueWrap = document.getElementById("demo_due_time_container");
      const dueTime = document.getElementById("demo_due_time");
      if (dueToggle && dueWrap) {
        dueToggle.addEventListener("change", () => {
          dueWrap.style.display = dueToggle.checked ? "block" : "none";
          if (!dueToggle.checked && dueTime) dueTime.value = "";
        });
      }
    
      // Stripe ack
      const ack = document.getElementById("demoManualOnlyAck");
      if (ack) ack.addEventListener("change", () => updateTotal());
    
      // Recurring toggle
      const recurBtn = document.getElementById("demoRecurringToggle");
      const recurText = document.getElementById("demoRecurringText");
      if (recurBtn && recurText) {
        recurBtn.addEventListener("click", () => {
          const isOn = recurBtn.classList.toggle("recurring-on");
          recurBtn.classList.toggle("recurring-off", !isOn);
          recurText.textContent = isOn ? "Enabled (Monthly)" : "Disabled (One-time)";
        });
      }
    
      // Banking drawer toggle
      const bankToggle = document.getElementById("demoToggleBankDetails");
      const bankDrawer = document.getElementById("demoBankingDrawer");
      if (bankToggle && bankDrawer) {
        bankToggle.addEventListener("change", () => {
          bankDrawer.classList.toggle("open", bankToggle.checked);
        });
      }
    
      // ✅ Add Field button (now under first column) — delegated click
      phase3Root.addEventListener("click", (e) => {
        const btn = e.target.closest("#demoAddFieldBtn");
        if (!btn) return;
    
        const body = document.getElementById("demoInvoiceBody");
        const head = document.getElementById("demoInvoiceHeadRow");
        if (!body || !head) return;
    
        const thCount = head.children.length; // includes select column
        const tr = document.createElement("tr");
        tr.className = "demo-data-row";
    
        let html = `<td><input type="checkbox" class="demo-rowCheckbox" checked></td>`;
        for (let i = 1; i < thCount; i++) {
          const key = head.children[i].dataset.key || "";
          const isPriceCol = (!isManualMode() && key && key === (demoState.priceColumn || ""));
          html += `<td class="${isPriceCol ? "amount " : ""}editable-cell" contenteditable="true"></td>`;
        }
    
        tr.innerHTML = html;
        body.appendChild(tr);
    
        applyColumnVisibilityToRow(tr);
        setRowEnabled(tr, true);
        syncSelectAllState();
        updateTotal();
      });
    
      // ✅ Signup modal helpers
      const modalBackdrop = document.getElementById("modalBackdrop");
      function openSignupModal() { modalBackdrop?.classList.add("open"); }
      function closeSignupModal(){ modalBackdrop?.classList.remove("open"); }
    
      document.getElementById("modalClose")?.addEventListener("click", closeSignupModal);
      modalBackdrop?.addEventListener("click", (e) => {
        if (e.target === modalBackdrop) closeSignupModal();
      });
    
      document.getElementById("demoSaveInvoiceBtn")?.addEventListener("click", () => {
        openSignupModal();
      });
    
      // Initial sync
      refreshAllRowStates();
      updateTotal();
    }
    
      // Reset button (uses existing reset button in landing demo header)
      el("demoReset")?.addEventListener("click", () => {
  
        demoState.phaseReached = 1;
        demoState.priceMode   = "column";
        demoState.priceColumn = "Amount Paid";
        demoState.includeCols = demoState.headers.map((_, i) => i);

        // ✅ restore true demo defaults
        demoState.billTo = { ...demoDefaults.billTo };
        demoState.priceMode = demoDefaults.priceMode;
        demoState.priceColumn = demoDefaults.priceColumn;
        
        if (el("demo_bill_to_name"))    el("demo_bill_to_name").value    = demoState.billTo.company;
        if (el("demo_bill_to_rep"))     el("demo_bill_to_rep").value     = demoState.billTo.rep;
        if (el("demo_bill_to_address")) el("demo_bill_to_address").value = demoState.billTo.address;
        if (el("demo_bill_to_phone"))   el("demo_bill_to_phone").value   = demoState.billTo.phone;
        if (el("demo_bill_to_email"))   el("demo_bill_to_email").value   = demoState.billTo.email;
        if (el("demo_google_sheet_url")) el("demo_google_sheet_url").value = "🔒 Demo Locked";

        if (fileInput) fileInput.value = "";
        if (fileNameDisplay) fileNameDisplay.style.display = "none";
        clearFileError();
    
        // clear custom table
        if (customContainer) customContainer.innerHTML = "";
    
        // hide step2 error
        const errBox = el("demo_step2_error");
        if (errBox) errBox.style.display = "none";
    
        showPhase(1);
      });
    
      // Init
      tab2.disabled = true;
      tab3.disabled = true;
      showPhase(1);
      
      // FAQ accordion (premium)
      document.querySelectorAll(".faq-item").forEach(btn => {
          btn.addEventListener("click", () => {
            const card = btn.closest(".glass");
            const panel = card?.querySelector(".faq-panel");
            const isOpen = btn.getAttribute("aria-expanded") === "true";
        
            btn.setAttribute("aria-expanded", isOpen ? "false" : "true");
            panel?.classList.toggle("hidden", isOpen);
          });
       });

    // Footer year
    document.getElementById("yr").textContent = new Date().getFullYear();
  </script>
</body>
</html>
