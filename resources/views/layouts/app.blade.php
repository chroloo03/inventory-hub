<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? 'Inventory Hub' }}</title>
    @vite(['resources/css/app.css'])
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
                        @if(auth()->user()->isAdmin())
                            <li>
                                <a href="{{ route('staff.index') }}"
                                    class="nav-link {{ request()->routeIs('staff.*') ? 'active' : '' }}">
                                    Staff
                                </a>
                            </li>
                        @endif
                    </ul>
                @endauth

                <div class="navbar-right">
                    <button id="theme-toggle" title="Toggle theme">🌙</button>

                    @auth
                        {{-- Low Stock Bell --}}
                        @php $lowStockCount = \App\Models\InventoryItem::lowStock()->count(); @endphp
                        @if($lowStockCount > 0)
                            <div class="low-stock-bell" id="low-stock-bell">
                                <a href="{{ route('inventory.low-stock') }}" class="bell-btn"
                                    title="{{ $lowStockCount }} item(s) are low on stock — click to view">
                                    <span class="bell-icon">🔔</span>
                                    <span class="bell-badge">{{ $lowStockCount }}</span>
                                </a>
                                <div class="bell-tooltip">
                                    ⚠ {{ $lowStockCount }} low stock item{{ $lowStockCount > 1 ? 's' : '' }}
                                </div>
                            </div>
                        @endif

                        {{-- User Dropdown --}}
                        <div class="user-menu" id="user-menu">
                            <button class="user-menu-trigger" id="user-menu-trigger" type="button">
                                <div class="user-avatar">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <span class="user-name">{{ auth()->user()->name }}</span>
                                <span class="user-chevron">▼</span>
                            </button>

                            <div class="user-dropdown">
                                <div class="dropdown-header">
                                    <div class="dropdown-header-name">{{ auth()->user()->name }}</div>
                                    <div class="dropdown-header-email">{{ auth()->user()->email }}</div>
                                    <div style="margin-top:4px;">
                                        <span class="badge {{ auth()->user()->isAdmin() ? 'badge-available' : 'badge-checked_out' }}"
                                              style="font-size:9px;">
                                            {{ ucfirst(auth()->user()->role) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="dropdown-items">
                                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                        <span class="dropdown-item-icon">⚙</span>
                                        Account Settings
                                    </a>

                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('staff.index') }}" class="dropdown-item">
                                            <span class="dropdown-item-icon">👥</span>
                                            Manage Staff
                                        </a>
                                        <a href="{{ route('staff.create') }}" class="dropdown-item">
                                            <span class="dropdown-item-icon">+</span>
                                            Add Staff Account
                                        </a>
                                    @endif

                                    <div class="dropdown-divider"></div>
                                    <button type="button" class="dropdown-item danger" onclick="confirmLogout()">
                                        <span class="dropdown-item-icon">↪</span>
                                        Logout
                                    </button>
                                </div>
                            </div>
                        </div>

                    @else
                        <div class="navbar-status" style="color: var(--text-dim);">
                            <span class="status-dot" style="background: var(--red); box-shadow: 0 0 6px var(--red);"></span>
                            Offline
                        </div>
                    @endauth
                </div>

            </div>
        </nav>

        <div style="max-width:1200px; width:100%; margin:0 auto; padding:20px 28px 0;">
            @if(session('success'))
                <div class="alert alert-success">✓ &nbsp;{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">✕ &nbsp;{{ session('error') }}</div>
            @endif
        </div>

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
                <br />This cannot be undone.
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

    <!-- Logout Confirm Modal -->
    <div class="modal-overlay" id="logout-modal">
        <div class="modal">
            <div class="modal-title">Sign Out</div>
            <div class="modal-body">
                Are you sure you want to log out of Inventory Hub?
            </div>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeLogoutModal()">Stay</button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">Yes, Logout</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // ── Theme ─────────────────────────────────────────────────
        const body = document.body;
        const themeBtn = document.getElementById('theme-toggle');

        function applyTheme(theme) {
            body.classList.toggle('light', theme === 'light');
            themeBtn.textContent = theme === 'light' ? '☀️' : '🌙';
        }

        applyTheme(localStorage.getItem('theme') || 'dark');

        themeBtn.addEventListener('click', () => {
            const next = body.classList.contains('light') ? 'dark' : 'light';
            localStorage.setItem('theme', next);
            applyTheme(next);
        });

        // ── User Dropdown ─────────────────────────────────────────
        const userMenu = document.getElementById('user-menu');
        const userTrigger = document.getElementById('user-menu-trigger');

        if (userTrigger) {
            userTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                userMenu.classList.toggle('open');
            });

            document.addEventListener('click', () => userMenu?.classList.remove('open'));

            userMenu.querySelector('.user-dropdown')?.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

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

        // ── Logout Modal ──────────────────────────────────────────
        function confirmLogout() {
            userMenu?.classList.remove('open');
            document.getElementById('logout-modal').classList.add('open');
        }

        function closeLogoutModal() {
            document.getElementById('logout-modal').classList.remove('open');
        }

        document.getElementById('logout-modal').addEventListener('click', function(e) {
            if (e.target === this) closeLogoutModal();
        });
    </script>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <span class="brand-name" style="font-size:14px;">Resource<span>&amp;</span>Inventory</span>
                <span class="footer-tagline">Inventory management made simple.</span>
            </div>

            <div class="footer-links">
                @auth
                    <a href="{{ route('inventory.index') }}" class="footer-link">All Items</a>
                    <a href="{{ route('inventory.create') }}" class="footer-link">Add Item</a>
                    <a href="{{ route('inventory.low-stock') }}" class="footer-link">Low Stock</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('staff.index') }}" class="footer-link">Staff</a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="footer-link">Account</a>
                @endauth
            </div>

            <div class="footer-meta">
                <span class="footer-status">
                    <span class="status-dot" style="width:5px; height:5px;"></span>
                    System Online
                </span>
                <span class="footer-copy">&copy; {{ date('Y') }} Inventory Hub</span>
            </div>
        </div>
    </footer>

</body>
</html>
