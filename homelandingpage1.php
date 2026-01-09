<?php /* bebo.php — OPTION A (Aurora Gradient + Interactive Mini Preview) */ ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>DocuBills — Invoices from Excel/Google Sheets in 3 Steps</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg0:#070A12;
      --bg1:#0B1222;
      --card: rgba(255,255,255,.06);
      --card2: rgba(255,255,255,.08);
      --stroke: rgba(255,255,255,.10);
      --text:#EAF0FF;
      --muted: rgba(234,240,255,.72);
      --muted2: rgba(234,240,255,.55);
      --accent:#4DA3FF;
      --accent2:#7C5CFF;
      --good:#2BE4A7;
      --warn:#FFCC66;
      --bad:#FF5C7C;
      --shadow: 0 20px 60px rgba(0,0,0,.45);
      --radius: 18px;
      --radius2: 26px;
      --max: 1160px;
    }

    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color:var(--text);
      background: radial-gradient(1200px 700px at 18% 18%, rgba(77,163,255,.25), transparent 55%),
                  radial-gradient(1200px 700px at 82% 26%, rgba(124,92,255,.22), transparent 60%),
                  radial-gradient(900px 600px at 55% 75%, rgba(43,228,167,.16), transparent 60%),
                  linear-gradient(180deg, var(--bg0), var(--bg1) 35%, #070A12 100%);
      overflow-x:hidden;
    }

    a{color:inherit;text-decoration:none}
    .wrap{max-width:var(--max); margin:0 auto; padding:0 18px;}
    .btn{
      display:inline-flex; align-items:center; gap:10px;
      border:1px solid transparent;
      padding:12px 16px;
      border-radius: 14px;
      font-weight:700;
      cursor:pointer;
      transition:.18s ease;
      user-select:none;
      white-space:nowrap;
    }
    .btn.primary{
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      box-shadow: 0 12px 30px rgba(77,163,255,.18);
    }
    .btn.primary:hover{transform: translateY(-1px); filter: brightness(1.04);}
    .btn.ghost{
      background: rgba(255,255,255,.06);
      border-color: rgba(255,255,255,.14);
    }
    .btn.ghost:hover{background: rgba(255,255,255,.085)}
    .pill{
      display:inline-flex; align-items:center; gap:8px;
      padding:8px 12px;
      border:1px solid rgba(255,255,255,.14);
      background: rgba(255,255,255,.06);
      border-radius: 999px;
      font-weight:700;
      color:var(--muted);
      backdrop-filter: blur(10px);
    }

    /* NAV */
    .nav{
      position:sticky; top:0; z-index:50;
      background: linear-gradient(180deg, rgba(7,10,18,.88), rgba(7,10,18,.60));
      border-bottom:1px solid rgba(255,255,255,.08);
      backdrop-filter: blur(12px);
    }
    .navin{
      display:flex; align-items:center; justify-content:space-between;
      padding:12px 0;
    }
    .brand{
      display:flex; align-items:center; gap:10px;
      font-weight:900; letter-spacing:.2px;
    }
    .logo{
      width:34px;height:34px;border-radius:12px;
      background: linear-gradient(135deg, rgba(77,163,255,1), rgba(124,92,255,1));
      box-shadow: 0 10px 24px rgba(124,92,255,.18);
      display:grid; place-items:center;
      border:1px solid rgba(255,255,255,.18);
    }
    .logo svg{opacity:.98}
    .navlinks{display:flex; gap:18px; align-items:center; color:var(--muted); font-weight:700}
    .navlinks a{padding:8px 10px;border-radius:12px}
    .navlinks a:hover{background: rgba(255,255,255,.06); color:var(--text)}
    .navcta{display:flex; gap:10px; align-items:center}
    .hide-sm{display:none}

    /* HERO */
    .hero{
      padding:56px 0 18px;
      position:relative;
    }
    .heroGrid{
      display:grid;
      grid-template-columns: 1.05fr .95fr;
      gap:22px;
      align-items:stretch;
    }
    .hTitle{
      font-size: clamp(34px, 4.2vw, 56px);
      line-height:1.05;
      margin:14px 0 12px;
      letter-spacing:-.8px;
    }
    .hSub{
      font-size: 16px;
      line-height:1.6;
      color: var(--muted);
      max-width: 54ch;
      margin:0 0 18px;
    }
    .hBadges{
      display:flex; flex-wrap:wrap; gap:10px; margin:14px 0 20px;
    }
    .hActions{display:flex; gap:12px; align-items:center; flex-wrap:wrap}
    .micro{
      margin-top:14px;
      color:var(--muted2);
      font-weight:600;
      display:flex; gap:12px; flex-wrap:wrap;
    }
    .micro span{display:inline-flex; align-items:center; gap:8px}
    .dot{
      width:8px;height:8px;border-radius:999px;background: var(--good);
      box-shadow:0 0 0 3px rgba(43,228,167,.12);
    }

    /* CARD */
    .card{
      background: rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.10);
      border-radius: var(--radius2);
      box-shadow: var(--shadow);
      overflow:hidden;
    }
    .cardHead{
      padding:14px 16px;
      display:flex; align-items:center; justify-content:space-between;
      border-bottom:1px solid rgba(255,255,255,.08);
      background: rgba(255,255,255,.04);
    }
    .cardHead .title{
      font-weight:900;
      letter-spacing:-.3px;
    }
    .cardBody{padding:14px 16px}

    /* MINI DEMO (HERO RIGHT) */
    .miniTabs{
      display:flex; gap:8px; flex-wrap:wrap;
    }
    .tab{
      padding:9px 10px;
      border-radius: 12px;
      font-weight:800;
      color:var(--muted);
      background: rgba(255,255,255,.05);
      border:1px solid rgba(255,255,255,.10);
      cursor:pointer;
      transition:.15s ease;
      user-select:none;
    }
    .tab.active{
      color:var(--text);
      background: linear-gradient(135deg, rgba(77,163,255,.22), rgba(124,92,255,.18));
      border-color: rgba(77,163,255,.30);
    }
    .grid2{
      display:grid; grid-template-columns: 1fr 1fr; gap:10px;
    }
    .field label{display:block; font-size:12px; color:var(--muted2); font-weight:800; margin-bottom:6px}
    .field select, .field input{
      width:100%;
      padding:10px 11px;
      border-radius: 14px;
      border:1px solid rgba(255,255,255,.14);
      background: rgba(7,10,18,.55);
      color:var(--text);
      outline:none;
      font-weight:700;
    }
    .field select:focus, .field input:focus{border-color: rgba(77,163,255,.55)}
    .tog{
      display:flex; align-items:center; justify-content:space-between;
      padding:10px 12px;
      border-radius: 14px;
      border:1px solid rgba(255,255,255,.12);
      background: rgba(255,255,255,.05);
      margin-top:10px;
    }
    .switch{
      width:44px; height:26px; border-radius:999px;
      background: rgba(255,255,255,.12);
      border:1px solid rgba(255,255,255,.12);
      position:relative; cursor:pointer;
      transition:.18s ease;
      flex:0 0 auto;
    }
    .switch::after{
      content:"";
      position:absolute; top:3px; left:3px;
      width:20px; height:20px; border-radius:999px;
      background: rgba(255,255,255,.86);
      transition:.18s ease;
    }
    .switch.on{
      background: rgba(43,228,167,.22);
      border-color: rgba(43,228,167,.35);
    }
    .switch.on::after{left:21px; background: rgba(43,228,167,.95)}
    .tog .l{
      display:flex; flex-direction:column; gap:3px;
    }
    .tog .l b{font-size:13px}
    .tog .l span{font-size:12px; color:var(--muted2); font-weight:700}
    .sheet{
      margin-top:12px;
      border-radius: 16px;
      border:1px solid rgba(255,255,255,.10);
      overflow:hidden;
      background: rgba(7,10,18,.35);
    }
    table{
      width:100%;
      border-collapse:collapse;
      font-size:12px;
    }
    thead th{
      text-align:left;
      padding:10px 10px;
      color: rgba(234,240,255,.75);
      font-weight:900;
      background: rgba(255,255,255,.04);
      border-bottom:1px solid rgba(255,255,255,.08);
    }
    tbody td{
      padding:9px 10px;
      border-bottom:1px solid rgba(255,255,255,.06);
      color: rgba(234,240,255,.86);
      font-weight:650;
    }
    tbody tr:hover{background: rgba(255,255,255,.03)}
    .cb{
      display:inline-flex; align-items:center; gap:8px;
    }
    .cb input{accent-color: var(--accent)}
    .badge{
      display:inline-flex; align-items:center; gap:6px;
      font-size:12px; font-weight:900;
      padding:7px 10px;
      border-radius: 999px;
      background: rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.10);
      color: rgba(234,240,255,.86);
    }
    .totalLine{
      display:flex; align-items:center; justify-content:space-between;
      margin-top:12px;
      padding:12px 12px;
      border-radius: 16px;
      background: linear-gradient(135deg, rgba(77,163,255,.16), rgba(124,92,255,.14));
      border:1px solid rgba(77,163,255,.20);
    }
    .totalLine .k{color:var(--muted); font-weight:900}
    .totalLine .v{font-weight:950; letter-spacing:-.3px}

    /* SECTIONS */
    section{padding:54px 0}
    .secTitle{
      font-size: 28px; letter-spacing:-.6px;
      margin:0 0 10px; font-weight:950;
    }
    .secSub{margin:0 0 18px; color:var(--muted); line-height:1.7; max-width:75ch}
    .grid3{display:grid; grid-template-columns: repeat(3, 1fr); gap:14px}
    .feat{
      padding:16px;
      border-radius: var(--radius);
      background: rgba(255,255,255,.05);
      border:1px solid rgba(255,255,255,.10);
      transition:.15s ease;
      min-height: 146px;
    }
    .feat:hover{transform: translateY(-2px); background: rgba(255,255,255,.07)}
    .ic{
      width:44px;height:44px;border-radius: 16px;
      display:grid; place-items:center;
      background: linear-gradient(135deg, rgba(77,163,255,.20), rgba(124,92,255,.15));
      border:1px solid rgba(77,163,255,.22);
      margin-bottom:10px;
    }
    .feat h3{margin:0 0 6px; font-size:15px; font-weight:950}
    .feat p{margin:0; color:var(--muted); line-height:1.55; font-size:13px; font-weight:650}

    /* STEPS */
    .steps{
      display:grid; grid-template-columns: 1.05fr .95fr; gap:14px; align-items:start;
    }
    .stepList{display:flex; flex-direction:column; gap:10px}
    .stepItem{
      padding:14px 14px;
      border-radius: var(--radius);
      border:1px solid rgba(255,255,255,.10);
      background: rgba(255,255,255,.05);
      cursor:pointer;
      transition:.15s ease;
    }
    .stepItem:hover{background: rgba(255,255,255,.07)}
    .stepItem.active{
      border-color: rgba(77,163,255,.34);
      background: linear-gradient(135deg, rgba(77,163,255,.14), rgba(124,92,255,.12));
    }
    .stepTop{display:flex; align-items:center; justify-content:space-between; gap:10px}
    .stepTop b{font-weight:950}
    .stepTop .n{
      width:34px;height:34px;border-radius: 14px;
      display:grid; place-items:center;
      background: rgba(255,255,255,.08);
      border:1px solid rgba(255,255,255,.12);
      font-weight:950;
      color: rgba(234,240,255,.92);
    }
    .stepItem.active .n{
      background: rgba(77,163,255,.18);
      border-color: rgba(77,163,255,.26);
    }
    .stepItem p{margin:10px 0 0; color:var(--muted); line-height:1.6; font-weight:650; font-size:13px}
    .stepPanel{
      padding:16px;
      border-radius: var(--radius2);
      border:1px solid rgba(255,255,255,.10);
      background: rgba(255,255,255,.05);
    }
    .stepPanel h4{margin:0 0 6px; font-size:16px; font-weight:950}
    .stepPanel ul{margin:10px 0 0; padding-left:18px; color:var(--muted); line-height:1.7; font-weight:650}
    .kbd{
      display:inline-flex; align-items:center;
      padding:4px 8px;
      border-radius: 10px;
      background: rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.12);
      font-weight:900;
      color: rgba(234,240,255,.86);
      font-size:12px;
    }

    /* PRICING */
    .pricingTop{
      display:flex; align-items:end; justify-content:space-between; gap:12px; flex-wrap:wrap;
    }
    .toggleRow{display:flex; gap:10px; align-items:center; color:var(--muted); font-weight:800}
    .seg{
      display:flex; background: rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.12);
      border-radius: 999px; overflow:hidden;
    }
    .seg button{
      background: transparent; border:0; color:var(--muted);
      padding:10px 12px; font-weight:950; cursor:pointer;
    }
    .seg button.active{
      background: linear-gradient(135deg, rgba(77,163,255,.22), rgba(124,92,255,.16));
      color:var(--text);
    }
    .priceGrid{display:grid; grid-template-columns: repeat(3, 1fr); gap:14px; margin-top:18px}
    .plan{
      padding:16px;
      border-radius: var(--radius2);
      border:1px solid rgba(255,255,255,.10);
      background: rgba(255,255,255,.05);
      position:relative;
      overflow:hidden;
    }
    .plan.reco{
      background: linear-gradient(135deg, rgba(77,163,255,.16), rgba(124,92,255,.12));
      border-color: rgba(77,163,255,.28);
    }
    .plan h3{margin:0 0 6px; font-weight:950}
    .plan p{margin:0 0 14px; color:var(--muted); line-height:1.6; font-weight:650; font-size:13px}
    .price{
      font-size: 30px;
      font-weight: 950;
      letter-spacing:-.8px;
      margin: 0 0 12px;
    }
    .price small{font-size: 12px; color:var(--muted2); font-weight:900}
    .list{margin:0; padding-left:18px; color:var(--muted); line-height:1.75; font-weight:650}
    .ribbon{
      position:absolute; top:14px; right:14px;
      padding:7px 10px;
      border-radius: 999px;
      background: rgba(43,228,167,.16);
      border:1px solid rgba(43,228,167,.28);
      color: rgba(43,228,167,.95);
      font-weight:950;
      font-size:12px;
    }

    /* FAQ */
    .faq{display:grid; grid-template-columns: 1fr 1fr; gap:12px}
    .qa{
      padding:14px 14px;
      border-radius: var(--radius);
      border:1px solid rgba(255,255,255,.10);
      background: rgba(255,255,255,.05);
      cursor:pointer;
      transition:.15s ease;
    }
    .qa:hover{background: rgba(255,255,255,.07)}
    .qa .q{display:flex; justify-content:space-between; align-items:center; gap:10px; font-weight:950}
    .qa .a{margin-top:10px; color:var(--muted); line-height:1.65; font-weight:650; display:none}
    .qa.open .a{display:block}
    .plus{
      width:28px;height:28px;border-radius: 12px;
      display:grid; place-items:center;
      background: rgba(255,255,255,.06);
      border:1px solid rgba(255,255,255,.12);
      flex:0 0 auto;
      font-weight:950;
      color: rgba(234,240,255,.85);
    }

    /* INTERACTIVE DEMO PLACEHOLDER */
    .demoBox{
      border-radius: var(--radius2);
      border:1px dashed rgba(255,255,255,.22);
      background: rgba(255,255,255,.04);
      padding:18px;
      color:var(--muted);
    }
    .demoBox b{color:var(--text)}
    .demoMount{
      margin-top:14px;
      border-radius: 16px;
      border:1px solid rgba(255,255,255,.10);
      background: rgba(7,10,18,.35);
      min-height: 240px;
      display:grid;
      place-items:center;
      padding:18px;
    }

    /* FOOTER */
    footer{
      padding:34px 0 44px;
      border-top:1px solid rgba(255,255,255,.08);
      color:var(--muted);
    }
    .foot{
      display:flex; justify-content:space-between; gap:14px; flex-wrap:wrap; align-items:center;
    }
    .foot a{color:var(--muted); font-weight:800}
    .foot a:hover{color:var(--text)}

    /* RESPONSIVE */
    @media (max-width: 980px){
      .heroGrid{grid-template-columns: 1fr; }
      .steps{grid-template-columns: 1fr}
      .grid3{grid-template-columns: 1fr}
      .priceGrid{grid-template-columns: 1fr}
      .faq{grid-template-columns: 1fr}
      .hide-sm{display:none}
    }
    @media (min-width: 980px){
      .hide-sm{display:flex}
    }
  </style>
</head>

<body>
  <!-- NAV -->
  <div class="nav">
    <div class="wrap">
      <div class="navin">
        <a class="brand" href="#top" aria-label="DocuBills Home">
          <div class="logo" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M7 3h7l3 3v15a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="white" stroke-width="2"/>
              <path d="M14 3v4a2 2 0 0 0 2 2h4" stroke="white" stroke-width="2"/>
            </svg>
          </div>
          <span>DocuBills</span>
        </a>

        <div class="navlinks hide-sm">
          <a href="#how">How it works</a>
          <a href="#features">Features</a>
          <a href="#pricing">Pricing</a>
          <a href="#faq">FAQ</a>
          <a href="#interactive-demo">Interactive Demo</a>
        </div>

        <div class="navcta">
          <a class="btn ghost" href="login.php">Login</a>
          <a class="btn primary" href="register.php">
            Start Free
            <span aria-hidden="true">→</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- HERO -->
  <div id="top" class="hero">
    <div class="wrap">
      <div class="heroGrid">
        <!-- LEFT -->
        <div>
          <span class="pill">⚡ Invoices from Excel/Google Sheets — without column mapping headaches</span>

          <h1 class="hTitle">
            Generate a clean, professional invoice in <span style="color:var(--good)">3 steps</span> —
            straight from your spreadsheet.
          </h1>

          <p class="hSub">
            Upload an Excel file or paste a Google Sheet link. DocuBills auto-understands your data,
            lets you choose which column calculates totals, and lets you deselect any rows/columns
            before exporting the final PDF.
          </p>

          <div class="hActions">
            <a class="btn primary" href="register.php">Create my first invoice →</a>
            <a class="btn ghost" href="#interactive-demo">See interactive demo</a>
          </div>

          <div class="hBadges">
            <span class="badge">✅ No credit card</span>
            <span class="badge">📄 PDF export</span>
            <span class="badge">🔁 Recurring invoices</span>
            <span class="badge">📧 Auto reminders</span>
            <span class="badge">👥 Roles & permissions</span>
          </div>

          <div class="micro">
            <span><span class="dot"></span> Built for busy teams & small businesses</span>
            <span>•</span>
            <span>Set reminders cadence + edit email templates inside the app</span>
          </div>
        </div>

        <!-- RIGHT (INTERACTIVE MINI PREVIEW) -->
        <div class="card" aria-label="Interactive preview widget">
          <div class="cardHead">
            <div class="title">Interactive Mini Preview</div>
            <div class="miniTabs">
              <div class="tab active" data-tab="preview">Preview</div>
              <div class="tab" data-tab="recurring">Recurring</div>
              <div class="tab" data-tab="reminders">Reminders</div>
            </div>
          </div>

          <div class="cardBody">
            <!-- PREVIEW TAB -->
            <div class="tabPanel" data-panel="preview">
              <div class="grid2">
                <div class="field">
                  <label>Source</label>
                  <select id="src">
                    <option value="excel">Excel upload (.xlsx)</option>
                    <option value="gsheet">Google Sheet URL</option>
                  </select>
                </div>
                <div class="field">
                  <label>Total column</label>
                  <select id="totalCol">
                    <option value="amount" selected>Amount</option>
                    <option value="rate">Rate</option>
                    <option value="qty">Qty</option>
                  </select>
                </div>

                <div class="field">
                  <label>Currency</label>
                  <select id="currency">
                    <option value="$">$ USD</option>
                    <option value="C$">C$ CAD</option>
                    <option value="£">£ GBP</option>
                    <option value="€">€ EUR</option>
                  </select>
                </div>

                <div class="field">
                  <label>Bank line (editable)</label>
                  <input id="bankLine" placeholder="e.g., Bank: RBC • Acc: 12345 • SWIFT: RBCCCA" />
                </div>
              </div>

              <div class="tog">
                <div class="l">
                  <b>Print bank info on invoice</b>
                  <span>Edit it right here before exporting PDF</span>
                </div>
                <div class="switch on" id="bankToggle" role="switch" aria-checked="true"></div>
              </div>

              <div class="tog">
                <div class="l">
                  <b>Hide accidental columns</b>
                  <span>Uncheck anything you don’t want printed</span>
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
                  <label class="cb" title="Show Qty column"><input type="checkbox" id="colQty" checked> Qty</label>
                  <label class="cb" title="Show Rate column"><input type="checkbox" id="colRate" checked> Rate</label>
                  <label class="cb" title="Show Amount column"><input type="checkbox" id="colAmount" checked> Amount</label>
                </div>
              </div>

              <div class="sheet" aria-label="Spreadsheet preview table">
                <table>
                  <thead>
                    <tr>
                      <th style="width:56px">Print</th>
                      <th>Description</th>
                      <th class="thQty">Qty</th>
                      <th class="thRate">Rate</th>
                      <th class="thAmount">Amount</th>
                    </tr>
                  </thead>
                  <tbody id="rows"></tbody>
                </table>
              </div>

              <div class="totalLine">
                <div>
                  <div class="k">Invoice total (based on your chosen column)</div>
                  <div style="color:var(--muted2); font-weight:800; margin-top:4px">
                    Try unchecking rows/columns — total updates instantly.
                  </div>
                </div>
                <div class="v" id="totalOut">$0.00</div>
              </div>
            </div>

            <!-- RECURRING TAB -->
            <div class="tabPanel" data-panel="recurring" style="display:none">
              <div class="grid2">
                <div class="field">
                  <label>Recurring</label>
                  <select id="recurOn">
                    <option value="off" selected>Off</option>
                    <option value="on">On</option>
                  </select>
                </div>
                <div class="field">
                  <label>Frequency</label>
                  <select id="recurFreq">
                    <option value="monthly" selected>Monthly</option>
                    <option value="weekly">Weekly</option>
                    <option value="quarterly">Quarterly</option>
                  </select>
                </div>
              </div>

              <div class="tog">
                <div class="l">
                  <b>Create next invoice automatically</b>
                  <span>Generated right from the preview page</span>
                </div>
                <div class="switch" id="autoGen" role="switch" aria-checked="false"></div>
              </div>

              <div style="margin-top:12px; color:var(--muted); line-height:1.7; font-weight:650">
                Once enabled, DocuBills can automatically generate new invoices on schedule, keep your
                bank info/currency consistent, and keep reminders running.
              </div>

              <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap">
                <span class="badge">🔁 Recurring plan from Preview</span>
                <span class="badge">🧾 Same template</span>
                <span class="badge">📧 Reminder cadence preserved</span>
              </div>
            </div>

            <!-- REMINDERS TAB -->
            <div class="tabPanel" data-panel="reminders" style="display:none">
              <div class="field">
                <label>Reminder cadence (example)</label>
                <select id="cadence">
                  <option value="gentle" selected>Gentle: 3 days before • on due date • 3 days after</option>
                  <option value="firm">Firm: 7 days before • 3 days before • on due • 7 days after</option>
                  <option value="custom">Custom (you control options)</option>
                </select>
              </div>

              <div style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap">
                <span class="badge">✍️ Edit email templates in-app</span>
                <span class="badge">📩 CC/BCC support</span>
                <span class="badge">🔔 Status-change notifications</span>
              </div>

              <div style="margin-top:12px; color:var(--muted); line-height:1.7; font-weight:650">
                Tired of manual follow-ups? Set the cadence once and DocuBills handles the reminders —
                using your own email template content.
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- HOW IT WORKS -->
  <section id="how">
    <div class="wrap">
      <h2 class="secTitle">How it works (the real 3 steps)</h2>
      <p class="secSub">
        No confusing column mapping screens. You stay in control: totals, print columns, printed rows,
        currency, bank info — all from the preview.
      </p>

      <div class="steps">
        <div class="stepList">
          <div class="stepItem active" data-step="1">
            <div class="stepTop">
              <div style="display:flex; align-items:center; gap:10px">
                <div class="n">1</div>
                <b>Upload Excel or paste Google Sheet URL</b>
              </div>
              <span class="kbd">30 sec</span>
            </div>
            <p>DocuBills automatically reads headers + rows and prepares a clean preview.</p>
          </div>

          <div class="stepItem" data-step="2">
            <div class="stepTop">
              <div style="display:flex; align-items:center; gap:10px">
                <div class="n">2</div>
                <b>Select the column that calculates the total</b>
              </div>
              <span class="kbd">1 click</span>
            </div>
            <p>Choose which column represents your totals — DocuBills calculates instantly.</p>
          </div>

          <div class="stepItem" data-step="3">
            <div class="stepTop">
              <div style="display:flex; align-items:center; gap:10px">
                <div class="n">3</div>
                <b>Deselect unwanted rows/columns, then export PDF</b>
              </div>
              <span class="kbd">Control</span>
            </div>
            <p>Accidentally included something? Uncheck it — it won’t print on the final invoice.</p>
          </div>
        </div>

        <div class="stepPanel" id="stepPanel">
          <h4>Step 1: Upload your data</h4>
          <div style="color:var(--muted); line-height:1.7; font-weight:650">
            Whether it’s Excel or Google Sheets, you’re ready in seconds.
          </div>
          <ul>
            <li>Auto-detects columns & data</li>
            <li>No painful mapping wizard</li>
            <li>Instant preview you can edit</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURES -->
  <section id="features">
    <div class="wrap">
      <h2 class="secTitle">Why DocuBills converts spreadsheet chaos into paid invoices</h2>
      <p class="secSub">
        This isn’t “just invoicing”. It’s an end-to-end flow: generate → preview → recurring → reminders → manage → paid/unpaid notifications.
      </p>

      <div class="grid3">
        <div class="feat">
          <div class="ic">📥</div>
          <h3>Upload Excel / Google Sheet URL</h3>
          <p>Drop your spreadsheet in and get a structured preview instantly — no mapping confusion.</p>
        </div>
        <div class="feat">
          <div class="ic">🧮</div>
          <h3>Choose the “total” column</h3>
          <p>Pick which column should drive invoice totals — perfect for different spreadsheet styles.</p>
        </div>
        <div class="feat">
          <div class="ic">✅</div>
          <h3>Deselect rows & columns</h3>
          <p>Remove accidental rows/columns before printing. What’s unchecked will never appear on the PDF.</p>
        </div>
        <div class="feat">
          <div class="ic">🔁</div>
          <h3>Recurring invoices from Preview</h3>
          <p>Enable recurring right from the preview screen. Keep settings + templates consistent.</p>
        </div>
        <div class="feat">
          <div class="ic">🏦</div>
          <h3>Bank info + currency on the fly</h3>
          <p>Pull bank details from Settings and edit them inline. Pick currency per invoice.</p>
        </div>
        <div class="feat">
          <div class="ic">📧</div>
          <h3>Automated reminders & templates</h3>
          <p>Set your cadence. Write your email content inside the app. DocuBills does the follow-ups.</p>
        </div>
        <div class="feat">
          <div class="ic">📌</div>
          <h3>Manage everything in one place</h3>
          <p>Invoice statuses, logs, exports, mark paid/unpaid, and notify the right people instantly.</p>
        </div>
        <div class="feat">
          <div class="ic">🔔</div>
          <h3>Status-change notifications</h3>
          <p>Every status flip triggers a notification. Keep clients and internal teams in sync.</p>
        </div>
        <div class="feat">
          <div class="ic">🛡️</div>
          <h3>Admin roles + permissions</h3>
          <p>Control every feature via roles and granular permissions matrix.</p>
        </div>
      </div>

      <div style="margin-top:18px; display:flex; gap:12px; flex-wrap:wrap; align-items:center">
        <a class="btn primary" href="register.php">Start Free →</a>
        <a class="btn ghost" href="#pricing">See pricing</a>
      </div>
    </div>
  </section>

  <!-- INTERACTIVE DEMO PLACEHOLDER -->
  <section id="interactive-demo">
    <div class="wrap">
      <h2 class="secTitle">Interactive Demo</h2>
      <p class="secSub">
        This section is reserved for your real interactive demo widget. When you share the code, we’ll mount it here.
      </p>

      <div class="demoBox">
        <b>Drop-in area:</b> Paste your demo code inside <span class="kbd">#demoMount</span> (or replace the placeholder content).
        <div id="demoMount" class="demoMount">
          <div style="text-align:center">
            <div style="font-weight:950; font-size:16px">Your Interactive Demo goes here</div>
            <div style="color:var(--muted2); margin-top:6px; font-weight:700">
              (We’ll plug it in once you share the demo code.)
            </div>
          </div>
        </div>
      </div>

      <div style="margin-top:14px; color:var(--muted); font-weight:650; line-height:1.7">
        Tip: If your demo needs external JS/CSS, we’ll include it right below this section to keep everything clean.
      </div>
    </div>
  </section>

  <!-- PRICING -->
  <section id="pricing">
    <div class="wrap">
      <div class="pricingTop">
        <div>
          <h2 class="secTitle">Simple pricing that scales with your workflow</h2>
          <p class="secSub">Start free, then upgrade when you need recurring automation, advanced reminders, and admin control.</p>
        </div>

        <div class="toggleRow">
          <span>Billing</span>
          <div class="seg" role="tablist" aria-label="Billing toggle">
            <button class="active" id="billM" aria-selected="true">Monthly</button>
            <button id="billY" aria-selected="false">Yearly <span style="color:var(--good)">(-20%)</span></button>
          </div>
        </div>
      </div>

      <div class="priceGrid">
        <div class="plan">
          <h3>Starter</h3>
          <p>For testing the workflow and generating invoices fast.</p>
          <div class="price" data-m="0" data-y="0">$0 <small>/ mo</small></div>
          <ul class="list">
            <li>Excel / Google Sheets import</li>
            <li>Choose total column</li>
            <li>Export PDF</li>
            <li>Basic invoice management</li>
          </ul>
          <div style="margin-top:14px">
            <a class="btn ghost" href="register.php">Get started</a>
          </div>
        </div>

        <div class="plan reco">
          <div class="ribbon">Most Popular</div>
          <h3>Pro</h3>
          <p>For teams who want recurring invoices + reminders that work.</p>
          <div class="price" data-m="19" data-y="15">$19 <small>/ mo</small></div>
          <ul class="list">
            <li>All Starter features</li>
            <li>Deselect rows/columns</li>
            <li>Recurring invoices</li>
            <li>Reminder cadence + templates</li>
            <li>Status-change email alerts</li>
          </ul>
          <div style="margin-top:14px">
            <a class="btn primary" href="register.php">Start Pro →</a>
          </div>
        </div>

        <div class="plan">
          <h3>Business</h3>
          <p>For admin control: roles, permissions, and governance.</p>
          <div class="price" data-m="49" data-y="39">$49 <small>/ mo</small></div>
          <ul class="list">
            <li>All Pro features</li>
            <li>Roles & permissions matrix</li>
            <li>Advanced notifications & CC/BCC</li>
            <li>Audit-friendly workflows</li>
          </ul>
          <div style="margin-top:14px">
            <a class="btn ghost" href="register.php">Contact / Start</a>
          </div>
        </div>
      </div>

      <div style="margin-top:14px; color:var(--muted2); font-weight:700">
        * Pricing numbers are placeholders — tell me your real pricing later and I’ll wire it perfectly.
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section id="faq">
    <div class="wrap">
      <h2 class="secTitle">FAQ</h2>
      <p class="secSub">Quick answers to the questions people ask right before they sign up.</p>

      <div class="faq">
        <div class="qa">
          <div class="q">Do I need to map columns? <span class="plus">+</span></div>
          <div class="a">No. DocuBills reads your sheet and lets you choose the total column + what to print — without a mapping wizard.</div>
        </div>
        <div class="qa">
          <div class="q">Can I remove accidental rows/columns? <span class="plus">+</span></div>
          <div class="a">Yes. Uncheck any row/column and it will not appear on the final PDF invoice.</div>
        </div>
        <div class="qa">
          <div class="q">Can I change currency per invoice? <span class="plus">+</span></div>
          <div class="a">Yes. Choose currency from the preview page before exporting PDF.</div>
        </div>
        <div class="qa">
          <div class="q">Does it send follow-up reminders automatically? <span class="plus">+</span></div>
          <div class="a">Yes. You can choose reminder cadence and write email template content inside the app.</div>
        </div>
      </div>

      <div style="margin-top:18px; display:flex; gap:12px; flex-wrap:wrap; align-items:center">
        <a class="btn primary" href="register.php">Start Free →</a>
        <a class="btn ghost" href="#how">Back to “How it works”</a>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <div class="wrap">
      <div class="foot">
        <div style="display:flex; align-items:center; gap:10px">
          <div class="logo" aria-hidden="true" style="width:30px;height:30px;border-radius:12px">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M7 3h7l3 3v15a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="white" stroke-width="2"/>
            </svg>
          </div>
          <div style="font-weight:950; color:var(--text)">DocuBills</div>
          <div style="color:var(--muted2); font-weight:700">© <?= date('Y') ?></div>
        </div>

        <div style="display:flex; gap:14px; flex-wrap:wrap">
          <a href="#features">Features</a>
          <a href="#pricing">Pricing</a>
          <a href="#faq">FAQ</a>
          <a href="login.php">Login</a>
          <a href="register.php">Sign up</a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // Smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(a=>{
      a.addEventListener('click', (e)=>{
        const id = a.getAttribute('href');
        if(!id || id === '#') return;
        const el = document.querySelector(id);
        if(el){
          e.preventDefault();
          el.scrollIntoView({behavior:'smooth', block:'start'});
        }
      });
    });

    // Mini tabs
    const tabs = document.querySelectorAll('.tab');
    const panels = document.querySelectorAll('.tabPanel');
    tabs.forEach(t=>{
      t.addEventListener('click', ()=>{
        tabs.forEach(x=>x.classList.remove('active'));
        t.classList.add('active');
        const name = t.dataset.tab;
        panels.forEach(p=>{
          p.style.display = (p.dataset.panel === name) ? 'block' : 'none';
        });
      });
    });

    // Switch toggles
    function toggleSwitch(el){
      el.classList.toggle('on');
      el.setAttribute('aria-checked', el.classList.contains('on') ? 'true' : 'false');
    }
    const bankToggle = document.getElementById('bankToggle');
    const autoGen = document.getElementById('autoGen');
    bankToggle.addEventListener('click', ()=> {
      toggleSwitch(bankToggle);
      document.getElementById('bankLine').disabled = !bankToggle.classList.contains('on');
      document.getElementById('bankLine').style.opacity = bankToggle.classList.contains('on') ? '1' : '.6';
    });
    autoGen.addEventListener('click', ()=> toggleSwitch(autoGen));

    // Step interactions
    const stepPanel = document.getElementById('stepPanel');
    const stepItems = document.querySelectorAll('.stepItem');
    const stepCopy = {
      1: {
        title: "Step 1: Upload your data",
        sub: "Whether it’s Excel or Google Sheets, you’re ready in seconds.",
        bullets: ["Auto-detects columns & data", "No painful mapping wizard", "Instant preview you can edit"]
      },
      2: {
        title: "Step 2: Pick the total column",
        sub: "Tell DocuBills which column represents the invoice total. Everything recalculates instantly.",
        bullets: ["Works with different sheet formats", "Instant calculation", "No manual formulas needed"]
      },
      3: {
        title: "Step 3: Control what prints, then export",
        sub: "Uncheck any accidental rows/columns before printing the final PDF invoice.",
        bullets: ["Hide columns you don’t need", "Remove accidental rows", "Export a clean PDF in seconds"]
      }
    };
    stepItems.forEach(si=>{
      si.addEventListener('click', ()=>{
        stepItems.forEach(x=>x.classList.remove('active'));
        si.classList.add('active');
        const n = si.dataset.step;
        const d = stepCopy[n];
        stepPanel.innerHTML = `
          <h4>${d.title}</h4>
          <div style="color:var(--muted); line-height:1.7; font-weight:650">${d.sub}</div>
          <ul>${d.bullets.map(b=>`<li>${b}</li>`).join('')}</ul>
        `;
      });
    });

    // Pricing toggle
    const billM = document.getElementById('billM');
    const billY = document.getElementById('billY');
    const prices = document.querySelectorAll('.price');
    function setBilling(mode){
      if(mode==='m'){
        billM.classList.add('active'); billY.classList.remove('active');
        billM.setAttribute('aria-selected','true'); billY.setAttribute('aria-selected','false');
        prices.forEach(p=>{
          const m = p.getAttribute('data-m');
          p.innerHTML = `$${m} <small>/ mo</small>`;
        });
      }else{
        billY.classList.add('active'); billM.classList.remove('active');
        billY.setAttribute('aria-selected','true'); billM.setAttribute('aria-selected','false');
        prices.forEach(p=>{
          const y = p.getAttribute('data-y');
          p.innerHTML = `$${y} <small>/ mo (billed yearly)</small>`;
        });
      }
    }
    billM.addEventListener('click', ()=>setBilling('m'));
    billY.addEventListener('click', ()=>setBilling('y'));

    // FAQ accordion
    document.querySelectorAll('.qa').forEach(q=>{
      q.addEventListener('click', ()=>{
        q.classList.toggle('open');
        q.querySelector('.plus').textContent = q.classList.contains('open') ? '–' : '+';
      });
    });

    // Mini preview table logic
    const data = [
      {desc:"Website Maintenance", qty:1, rate:120, amount:120, on:true},
      {desc:"Hosting (Jan)", qty:1, rate:35, amount:35, on:true},
      {desc:"Logo touch-up (accidental row)", qty:1, rate:20, amount:20, on:false},
      {desc:"Consultation", qty:2, rate:60, amount:120, on:true},
    ];

    const rowsEl = document.getElementById('rows');
    function fmt(n){ return (Math.round(n*100)/100).toFixed(2); }

    function renderRows(){
      rowsEl.innerHTML = data.map((r,i)=>`
        <tr>
          <td><label class="cb"><input type="checkbox" data-row="${i}" ${r.on?'checked':''}> </label></td>
          <td>${r.desc}</td>
          <td class="tdQty">${r.qty}</td>
          <td class="tdRate">${r.rate}</td>
          <td class="tdAmount">${r.amount}</td>
        </tr>
      `).join('');
      rowsEl.querySelectorAll('input[type="checkbox"]').forEach(cb=>{
        cb.addEventListener('change', ()=>{
          const idx = +cb.dataset.row;
          data[idx].on = cb.checked;
          updateTotal();
        });
      });
      updateColumns();
    }

    function updateColumns(){
      const showQty = document.getElementById('colQty').checked;
      const showRate = document.getElementById('colRate').checked;
      const showAmount = document.getElementById('colAmount').checked;

      document.querySelectorAll('.thQty, .tdQty').forEach(el=>el.style.display = showQty ? '' : 'none');
      document.querySelectorAll('.thRate, .tdRate').forEach(el=>el.style.display = showRate ? '' : 'none');
      document.querySelectorAll('.thAmount, .tdAmount').forEach(el=>el.style.display = showAmount ? '' : 'none');

      // if selected total column is hidden, auto-correct
      const totalCol = document.getElementById('totalCol');
      const v = totalCol.value;
      if((v==='qty' && !showQty) || (v==='rate' && !showRate) || (v==='amount' && !showAmount)){
        if(showAmount) totalCol.value = 'amount';
        else if(showRate) totalCol.value = 'rate';
        else totalCol.value = 'qty';
      }
      updateTotal();
    }

    function updateTotal(){
      const currency = document.getElementById('currency').value;
      const col = document.getElementById('totalCol').value;
      const sum = data.reduce((acc,r)=>{
        if(!r.on) return acc;
        return acc + (col==='qty' ? r.qty : (col==='rate' ? r.rate : r.amount));
      },0);
      document.getElementById('totalOut').textContent = `${currency}${fmt(sum)}`;
    }

    // Wire controls
    ['totalCol','currency'].forEach(id=>document.getElementById(id).addEventListener('change', updateTotal));
    ['colQty','colRate','colAmount'].forEach(id=>document.getElementById(id).addEventListener('change', updateColumns));
    document.getElementById('bankLine').value = "Bank: Example • Acc: 12345 • SWIFT: EXAMPLEx";
    document.getElementById('src').addEventListener('change', (e)=>{
      const isG = e.target.value === 'gsheet';
      const pill = isG ? "Google Sheet URL detected ✔" : "Excel upload detected ✔";
      // tiny feedback
      const old = document.querySelector('.cardHead .title');
      old.textContent = `Interactive Mini Preview — ${pill}`;
      setTimeout(()=> old.textContent = 'Interactive Mini Preview', 1300);
    });

    renderRows();
    updateTotal();
  </script>
</body>
</html>
