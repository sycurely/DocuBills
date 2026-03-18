<?php /* bebo.php — OPTION B (White / Premium / Comparison-led) */ ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>DocuBills — The fastest way to invoice from spreadsheets</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg:#ffffff;
      --ink:#0b1220;
      --muted: rgba(11,18,32,.68);
      --muted2: rgba(11,18,32,.52);
      --line: rgba(11,18,32,.10);
      --card:#ffffff;
      --soft: rgba(11,18,32,.04);
      --soft2: rgba(11,18,32,.06);
      --accent:#2563EB;   /* blue */
      --accent2:#7C3AED;  /* purple */
      --good:#12B981;
      --radius:18px;
      --max:1160px;
      --shadow: 0 18px 50px rgba(11,18,32,.10);
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color:var(--ink);
      background:
        radial-gradient(900px 500px at 20% 10%, rgba(37,99,235,.10), transparent 55%),
        radial-gradient(900px 500px at 80% 0%, rgba(124,58,237,.09), transparent 55%),
        #fff;
      overflow-x:hidden;
    }
    a{text-decoration:none;color:inherit}
    .wrap{max-width:var(--max); margin:0 auto; padding:0 18px;}
    .btn{
      display:inline-flex; align-items:center; gap:10px;
      padding:12px 16px;
      border-radius: 14px;
      border:1px solid transparent;
      font-weight:800;
      cursor:pointer;
      transition:.18s ease;
      white-space:nowrap;
    }
    .btn.primary{
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      color:#fff;
      box-shadow: 0 14px 36px rgba(37,99,235,.20);
    }
    .btn.primary:hover{transform:translateY(-1px); filter:brightness(1.02)}
    .btn.ghost{
      background:#fff;
      border-color: var(--line);
      color: var(--ink);
    }
    .btn.ghost:hover{background: var(--soft)}
    .pill{
      display:inline-flex; align-items:center; gap:10px;
      padding:8px 12px;
      border-radius: 999px;
      background: rgba(37,99,235,.08);
      border: 1px solid rgba(37,99,235,.18);
      color: rgba(37,99,235,.92);
      font-weight:900;
    }

    /* NAV */
    .nav{
      position:sticky; top:0; z-index:50;
      background: rgba(255,255,255,.85);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--line);
    }
    .navin{
      display:flex; align-items:center; justify-content:space-between;
      padding:12px 0;
    }
    .brand{display:flex; align-items:center; gap:10px; font-weight:950}
    .mark{
      width:34px;height:34px;border-radius: 14px;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      display:grid; place-items:center;
      color:#fff; font-weight:950;
      box-shadow: 0 10px 20px rgba(124,58,237,.18);
    }
    .links{display:flex; gap:16px; align-items:center; color:var(--muted); font-weight:800}
    .links a{padding:8px 10px; border-radius: 12px}
    .links a:hover{background: var(--soft); color:var(--ink)}
    .cta{display:flex; gap:10px; align-items:center}
    .hide-sm{display:none}

    /* HERO */
    .hero{padding:56px 0 24px}
    .heroGrid{
      display:grid;
      grid-template-columns: 1.05fr .95fr;
      gap:22px;
      align-items:stretch;
    }
    h1{
      margin:14px 0 10px;
      font-size: clamp(34px, 4.4vw, 56px);
      line-height:1.05;
      letter-spacing:-.9px;
    }
    .sub{
      margin:0 0 18px;
      color:var(--muted);
      line-height:1.65;
      max-width: 60ch;
      font-weight:600;
      font-size:16px;
    }
    .heroActions{display:flex; gap:12px; flex-wrap:wrap}
    .trust{
      display:flex; gap:12px; flex-wrap:wrap;
      margin-top:16px;
      color:var(--muted2);
      font-weight:700;
    }
    .trust span{display:inline-flex; align-items:center; gap:8px}
    .dot{width:8px;height:8px;border-radius:999px;background:var(--good); box-shadow:0 0 0 3px rgba(18,185,129,.14)}

    /* RIGHT PANEL */
    .panel{
      background: var(--card);
      border:1px solid var(--line);
      border-radius: 26px;
      box-shadow: var(--shadow);
      overflow:hidden;
    }
    .panelTop{
      padding:14px 16px;
      border-bottom: 1px solid var(--line);
      display:flex; justify-content:space-between; align-items:center; gap:12px;
      background: linear-gradient(180deg, rgba(11,18,32,.02), rgba(11,18,32,.00));
    }
    .panelTop b{font-weight:950}
    .toggle{
      display:flex; background: var(--soft); border:1px solid var(--line);
      border-radius: 999px; overflow:hidden;
    }
    .toggle button{
      border:0; background:transparent; cursor:pointer;
      padding:9px 12px; font-weight:950; color:var(--muted);
    }
    .toggle button.active{
      background:#fff;
      color:var(--ink);
      box-shadow: 0 10px 26px rgba(11,18,32,.06);
    }
    .panelBody{padding:14px 16px}
    .compare{
      display:grid; grid-template-columns: 1fr 1fr; gap:12px;
    }
    .box{
      border-radius: 18px;
      border:1px solid var(--line);
      background: var(--soft);
      padding:12px;
      min-height: 150px;
    }
    .box h3{margin:0 0 6px; font-size:14px; font-weight:950}
    .box ul{margin:0; padding-left:18px; color:var(--muted); line-height:1.7; font-weight:650}
    .box.good{background: rgba(18,185,129,.08); border-color: rgba(18,185,129,.18)}
    .box.good h3{color: rgba(18,185,129,.95)}
    .miniStep{
      margin-top:12px;
      border-radius: 18px;
      border:1px solid var(--line);
      background:#fff;
      padding:12px;
    }
    .stepRow{
      display:flex; gap:8px; flex-wrap:wrap; align-items:center;
      margin-top:8px;
    }
    .chip{
      display:inline-flex; align-items:center; gap:8px;
      padding:8px 10px;
      border-radius: 999px;
      border:1px solid var(--line);
      background: #fff;
      font-weight:850;
      color: var(--muted);
      cursor:pointer;
      transition:.15s ease;
      user-select:none;
    }
    .chip.active{
      color: var(--ink);
      border-color: rgba(37,99,235,.24);
      background: rgba(37,99,235,.08);
    }
    .hint{margin-top:10px; color:var(--muted); line-height:1.65; font-weight:650}

    /* SECTIONS */
    section{padding:56px 0}
    .title{font-size: 28px; margin:0 0 10px; letter-spacing:-.7px; font-weight:950}
    .desc{margin:0 0 18px; color:var(--muted); line-height:1.7; font-weight:600; max-width: 78ch}
    .grid3{display:grid; grid-template-columns: repeat(3, 1fr); gap:14px}
    .card{
      background:#fff;
      border:1px solid var(--line);
      border-radius: 22px;
      box-shadow: 0 14px 40px rgba(11,18,32,.07);
      padding:16px;
      transition:.15s ease;
      min-height: 150px;
    }
    .card:hover{transform: translateY(-2px)}
    .icon{
      width:44px;height:44px;border-radius: 16px;
      background: rgba(37,99,235,.10);
      border:1px solid rgba(37,99,235,.18);
      display:grid; place-items:center;
      margin-bottom:10px;
      font-weight:950;
      color: rgba(37,99,235,.92);
    }
    .card h4{margin:0 0 6px; font-size:15px; font-weight:950}
    .card p{margin:0; color:var(--muted); line-height:1.6; font-weight:650; font-size:13px}

    /* DEMO PLACEHOLDER */
    .demo{
      border-radius: 26px;
      border:1px dashed rgba(11,18,32,.18);
      background: linear-gradient(180deg, rgba(11,18,32,.02), rgba(11,18,32,.00));
      padding:18px;
    }
    .mount{
      margin-top:12px;
      min-height: 240px;
      border-radius: 18px;
      border:1px solid var(--line);
      background:#fff;
      display:grid; place-items:center;
      padding:18px;
      color: var(--muted);
      font-weight:700;
    }

    /* FOOTER */
    footer{
      padding:34px 0 44px;
      border-top:1px solid var(--line);
      color:var(--muted);
      font-weight:650;
    }
    .foot{display:flex; justify-content:space-between; align-items:center; gap:14px; flex-wrap:wrap}
    .foot a{color:var(--muted); font-weight:800}
    .foot a:hover{color:var(--ink)}

    @media (max-width:980px){
      .heroGrid{grid-template-columns:1fr}
      .grid3{grid-template-columns:1fr}
      .compare{grid-template-columns:1fr}
      .hide-sm{display:none}
    }
    @media (min-width:980px){
      .hide-sm{display:flex}
    }
  </style>
</head>

<body>
  <div class="nav">
    <div class="wrap">
      <div class="navin">
        <a class="brand" href="#top">
          <div class="mark">D</div>
          <span>DocuBills</span>
        </a>

        <div class="links hide-sm">
          <a href="#why">Why it wins</a>
          <a href="#features">Features</a>
          <a href="#interactive-demo">Interactive Demo</a>
          <a href="#faq">FAQ</a>
        </div>

        <div class="cta">
          <a class="btn ghost" href="login.php">Login</a>
          <a class="btn primary" href="register.php">Start free →</a>
        </div>
      </div>
    </div>
  </div>

  <div id="top" class="hero">
    <div class="wrap">
      <div class="heroGrid">
        <div>
          <span class="pill">3 steps. No mapping. Full control.</span>
          <h1>Invoice directly from your spreadsheet — in minutes, not hours.</h1>
          <p class="sub">
            Upload Excel or paste a Google Sheet link. Choose the total column, deselect any rows/columns you don’t want printed,
            add bank info + currency on the preview, then export PDF and automate reminders.
          </p>

          <div class="heroActions">
            <a class="btn primary" href="register.php">Create my first invoice →</a>
            <a class="btn ghost" href="#interactive-demo">View interactive demo</a>
          </div>

          <div class="trust">
            <span><span class="dot"></span> No credit card</span>
            <span><span class="dot"></span> PDF export</span>
            <span><span class="dot"></span> Recurring + reminders</span>
            <span><span class="dot"></span> Admin roles & permissions</span>
          </div>
        </div>

        <div class="panel">
          <div class="panelTop">
            <b>Before vs After</b>
            <div class="toggle" role="tablist" aria-label="Comparison toggle">
              <button class="active" id="oldBtn" aria-selected="true">Old way</button>
              <button id="newBtn" aria-selected="false">DocuBills</button>
            </div>
          </div>

          <div class="panelBody">
            <div class="compare">
              <div class="box" id="leftBox">
                <h3>Manual invoicing</h3>
                <ul>
                  <li>Confusing column mapping screens</li>
                  <li>Fix totals manually</li>
                  <li>Accidental rows get printed</li>
                  <li>Follow-ups done by hand</li>
                </ul>
              </div>

              <div class="box good" id="rightBox">
                <h3>DocuBills flow</h3>
                <ul>
                  <li>Upload sheet (Excel/URL)</li>
                  <li>Pick the total column</li>
                  <li>Uncheck rows/columns to hide</li>
                  <li>Reminders + templates inside app</li>
                </ul>
              </div>
            </div>

            <div class="miniStep">
              <div style="font-weight:950">Click through the 3 steps:</div>
              <div class="stepRow">
                <div class="chip active" data-s="1">1) Upload</div>
                <div class="chip" data-s="2">2) Total Column</div>
                <div class="chip" data-s="3">3) Preview & PDF</div>
              </div>
              <div class="hint" id="hint">
                Upload Excel or paste a Google Sheet link — DocuBills reads headers + rows automatically.
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>

  <section id="why">
    <div class="wrap">
      <h2 class="title">Why people choose DocuBills</h2>
      <p class="desc">
        It’s built around the real pain: spreadsheets are messy — invoicing shouldn’t be.
        DocuBills gives you a preview-first experience where you control exactly what prints.
      </p>

      <div class="grid3">
        <div class="card">
          <div class="icon">📥</div>
          <h4>Upload or paste URL</h4>
          <p>No format drama. Excel upload or Google Sheet link — both work.</p>
        </div>
        <div class="card">
          <div class="icon">✅</div>
          <h4>Hide mistakes instantly</h4>
          <p>Uncheck any row/column and it won’t appear on the final PDF invoice.</p>
        </div>
        <div class="card">
          <div class="icon">📧</div>
          <h4>Stop manual follow-ups</h4>
          <p>Set your reminder cadence and edit your email templates inside the app.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="features">
    <div class="wrap">
      <h2 class="title">Everything happens from the Preview page</h2>
      <p class="desc">
        Recurring invoices, bank info edits, currency selection, and reminders — all controlled where the invoice becomes real.
      </p>

      <div class="grid3">
        <div class="card"><div class="icon">🧮</div><h4>Pick the total column</h4><p>Different sheet styles? No problem. Choose what drives totals.</p></div>
        <div class="card"><div class="icon">🏦</div><h4>Bank info + currency</h4><p>Pull settings, edit inline, print cleanly on the PDF.</p></div>
        <div class="card"><div class="icon">🔁</div><h4>Recurring invoices</h4><p>Enable recurring directly from preview, keep cadence consistent.</p></div>
        <div class="card"><div class="icon">📌</div><h4>Manage invoices</h4><p>Statuses, logs, mark paid/unpaid, all in one place.</p></div>
        <div class="card"><div class="icon">🔔</div><h4>Auto notifications</h4><p>Status changes trigger emails to relevant parties instantly.</p></div>
        <div class="card"><div class="icon">🛡️</div><h4>Roles & permissions</h4><p>Admins can control every feature with granular access.</p></div>
      </div>

      <div style="margin-top:18px; display:flex; gap:12px; flex-wrap:wrap">
        <a class="btn primary" href="register.php">Start free →</a>
        <a class="btn ghost" href="#interactive-demo">See demo section</a>
      </div>
    </div>
  </section>

  <section id="interactive-demo">
    <div class="wrap">
      <h2 class="title">Interactive Demo</h2>
      <p class="desc">Reserved space — you will share the real demo code later and we’ll mount it here.</p>
      <div class="demo">
        <div style="font-weight:950">Drop-in Mount:</div>
        <div class="mount" id="demoMount">
          Your Interactive Demo will be placed here (replace #demoMount content).
        </div>
      </div>
    </div>
  </section>

  <section id="faq">
    <div class="wrap">
      <h2 class="title">FAQ</h2>
      <p class="desc">Last-mile questions before signup.</p>

      <div class="grid3">
        <div class="card">
          <h4>Do I have to map columns?</h4>
          <p>No. You select the total column and hide what you don’t want — no mapping wizard.</p>
        </div>
        <div class="card">
          <h4>Can I edit bank info per invoice?</h4>
          <p>Yes. Stored in settings, editable on preview, printed to PDF.</p>
        </div>
        <div class="card">
          <h4>Can reminders be customized?</h4>
          <p>Yes. Choose cadence and template content inside DocuBills.</p>
        </div>
      </div>

      <div style="margin-top:18px; display:flex; gap:12px; flex-wrap:wrap">
        <a class="btn primary" href="register.php">Create account →</a>
        <a class="btn ghost" href="#top">Back to top</a>
      </div>
    </div>
  </section>

  <footer>
    <div class="wrap">
      <div class="foot">
        <div style="display:flex; align-items:center; gap:10px">
          <div class="mark" style="width:30px;height:30px;border-radius:12px">D</div>
          <div style="font-weight:950">DocuBills</div>
          <div>© <?= date('Y') ?></div>
        </div>
        <div style="display:flex; gap:14px; flex-wrap:wrap">
          <a href="login.php">Login</a>
          <a href="register.php">Sign up</a>
          <a href="#features">Features</a>
          <a href="#interactive-demo">Demo</a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // Smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(a=>{
      a.addEventListener('click', (e)=>{
        const id = a.getAttribute('href');
        const el = document.querySelector(id);
        if(el){
          e.preventDefault();
          el.scrollIntoView({behavior:'smooth', block:'start'});
        }
      });
    });

    // Before/After toggle
    const oldBtn = document.getElementById('oldBtn');
    const newBtn = document.getElementById('newBtn');
    const leftBox = document.getElementById('leftBox');
    const rightBox = document.getElementById('rightBox');

    function setMode(mode){
      if(mode==='old'){
        oldBtn.classList.add('active'); newBtn.classList.remove('active');
        oldBtn.setAttribute('aria-selected','true'); newBtn.setAttribute('aria-selected','false');
        leftBox.style.opacity = "1";
        rightBox.style.opacity = ".55";
      }else{
        newBtn.classList.add('active'); oldBtn.classList.remove('active');
        newBtn.setAttribute('aria-selected','true'); oldBtn.setAttribute('aria-selected','false');
        rightBox.style.opacity = "1";
        leftBox.style.opacity = ".55";
      }
    }
    oldBtn.addEventListener('click', ()=>setMode('old'));
    newBtn.addEventListener('click', ()=>setMode('new'));
    setMode('new');

    // Step chips
    const hint = document.getElementById('hint');
    const chips = document.querySelectorAll('.chip');
    const copy = {
      1: "Upload Excel or paste a Google Sheet link — DocuBills reads headers + rows automatically.",
      2: "Select which column calculates the invoice total — totals update instantly.",
      3: "Uncheck rows/columns you don’t want printed, pick currency/bank info, then export PDF."
    };
    chips.forEach(c=>{
      c.addEventListener('click', ()=>{
        chips.forEach(x=>x.classList.remove('active'));
        c.classList.add('active');
        hint.textContent = copy[c.dataset.s];
      });
    });
  </script>
</body>
</html>
