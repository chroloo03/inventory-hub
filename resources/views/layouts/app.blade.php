<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? 'Inventory Hub' }}</title>
    @vite(['resources/css/app.css'])
    {{-- Prevent flash of wrong theme --}}
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.style.visibility = 'hidden';
            document.addEventListener('DOMContentLoaded', function() {
                document.body.classList.add('light');
                document.documentElement.style.visibility = '';
            });
        }
    </script>
</head>
<body>
<div class="app-wrapper">

    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-inner">

            <a href="{{ route('inventory.index') }}" class="navbar-brand">
                <span class="brand-eyebrow">// inventory-hub v1.0</span>
                <span class="brand-name">Resource<span>&amp;</span>Inventory</span>
            </a>

            @auth
                <ul class="navbar-nav">
                    <li>
                        <a href="{{ route('inventory.index') }}"
                           class="nav-link {{ request()->routeIs('inventory.index') ? 'active' : '' }}">
                            All Items
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('inventory.create') }}"
                           class="nav-link {{ request()->routeIs('inventory.create') ? 'active' : '' }}">
                            + Add Item
                        </a>
                    </li>
                </ul>
            @endauth

            <div class="navbar-right">
                <button id="theme-toggle" title="Toggle theme">🌙</button>

                @auth
                    <div class="navbar-status">
                        <span class="status-dot"></span>
                        {{ auth()->user()->name }}
                    </div>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline; margin-left: 14px;">
                        @csrf
                        <button type="submit" class="btn btn-logout">Logout</button>
                    </form>
                @else
                    <div class="navbar-status" style="color: var(--text-dim);">
                        <span class="status-dot" style="background: var(--red);"></span>
                        Offline
                    </div>
                @endauth
            </div>

        </div>
    </nav>

    <!-- Flash Messages -->
    <div style="max-width:1200px; width:100%; margin:0 auto; padding:20px 28px 0;">
        @if(session('success'))
            <div class="alert alert-success">✓ &nbsp;{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">✕ &nbsp;{{ session('error') }}</div>
        @endif
    </div>

    <!-- Page Content -->
    <main class="main-content">
        {{ $slot }}
    </main>

</div>

<!-- Delete Confirm Modal -->
<div class="modal-overlay" id="delete-modal">
    <div class="modal">
        <div class="modal-title">Confirm Delete</div>
        <div class="modal-body">
            Are you sure you want to delete <strong id="delete-item-name"></strong>?
            <br/>This cannot be undone.
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form id="delete-form" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
    // ── Theme ─────────────────────────────────────────────────
    const body = document.body;
    const btn  = document.getElementById('theme-toggle');

    function applyTheme(theme) {
        if (theme === 'light') {
            body.classList.add('light');
            btn.textContent = '☀️';
        } else {
            body.classList.remove('light');
            btn.textContent = '🌙';
        }
    }

    applyTheme(localStorage.getItem('theme') || 'dark');

    btn.addEventListener('click', () => {
        const next = body.classList.contains('light') ? 'dark' : 'light';
        localStorage.setItem('theme', next);
        applyTheme(next);
    });

    // ── Delete Modal ──────────────────────────────────────────
    function confirmDelete(id, name, url) {
        document.getElementById('delete-item-name').textContent = name;
        document.getElementById('delete-form').action = url;
        document.getElementById('delete-modal').classList.add('open');
    }

    function closeDeleteModal() {
        document.getElementById('delete-modal').classList.remove('open');
    }

    document.getElementById('delete-modal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
</script>
</body>
</html>
