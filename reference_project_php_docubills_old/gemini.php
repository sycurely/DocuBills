<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocuBills | World's 1st Free Custom Invoice Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .gradient-bg { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .step-inactive { display: none; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-sans">

    <nav class="flex items-center justify-between px-8 py-6 bg-white border-b border-slate-100 sticky top-0 z-50">
        <div class="flex items-center gap-2">
            <div class="bg-indigo-600 p-2 rounded-lg text-white">
                <i data-lucide="file-text"></i>
            </div>
            <span class="text-2xl font-bold tracking-tight text-indigo-900">DocuBills</span>
        </div>
        <div class="hidden md:flex gap-8 font-medium text-slate-600">
            <a href="#" class="hover:text-indigo-600">Features</a>
            <a href="#" class="hover:text-indigo-600">Templates</a>
            <a href="#" class="hover:text-indigo-600">Pricing</a>
        </div>
        <div class="flex gap-4 items-center">
            <a href="login.php" class="text-slate-600 font-medium hover:text-indigo-600">Login</a>
            <a href="#demo" class="bg-indigo-600 text-white px-5 py-2.5 rounded-full font-semibold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">Get Started</a>
        </div>
    </nav>

    <header class="max-w-7xl mx-auto px-8 py-20 text-center">
        <h1 class="text-5xl md:text-7xl font-extrabold text-slate-900 mb-6 tracking-tight">
            Invoicing without <span class="text-indigo-600">Limitations.</span>
        </h1>
        <p class="text-xl text-slate-600 max-w-3xl mx-auto mb-10 leading-relaxed">
            The world's first 100% FREE custom invoice generator. Upload Excel, sync Google Sheets, and define your own pricing logic. No more rigid templates.
        </p>
        
        <div id="demo" class="max-w-4xl mx-auto border border-slate-200 rounded-2xl shadow-2xl bg-white overflow-hidden">
            <div class="bg-slate-100 border-b border-slate-200 px-6 py-3 flex items-center justify-between">
                <div class="flex gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                </div>
                <span class="text-xs font-mono text-slate-400 uppercase tracking-widest">Real-time Simulation</span>
                <div></div>
            </div>

            <div class="p-8 min-h-[400px] flex flex-col justify-center">
                <div id="step1" class="space-y-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="bg-indigo-100 text-indigo-700 w-8 h-8 rounded-full flex items-center justify-center font-bold">1</span>
                        <h3 class="text-xl font-bold">Import Data Source</h3>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 hover:border-indigo-400 transition cursor-pointer group">
                            <i data-lucide="file-up" class="mx-auto mb-2 text-slate-400 group-hover:text-indigo-600"></i>
                            <p class="text-sm font-semibold">Upload Excel File</p>
                        </div>
                        <div class="border-2 border-indigo-100 bg-indigo-50 rounded-xl p-6">
                            <i data-lucide="link" class="mx-auto mb-2 text-indigo-600"></i>
                            <input type="text" placeholder="Paste Google Sheet URL" class="w-full text-xs p-2 rounded border border-indigo-200">
                        </div>
                    </div>
                    <button onclick="nextStep(2)" class="bg-indigo-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-indigo-700 transition">Fetch Data Columns</button>
                </div>

                <div id="step2" class="step-inactive space-y-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="bg-indigo-100 text-indigo-700 w-8 h-8 rounded-full flex items-center justify-center font-bold">2</span>
                        <h3 class="text-xl font-bold">Map Your Custom Columns</h3>
                    </div>
                    <p class="text-sm text-slate-500">We found 5 columns in your sheet. Which one represents the <span class="font-bold text-slate-800">Price</span>?</p>
                    <div class="flex flex-wrap gap-3 justify-center">
                        <button class="px-4 py-2 border rounded-md text-sm">Item Name</button>
                        <button class="px-4 py-2 border rounded-md text-sm">Description</button>
                        <button class="px-4 py-2 border-2 border-indigo-600 bg-indigo-50 text-indigo-700 font-bold rounded-md text-sm">Hourly_Rate_Final</button>
                        <button class="px-4 py-2 border rounded-md text-sm">Quantity</button>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-lg text-left text-xs font-mono text-slate-600">
                        Logic Applied: [Total] = [Quantity] * [Hourly_Rate_Final]
                    </div>
                    <button onclick="nextStep(3)" class="bg-indigo-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-indigo-700 transition">Preview Custom Invoice</button>
                </div>

                <div id="step3" class="step-inactive space-y-4 py-10">
                    <i data-lucide="party-popper" class="mx-auto text-indigo-600 w-16 h-16"></i>
                    <h3 class="text-3xl font-extrabold">Ready to Send?</h3>
                    <p class="text-slate-600">Your custom invoice is mapped and ready. Sign up now to send, get paid via Stripe, and set recurring reminders.</p>
                    <div class="pt-4">
                        <a href="signup.php" class="bg-green-500 text-white px-10 py-4 rounded-full text-lg font-bold hover:bg-green-600 transition shadow-xl">Join DocuBills for FREE</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="bg-white py-24 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4">Why DocuBills is Different</h2>
                <p class="text-slate-500">Breaking the limits of traditional invoicing software.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-12">
                <div class="space-y-4">
                    <div class="bg-indigo-100 w-12 h-12 rounded-lg flex items-center justify-center text-indigo-600">
                        <i data-lucide="zap"></i>
                    </div>
                    <h4 class="text-xl font-bold">Dynamic Mapping</h4>
                    <p class="text-slate-600 leading-relaxed">Don't be forced into "Price" and "Qty". Map any column from your Excel sheet as the billing driver.</p>
                </div>
                <div class="space-y-4">
                    <div class="bg-indigo-100 w-12 h-12 rounded-lg flex items-center justify-center text-indigo-600">
                        <i data-lucide="credit-card"></i>
                    </div>
                    <h4 class="text-xl font-bold">Integrated Payments</h4>
                    <p class="text-slate-600 leading-relaxed">Native Stripe integration and bank detail sharing so your clients can pay you instantly.</p>
                </div>
                <div class="space-y-4">
                    <div class="bg-indigo-100 w-12 h-12 rounded-lg flex items-center justify-center text-indigo-600">
                        <i data-lucide="refresh-cw"></i>
                    </div>
                    <h4 class="text-xl font-bold">Recurring Bliss</h4>
                    <p class="text-slate-600 leading-relaxed">Set your email cadence and templates. Let DocuBills handle the follow-ups while you sleep.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-12 text-center text-slate-400 border-t border-slate-200">
        <p>© 2024 DocuBills. The world's only truly flexible invoice generator.</p>
    </footer>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Simple Step Logic
        function nextStep(step) {
            document.getElementById('step1').classList.add('step-inactive');
            document.getElementById('step2').classList.add('step-inactive');
            document.getElementById('step3').classList.add('step-inactive');
            
            document.getElementById('step' + step).classList.remove('step-inactive');
        }
    </script>
</body>
</html>