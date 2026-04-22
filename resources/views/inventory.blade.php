<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Inventory Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet" />
    <style>
        /* ─── Reset & Base ─────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0b0d0f;
            --surface:   #111417;
            --border:    #1e2328;
            --border-hi: #2a3040;
            --cyan:      #00e5ff;
            --cyan-dim:  #00b4c8;
            --cyan-glow: rgba(0, 229, 255, 0.12);
            --text:      #c8d0dc;
            --text-dim:  #5a6478;
            --text-hi:   #eef2f8;
            --green:     #00ff9d;
            --amber:     #ffb800;
            --red:       #ff4d6a;
            --mono:      'Space Mono', monospace;
            --sans:      'Syne', sans-serif;
        }

        html, body {
            height: 100%;
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
            font-size: 15px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* Scanline texture overlay */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(0,0,0,0.03) 2px,
                rgba(0,0,0,0.03) 4px
            );
            pointer-events: none;
            z-index: 999;
        }

        /* ─── Layout ────────────────────────────────────────── */
        .shell {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 24px 80px;
        }

        /* ─── Header ────────────────────────────────────────── */
        header {
            padding: 40px 0 48px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 48px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
        }

        .logo {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .logo-eyebrow {
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: 0.2em;
            color: var(--cyan);
            text-transform: uppercase;
        }

        .logo-title {
            font-family: var(--sans);
            font-size: 26px;
            font-weight: 800;
            color: var(--text-hi);
            letter-spacing: -0.02em;
            line-height: 1;
        }

        .logo-title span {
            color: var(--cyan);
        }

        .header-meta {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text-dim);
            text-align: right;
            line-height: 1.8;
        }

        .status-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--green);
            margin-right: 5px;
            vertical-align: middle;
            box-shadow: 0 0 6px var(--green);
            animation: pulse 2.4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.35; }
        }

        /* ─── Search Block ──────────────────────────────────── */
        .search-block {
            margin-bottom: 48px;
        }

        .search-label {
            font-family: var(--mono);
            font-size: 11px;
            letter-spacing: 0.15em;
            color: var(--text-dim);
            text-transform: uppercase;
            margin-bottom: 12px;
            display: block;
        }

        .search-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-prompt {
            position: absolute;
            left: 20px;
            font-family: var(--mono);
            font-size: 14px;
            color: var(--cyan);
            pointer-events: none;
            user-select: none;
        }

        #search-input {
            width: 100%;
            background: var(--surface);
            border: 1px solid var(--border-hi);
            color: var(--text-hi);
            font-family: var(--mono);
            font-size: 15px;
            padding: 18px 140px 18px 48px;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            caret-color: var(--cyan);
        }

        #search-input::placeholder {
            color: var(--text-dim);
        }

        #search-input:focus {
            border-color: var(--cyan-dim);
            box-shadow: 0 0 0 3px var(--cyan-glow), inset 0 0 20px rgba(0,229,255,0.03);
        }

        #search-btn {
            position: absolute;
            right: 8px;
            background: var(--cyan);
            color: #000;
            border: none;
            border-radius: 3px;
            font-family: var(--mono);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 10px 18px;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
        }

        #search-btn:hover  { background: #33ecff; }
        #search-btn:active { transform: scale(0.97); }
        #search-btn:disabled {
            background: var(--border-hi);
            color: var(--text-dim);
            cursor: not-allowed;
        }

        /* Example queries */
        .examples {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .example-chip {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text-dim);
            border: 1px solid var(--border);
            border-radius: 2px;
            padding: 4px 10px;
            cursor: pointer;
            transition: color 0.15s, border-color 0.15s;
            background: transparent;
        }

        .example-chip:hover {
            color: var(--cyan);
            border-color: var(--cyan-dim);
        }

        /* ─── Results Area ──────────────────────────────────── */
        #results-area { min-height: 200px; }

        /* AI Parse Summary */
        #parse-banner {
            display: none;
            background: rgba(0, 229, 255, 0.04);
            border: 1px solid var(--border-hi);
            border-left: 3px solid var(--cyan);
            border-radius: 4px;
            padding: 14px 18px;
            margin-bottom: 28px;
            font-family: var(--mono);
            font-size: 12px;
            color: var(--text-dim);
            line-height: 1.7;
        }

        #parse-banner strong {
            color: var(--cyan);
            font-weight: 700;
        }

        /* Results header row */
        .results-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .results-count {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text-dim);
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .results-count strong {
            color: var(--text-hi);
            font-size: 13px;
        }

        /* ─── Cards Grid ────────────────────────────────────── */
        #cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 16px;
        }

        .item-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 22px;
            transition: border-color 0.2s, transform 0.2s, box-shadow 0.2s;
            animation: cardIn 0.3s ease both;
        }

        .item-card:hover {
            border-color: var(--border-hi);
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
        }

        .card-name {
            font-family: var(--sans);
            font-size: 16px;
            font-weight: 600;
            color: var(--text-hi);
            line-height: 1.3;
        }

        .card-id {
            font-family: var(--mono);
            font-size: 10px;
            color: var(--text-dim);
            margin-top: 3px;
        }

        /* Status badge */
        .badge {
            font-family: var(--mono);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 2px;
            flex-shrink: 0;
        }

        .badge-available   { background: rgba(0,255,157,0.1); color: var(--green); border: 1px solid rgba(0,255,157,0.2); }
        .badge-unavailable { background: rgba(255,77,106,0.1); color: var(--red);   border: 1px solid rgba(255,77,106,0.2); }
        .badge-maintenance { background: rgba(255,184,0,0.1);  color: var(--amber); border: 1px solid rgba(255,184,0,0.2); }

        /* Category tag */
        .card-category {
            display: inline-block;
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--cyan-dim);
            margin-bottom: 14px;
        }

        /* Attributes table */
        .attrs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 16px;
        }

        .attr-row {
            display: contents;
        }

        .attr-key {
            font-family: var(--mono);
            font-size: 10px;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 3px 0;
            align-self: center;
        }

        .attr-val {
            font-family: var(--mono);
            font-size: 12px;
            color: var(--text);
            padding: 3px 0;
            align-self: center;
        }

        /* Location footer */
        .card-footer {
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid var(--border);
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text-dim);
        }

        .card-footer span { color: var(--text); }

        /* ─── States ────────────────────────────────────────── */

        /* Loading */
        #state-loading {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 80px 0;
            gap: 20px;
        }

        .loader-ring {
            width: 44px;
            height: 44px;
            border: 2px solid var(--border-hi);
            border-top-color: var(--cyan);
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loader-label {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text-dim);
            letter-spacing: 0.15em;
            text-transform: uppercase;
            animation: blink 1.4s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.3; }
        }

        /* Empty */
        #state-empty {
            display: none;
            text-align: center;
            padding: 80px 0;
        }

        .empty-glyph {
            font-family: var(--mono);
            font-size: 40px;
            color: var(--border-hi);
            margin-bottom: 16px;
        }

        .empty-title {
            font-family: var(--sans);
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dim);
            margin-bottom: 8px;
        }

        .empty-sub {
            font-family: var(--mono);
            font-size: 12px;
            color: var(--text-dim);
        }

        /* Error */
        #state-error {
            display: none;
            background: rgba(255,77,106,0.05);
            border: 1px solid rgba(255,77,106,0.2);
            border-radius: 4px;
            padding: 20px 24px;
            font-family: var(--mono);
            font-size: 12px;
            color: var(--red);
        }

        /* Idle prompt */
        #state-idle {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 80px 0;
            gap: 12px;
            text-align: center;
        }

        .idle-icon {
            font-size: 36px;
            margin-bottom: 8px;
            opacity: 0.3;
        }

        .idle-title {
            font-family: var(--sans);
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dim);
        }

        .idle-sub {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--text-dim);
            max-width: 340px;
            line-height: 1.8;
        }

        /* ─── Responsive ────────────────────────────────────── */
        @media (max-width: 600px) {
            header { flex-direction: column; align-items: flex-start; }
            .header-meta { text-align: left; }
            #search-input { font-size: 13px; }
            #cards-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="shell">

    <!-- Header -->
    <header>
        <div class="logo">
            <span class="logo-eyebrow">// inventory-hub v1.0</span>
            <h1 class="logo-title">Resource<span>&</span>Inventory</h1>
        </div>
        <div class="header-meta">
            <div><span class="status-dot"></span>AI Service Online</div>
            <div>LLaMA 3.3 70B · Groq</div>
        </div>
    </header>

    <!-- Search -->
    <section class="search-block">
        <label class="search-label" for="search-input">// Natural Language Query</label>
        <div class="search-wrapper">
            <span class="search-prompt">&gt;_</span>
            <input
                type="text"
                id="search-input"
                placeholder="e.g. Do we have any high-refresh-rate monitors available?"
                autocomplete="off"
                spellcheck="false"
            />
            <button id="search-btn">Search</button>
        </div>
        <div class="examples">
            <span class="example-chip" onclick="fillQuery(this)">high-refresh-rate monitors</span>
            <span class="example-chip" onclick="fillQuery(this)">books for advanced web routing</span>
            <span class="example-chip" onclick="fillQuery(this)">available laptops</span>
            <span class="example-chip" onclick="fillQuery(this)">all vehicles</span>
            <span class="example-chip" onclick="fillQuery(this)">office furniture</span>
        </div>
    </section>

    <!-- Results -->
    <section id="results-area">

        <!-- AI parse banner -->
        <div id="parse-banner"></div>

        <!-- Loading -->
        <div id="state-loading">
            <div class="loader-ring"></div>
            <span class="loader-label">Querying AI · Searching inventory...</span>
        </div>

        <!-- Error -->
        <div id="state-error"></div>

        <!-- Empty -->
        <div id="state-empty">
            <div class="empty-glyph">[ ]</div>
            <div class="empty-title">No items found</div>
            <div class="empty-sub">Try a broader query or different keywords.</div>
        </div>

        <!-- Idle -->
        <div id="state-idle">
            <div class="idle-icon">⌕</div>
            <div class="idle-title">Ask anything about inventory</div>
            <div class="idle-sub">Type a natural language query above.<br>The AI will parse it and search the database.</div>
        </div>

        <!-- Cards -->
        <div id="results-header" style="display:none;">
            <div class="results-header">
                <div class="results-count">Results — <strong id="result-count">0</strong> items found</div>
            </div>
        </div>
        <div id="cards-grid"></div>

    </section>
</div>

<script>
    const input   = document.getElementById('search-input');
    const btn     = document.getElementById('search-btn');
    const grid    = document.getElementById('cards-grid');
    const banner  = document.getElementById('parse-banner');
    const stLoad  = document.getElementById('state-loading');
    const stError = document.getElementById('state-error');
    const stEmpty = document.getElementById('state-empty');
    const stIdle  = document.getElementById('state-idle');
    const resHdr  = document.getElementById('results-header');
    const resCnt  = document.getElementById('result-count');

    // ── State management ──────────────────────────────────────────
    function showState(state) {
        stLoad.style.display  = 'none';
        stError.style.display = 'none';
        stEmpty.style.display = 'none';
        stIdle.style.display  = 'none';
        resHdr.style.display  = 'none';
        banner.style.display  = 'none';
        grid.innerHTML        = '';

        if (state === 'loading') stLoad.style.display  = 'flex';
        if (state === 'error')   stError.style.display = 'block';
        if (state === 'empty')   stEmpty.style.display = 'block';
        if (state === 'idle')    stIdle.style.display  = 'flex';
    }

    // ── Example chips ──────────────────────────────────────────────
    function fillQuery(el) {
        input.value = el.textContent;
        input.focus();
        doSearch();
    }

    // ── Status badge ───────────────────────────────────────────────
    function statusBadge(status) {
        if (!status) return '';
        const map = {
            available:   'badge-available',
            unavailable: 'badge-unavailable',
            maintenance: 'badge-maintenance',
        };
        const cls = map[status.toLowerCase()] || 'badge-available';
        return `<span class="badge ${cls}">${status}</span>`;
    }

    // ── Format attribute key ───────────────────────────────────────
    function fmtKey(key) {
        return key.replace(/_/g, ' ');
    }

    // ── Render a single card ───────────────────────────────────────
    function renderCard(item, index) {
        const attrs   = item.attributes || {};
        const status  = attrs.status || 'available';
        const location = attrs.location || null;

        // Build attributes rows (skip status & location — shown separately)
        const skip = new Set(['status', 'location']);
        const attrRows = Object.entries(attrs)
            .filter(([k]) => !skip.has(k))
            .map(([k, v]) => `
                <div class="attr-row">
                    <div class="attr-key">${fmtKey(k)}</div>
                    <div class="attr-val">${v}</div>
                </div>`)
            .join('');

        const locationRow = location
            ? `<div class="card-footer">Location — <span>${location}</span></div>`
            : '';

        const card = document.createElement('div');
        card.className = 'item-card';
        card.style.animationDelay = `${index * 60}ms`;
        card.innerHTML = `
            <div class="card-header">
                <div>
                    <div class="card-name">${item.name}</div>
                    <div class="card-id">#${String(item.id).padStart(4, '0')}</div>
                </div>
                ${statusBadge(status)}
            </div>
            <div class="card-category">${item.category}</div>
            ${attrRows ? `<div class="attrs">${attrRows}</div>` : ''}
            ${locationRow}
        `;
        return card;
    }

    // ── Render parse banner ────────────────────────────────────────
    function renderBanner(meta) {
        if (!meta) return;
        const parts = [];
        if (meta.intent_summary) parts.push(`<strong>AI:</strong> ${meta.intent_summary}`);
        if (meta.category)       parts.push(`<strong>Category:</strong> ${meta.category}`);
        if (meta.keywords?.length) parts.push(`<strong>Keywords:</strong> ${meta.keywords.join(', ')}`);
        if (parts.length) {
            banner.innerHTML     = parts.join('&emsp;·&emsp;');
            banner.style.display = 'block';
        }
    }

    // ── Main search ────────────────────────────────────────────────
    async function doSearch() {
        const query = input.value.trim();
        if (!query) return;

        showState('loading');
        btn.disabled = true;

        try {
            const res = await fetch('/api/search', {
                method: 'POST',
                headers: {
                    'Content-Type':  'application/json',
                    'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':        'application/json',
                },
                body: JSON.stringify({ query }),
            });

            const data = await res.json();

            if (!res.ok) {
                showState('error');
                stError.textContent = `Error ${res.status}: ${data.message || 'Something went wrong. Is the AI service running?'}`;
                return;
            }

            const items = data.results ?? data ?? [];
            const meta  = data.meta ?? null;

            if (!items.length) {
                showState('empty');
                renderBanner(meta);
                banner.style.display = items.length === 0 && meta ? 'block' : 'none';
                return;
            }

            showState(null); // clear all states
            renderBanner(meta);
            resHdr.style.display = 'block';
            resCnt.textContent   = items.length;

            items.forEach((item, i) => grid.appendChild(renderCard(item, i)));

        } catch (err) {
            showState('error');
            stError.textContent = `Network error — ${err.message}. Make sure the Laravel server is running.`;
        } finally {
            btn.disabled = false;
        }
    }

    // ── Event listeners ────────────────────────────────────────────
    btn.addEventListener('click', doSearch);
    input.addEventListener('keydown', e => {
        if (e.key === 'Enter') doSearch();
    });
</script>
</body>
</html>
