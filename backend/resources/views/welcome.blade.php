<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Social - API & Developer Console</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: radial-gradient(circle at 50% 0%, #1e1136 0%, #0b0b12 55%, #050508 100%);
            min-height: 100vh;
        }
        .glass {
            background: rgba(22, 21, 38, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card {
            background: rgba(28, 26, 48, 0.55);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .code-box {
            background: #090812;
            border: 1px solid rgba(139, 92, 246, 0.2);
        }
    </style>
</head>
<body class="text-zinc-100 font-sans antialiased py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- Header -->
        <header class="glass rounded-2xl p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-violet-600/15 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-violet-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-violet-500/25 ring-1 ring-white/20">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight bg-gradient-to-r from-white via-zinc-100 to-zinc-400 bg-clip-text text-transparent">Nova Social</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-violet-500/10 text-violet-300 border border-violet-500/20">v1.0 API</span>
                    </div>
                    <p class="text-zinc-400 text-sm mt-0.5">High-Performance Realtime Social Platform Backend</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-300 text-xs font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Backend Active (:8000)
                </div>
                <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-violet-500/10 border border-violet-500/25 text-violet-300 text-xs font-medium">
                    <span>⚡</span> MySQL Connected
                </div>
                <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-500/10 border border-blue-500/25 text-blue-300 text-xs font-medium">
                    <span>🧪</span> 55 Tests Passing
                </div>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="glass-card rounded-2xl p-5 hover:border-violet-500/40 transition">
                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Registered Users</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-white">{{ $stats['users'] ?? 0 }}</span>
                    <span class="text-xs text-emerald-400 font-medium">seeded</span>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-5 hover:border-violet-500/40 transition">
                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Posts & Media</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-white">{{ $stats['posts'] ?? 0 }}</span>
                    <span class="text-xs text-violet-400 font-medium">active</span>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-5 hover:border-violet-500/40 transition">
                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Comments</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-white">{{ $stats['comments'] ?? 0 }}</span>
                    <span class="text-xs text-blue-400 font-medium">threaded</span>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-5 hover:border-violet-500/40 transition">
                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Stories (24h)</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-white">{{ $stats['stories'] ?? 0 }}</span>
                    <span class="text-xs text-pink-400 font-medium">ephemeral</span>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-5 hover:border-violet-500/40 transition col-span-2 sm:col-span-1">
                <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider">Chat Threads</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-3xl font-extrabold text-white">{{ $stats['conversations'] ?? 0 }}</span>
                    <span class="text-xs text-amber-400 font-medium">channels</span>
                </div>
            </div>
        </div>

        <!-- Main Interactive Console -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Column: Interactive Client -->
            <div class="lg:col-span-7 space-y-6">
                
                <div class="glass rounded-2xl p-6 shadow-xl">
                    <div class="flex items-center justify-between pb-4 border-b border-white/5 mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                <span class="text-violet-400">⚡</span> Live API Interactive Playground
                            </h2>
                            <p class="text-xs text-zinc-400 mt-0.5">Test real endpoints with zero setup directly in your browser.</p>
                        </div>
                        <div id="authStatusPill" class="text-xs px-2.5 py-1 rounded-full bg-zinc-800 text-zinc-400 border border-zinc-700">
                            Unauthenticated
                        </div>
                    </div>

                    <!-- Quick Seeded Logins -->
                    <div class="mb-6">
                        <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wider mb-2">1-Click Quick Demo Login:</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <button onclick="quickFill('user@novasocial.test', 'password')" class="px-3 py-2 text-left rounded-xl bg-zinc-800/80 hover:bg-violet-900/30 hover:border-violet-500/40 border border-white/5 transition group">
                                <p class="text-xs font-bold text-white group-hover:text-violet-300">👤 Demo User</p>
                                <p class="text-[11px] text-zinc-400 font-mono truncate">user@novasocial.test</p>
                            </button>
                            <button onclick="quickFill('admin@novasocial.test', 'password')" class="px-3 py-2 text-left rounded-xl bg-zinc-800/80 hover:bg-violet-900/30 hover:border-violet-500/40 border border-white/5 transition group">
                                <p class="text-xs font-bold text-white group-hover:text-violet-300">🛡️ Admin</p>
                                <p class="text-[11px] text-zinc-400 font-mono truncate">admin@novasocial.test</p>
                            </button>
                            <button onclick="quickFill('moderator@novasocial.test', 'password')" class="px-3 py-2 text-left rounded-xl bg-zinc-800/80 hover:bg-violet-900/30 hover:border-violet-500/40 border border-white/5 transition group">
                                <p class="text-xs font-bold text-white group-hover:text-violet-300">⚖️ Moderator</p>
                                <p class="text-[11px] text-zinc-400 font-mono truncate">moderator@novasocial.test</p>
                            </button>
                        </div>
                    </div>

                    <!-- Tab Buttons -->
                    <div class="flex border-b border-white/5 mb-6 gap-2">
                        <button id="tab-login" onclick="switchTab('login')" class="px-4 py-2 text-sm font-semibold border-b-2 border-violet-500 text-violet-400 transition">
                            Login
                        </button>
                        <button id="tab-me" onclick="switchTab('me')" class="px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-zinc-400 hover:text-zinc-200 transition">
                            My Profile (/me)
                        </button>
                        <button id="tab-health" onclick="switchTab('health')" class="px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-zinc-400 hover:text-zinc-200 transition">
                            Health Check
                        </button>
                        <button id="tab-register" onclick="switchTab('register')" class="px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-zinc-400 hover:text-zinc-200 transition">
                            Register
                        </button>
                    </div>

                    <!-- Tab: Login -->
                    <div id="pane-login" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Username or Email</label>
                            <input id="loginInput" type="text" value="user@novasocial.test" placeholder="Username or Email" class="w-full px-3.5 py-2.5 rounded-xl bg-black/40 border border-white/10 text-white font-mono text-sm focus:outline-none focus:border-violet-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Password</label>
                            <input id="passwordInput" type="password" value="password" placeholder="Password" class="w-full px-3.5 py-2.5 rounded-xl bg-black/40 border border-white/10 text-white font-mono text-sm focus:outline-none focus:border-violet-500 transition">
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button onclick="executeLogin()" class="flex-1 py-2.5 px-4 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-semibold text-sm shadow-lg shadow-violet-600/30 transition flex items-center justify-center gap-2">
                                <span>Sign In & Get Token</span>
                            </button>
                            <button onclick="executeLogout()" id="logoutBtn" disabled class="py-2.5 px-4 rounded-xl bg-zinc-800 text-zinc-500 border border-white/5 font-semibold text-sm transition">
                                Logout
                            </button>
                        </div>
                    </div>

                    <!-- Tab: /me -->
                    <div id="pane-me" class="space-y-4 hidden">
                        <p class="text-xs text-zinc-400">Calls <code class="font-mono text-violet-300">GET /api/v1/auth/me</code> using the saved Sanctum token in Authorization header.</p>
                        <button onclick="executeMe()" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-semibold text-sm shadow-lg shadow-violet-600/30 transition flex items-center justify-center gap-2">
                            <span>Fetch Authenticated User (/me)</span>
                        </button>
                    </div>

                    <!-- Tab: Health -->
                    <div id="pane-health" class="space-y-4 hidden">
                        <p class="text-xs text-zinc-400">Calls <code class="font-mono text-violet-300">GET /api/v1/health</code> to inspect API and Database connection health.</p>
                        <button onclick="executeHealth()" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2">
                            <span>Check System Health</span>
                        </button>
                    </div>

                    <!-- Tab: Register -->
                    <div id="pane-register" class="space-y-3 hidden">
                        <div>
                            <label class="block text-xs font-medium text-zinc-300 mb-1">Full Name</label>
                            <input id="regName" type="text" value="New Explorer" class="w-full px-3.5 py-2 rounded-xl bg-black/40 border border-white/10 text-white text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Username</label>
                                <input id="regUsername" type="text" value="explorer_99" class="w-full px-3.5 py-2 rounded-xl bg-black/40 border border-white/10 text-white font-mono text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Email</label>
                                <input id="regEmail" type="email" value="explorer99@novasocial.test" class="w-full px-3.5 py-2 rounded-xl bg-black/40 border border-white/10 text-white font-mono text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Password</label>
                                <input id="regPass" type="password" value="Password123!" class="w-full px-3.5 py-2 rounded-xl bg-black/40 border border-white/10 text-white font-mono text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-300 mb-1">Confirm Password</label>
                                <input id="regPassConfirm" type="password" value="Password123!" class="w-full px-3.5 py-2 rounded-xl bg-black/40 border border-white/10 text-white font-mono text-sm">
                            </div>
                        </div>
                        <button onclick="executeRegister()" class="w-full py-2.5 px-4 mt-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-semibold text-sm shadow-lg shadow-violet-600/30 transition">
                            Create Account & Authenticate
                        </button>
                    </div>

                </div>

                <!-- API Response Viewer -->
                <div class="glass rounded-2xl p-6 shadow-xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider font-mono">Response Payload</span>
                            <span id="responseStatusBadge" class="hidden px-2 py-0.5 rounded text-xs font-mono font-bold"></span>
                        </div>
                        <span id="responseTimeBadge" class="text-xs text-zinc-500 font-mono"></span>
                    </div>

                    <pre id="responseOutput" class="code-box rounded-xl p-4 text-xs font-mono text-zinc-300 overflow-x-auto max-h-72 leading-relaxed whitespace-pre-wrap">// Click any action above to inspect live API response</pre>
                </div>

            </div>

            <!-- Right Column: Demo Accounts & System Info -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- Seeded Accounts Quick Table -->
                <div class="glass rounded-2xl p-6 shadow-xl">
                    <h3 class="text-base font-bold text-white flex items-center gap-2 mb-1">
                        <span>👥</span> Seeded Demo Accounts
                    </h3>
                    <p class="text-xs text-zinc-400 mb-4">All accounts share password: <code class="font-mono text-violet-300 bg-violet-500/10 px-1.5 py-0.5 rounded border border-violet-500/20">password</code></p>

                    <div class="space-y-2.5 text-xs">
                        <div class="p-3 rounded-xl bg-zinc-900/60 border border-white/5 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-white">Nova Administrator</span>
                                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] bg-red-500/15 text-red-400 border border-red-500/30">admin</span>
                                <div class="text-zinc-400 font-mono text-[11px] mt-0.5">admin@novasocial.test</div>
                            </div>
                            <button onclick="quickFill('admin@novasocial.test', 'password')" class="px-2.5 py-1 rounded-lg bg-zinc-800 hover:bg-violet-600 hover:text-white text-zinc-300 text-[11px] font-semibold transition">Use</button>
                        </div>

                        <div class="p-3 rounded-xl bg-zinc-900/60 border border-white/5 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-white">Nova Moderator</span>
                                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] bg-amber-500/15 text-amber-400 border border-amber-500/30">moderator</span>
                                <div class="text-zinc-400 font-mono text-[11px] mt-0.5">moderator@novasocial.test</div>
                            </div>
                            <button onclick="quickFill('moderator@novasocial.test', 'password')" class="px-2.5 py-1 rounded-lg bg-zinc-800 hover:bg-violet-600 hover:text-white text-zinc-300 text-[11px] font-semibold transition">Use</button>
                        </div>

                        <div class="p-3 rounded-xl bg-zinc-900/60 border border-white/5 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-white">Nova Demo User</span>
                                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] bg-violet-500/15 text-violet-400 border border-violet-500/30">user</span>
                                <div class="text-zinc-400 font-mono text-[11px] mt-0.5">user@novasocial.test</div>
                            </div>
                            <button onclick="quickFill('user@novasocial.test', 'password')" class="px-2.5 py-1 rounded-lg bg-zinc-800 hover:bg-violet-600 hover:text-white text-zinc-300 text-[11px] font-semibold transition">Use</button>
                        </div>

                        <div class="p-3 rounded-xl bg-zinc-900/60 border border-white/5 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-white">Elena Rostova</span>
                                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">designer</span>
                                <div class="text-zinc-400 font-mono text-[11px] mt-0.5">elena@novasocial.test</div>
                            </div>
                            <button onclick="quickFill('elena@novasocial.test', 'password')" class="px-2.5 py-1 rounded-lg bg-zinc-800 hover:bg-violet-600 hover:text-white text-zinc-300 text-[11px] font-semibold transition">Use</button>
                        </div>
                    </div>
                </div>

                <!-- Roadmap / Architecture Status -->
                <div class="glass rounded-2xl p-6 shadow-xl">
                    <h3 class="text-base font-bold text-white flex items-center gap-2 mb-3">
                        <span>🗺️</span> Roadmap & Capabilities
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-start gap-3 p-2.5 rounded-xl bg-emerald-500/5 border border-emerald-500/20">
                            <span class="text-emerald-400 font-bold">✓</span>
                            <div>
                                <p class="font-semibold text-emerald-300">Phase 1.1: Backend Foundation & DB</p>
                                <p class="text-zinc-400 text-[11px]">21 Migrations, Polymorphic Relations, Health Check API</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-2.5 rounded-xl bg-emerald-500/5 border border-emerald-500/20">
                            <span class="text-emerald-400 font-bold">✓</span>
                            <div>
                                <p class="font-semibold text-emerald-300">Phase 2: Authentication Engine</p>
                                <p class="text-zinc-400 text-[11px]">Sanctum Tokens, Register, Login, Rate Limiting, Forgot/Reset Password</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-2.5 rounded-xl bg-violet-500/5 border border-violet-500/20">
                            <span class="text-violet-400 font-bold">⏳</span>
                            <div>
                                <p class="font-semibold text-violet-300">Phase 1.2: Next.js Frontend</p>
                                <p class="text-zinc-400 text-[11px]">Tailwind UI, Feed, Stories & Auth client in /frontend</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-2.5 rounded-xl bg-zinc-800/40 border border-white/5">
                            <span class="text-zinc-500 font-bold">○</span>
                            <div>
                                <p class="font-semibold text-zinc-300">Phase 3: Profiles & Privacy</p>
                                <p class="text-zinc-500 text-[11px]">Avatars, Cover photos, Bio updates, Account privacy toggle</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        let authToken = localStorage.getItem('nova_token') || null;

        function updateAuthStatus() {
            const pill = document.getElementById('authStatusPill');
            const logoutBtn = document.getElementById('logoutBtn');
            if (authToken) {
                pill.textContent = '● Authenticated (Token Saved)';
                pill.className = 'text-xs px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-300 border border-emerald-500/25';
                logoutBtn.disabled = false;
                logoutBtn.className = 'py-2.5 px-4 rounded-xl bg-rose-900/30 text-rose-300 border border-rose-500/30 hover:bg-rose-900/50 font-semibold text-sm transition';
            } else {
                pill.textContent = 'Unauthenticated';
                pill.className = 'text-xs px-2.5 py-1 rounded-full bg-zinc-800 text-zinc-400 border border-zinc-700';
                logoutBtn.disabled = true;
                logoutBtn.className = 'py-2.5 px-4 rounded-xl bg-zinc-800 text-zinc-500 border border-white/5 font-semibold text-sm transition';
            }
        }

        updateAuthStatus();

        function switchTab(tab) {
            ['login', 'me', 'health', 'register'].forEach(t => {
                document.getElementById('pane-' + t).classList.add('hidden');
                const btn = document.getElementById('tab-' + t);
                btn.className = 'px-4 py-2 text-sm font-semibold border-b-2 border-transparent text-zinc-400 hover:text-zinc-200 transition';
            });
            document.getElementById('pane-' + tab).classList.remove('hidden');
            const activeBtn = document.getElementById('tab-' + tab);
            activeBtn.className = 'px-4 py-2 text-sm font-semibold border-b-2 border-violet-500 text-violet-400 transition';
        }

        function quickFill(email, pass) {
            switchTab('login');
            document.getElementById('loginInput').value = email;
            document.getElementById('passwordInput').value = pass;
            executeLogin();
        }

        function renderResponse(status, time, data) {
            const badge = document.getElementById('responseStatusBadge');
            const timeBadge = document.getElementById('responseTimeBadge');
            const output = document.getElementById('responseOutput');

            badge.classList.remove('hidden', 'bg-emerald-500/20', 'text-emerald-300', 'border-emerald-500/30', 'bg-rose-500/20', 'text-rose-300', 'border-rose-500/30');
            badge.textContent = status + (status >= 200 && status < 300 ? ' OK' : ' Error');
            if (status >= 200 && status < 300) {
                badge.classList.add('bg-emerald-500/20', 'text-emerald-300', 'border', 'border-emerald-500/30');
            } else {
                badge.classList.add('bg-rose-500/20', 'text-rose-300', 'border', 'border-rose-500/30');
            }

            timeBadge.textContent = time + ' ms';
            output.textContent = JSON.stringify(data, null, 2);
        }

        async function executeHealth() {
            const start = performance.now();
            try {
                const res = await fetch('/api/v1/health');
                const data = await res.json();
                renderResponse(res.status, Math.round(performance.now() - start), data);
            } catch (err) {
                renderResponse(500, Math.round(performance.now() - start), { error: err.message });
            }
        }

        async function executeLogin() {
            const login = document.getElementById('loginInput').value;
            const password = document.getElementById('passwordInput').value;
            const start = performance.now();

            try {
                const res = await fetch('/api/v1/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ login, password })
                });
                const data = await res.json();
                renderResponse(res.status, Math.round(performance.now() - start), data);

                if (res.ok && data.token) {
                    authToken = data.token;
                    localStorage.setItem('nova_token', authToken);
                    updateAuthStatus();
                }
            } catch (err) {
                renderResponse(500, Math.round(performance.now() - start), { error: err.message });
            }
        }

        async function executeMe() {
            if (!authToken) {
                renderResponse(401, 0, { message: 'No auth token found. Please login first!' });
                return;
            }
            const start = performance.now();
            try {
                const res = await fetch('/api/v1/auth/me', {
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                renderResponse(res.status, Math.round(performance.now() - start), data);
            } catch (err) {
                renderResponse(500, Math.round(performance.now() - start), { error: err.message });
            }
        }

        async function executeLogout() {
            if (!authToken) return;
            const start = performance.now();
            try {
                const res = await fetch('/api/v1/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                renderResponse(res.status, Math.round(performance.now() - start), data);
                authToken = null;
                localStorage.removeItem('nova_token');
                updateAuthStatus();
            } catch (err) {
                renderResponse(500, Math.round(performance.now() - start), { error: err.message });
            }
        }

        async function executeRegister() {
            const body = {
                name: document.getElementById('regName').value,
                username: document.getElementById('regUsername').value,
                email: document.getElementById('regEmail').value,
                password: document.getElementById('regPass').value,
                password_confirmation: document.getElementById('regPassConfirm').value
            };
            const start = performance.now();
            try {
                const res = await fetch('/api/v1/auth/register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(body)
                });
                const data = await res.json();
                renderResponse(res.status, Math.round(performance.now() - start), data);
                if (res.ok && data.token) {
                    authToken = data.token;
                    localStorage.setItem('nova_token', authToken);
                    updateAuthStatus();
                }
            } catch (err) {
                renderResponse(500, Math.round(performance.now() - start), { error: err.message });
            }
        }
    </script>
</body>
</html>
