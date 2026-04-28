<x-layout title="Account Settings">

    <div class="page-header">
        <div class="page-title-group">
            <div class="page-eyebrow">// account</div>
            <h1 class="page-title">Account Settings</h1>
            <div class="page-subtitle">Manage your name, email, and password.</div>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">← Back</a>
    </div>

    <div class="profile-grid">

        <!-- Sidebar -->
        <div class="profile-sidebar">
            <div class="profile-avatar-lg">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="profile-display-name">{{ $user->name }}</div>
            <div class="profile-display-email">{{ $user->email }}</div>

            <div class="profile-meta">
                <div class="profile-meta-row">
                    <span class="profile-meta-key">Member since</span>
                    <span class="profile-meta-val">{{ $user->created_at->format('M Y') }}</span>
                </div>
                <div class="profile-meta-row">
                    <span class="profile-meta-key">Last updated</span>
                    <span class="profile-meta-val">{{ $user->updated_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>

        <!-- Forms Column -->
        <div>

            <!-- Update Info -->
            <div class="form-card">
                <div class="form-card-title">// Account Information</div>

                @if($errors->updateInfo->any())
                    <div class="alert alert-error mb-3">
                        ✕ &nbsp;
                        <ul style="margin:0; padding:0; list-style:none;">
                            @foreach($errors->updateInfo->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update-info') }}">
                    @csrf
                    @method('PATCH')
                    <div class="form-grid">

                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control {{ $errors->updateInfo->has('name') ? 'is-invalid' : '' }}"
                                value="{{ old('name', $user->name) }}"
                                required
                                autocomplete="name"
                            />
                            @if($errors->updateInfo->has('name'))
                                <span class="form-error">{{ $errors->updateInfo->first('name') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control {{ $errors->updateInfo->has('email') ? 'is-invalid' : '' }}"
                                value="{{ old('email', $user->email) }}"
                                required
                                autocomplete="email"
                            />
                            @if($errors->updateInfo->has('email'))
                                <span class="form-error">{{ $errors->updateInfo->first('email') }}</span>
                            @endif
                        </div>

                    </div>

                    <div style="display:flex; justify-content:flex-end; margin-top:20px;">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>

            <!-- Update Password -->
            <div class="form-card">
                <div class="form-card-title">// Change Password</div>

                @if($errors->updatePassword->any())
                    <div class="alert alert-error mb-3">
                        ✕ &nbsp;
                        <ul style="margin:0; padding:0; list-style:none;">
                            @foreach($errors->updatePassword->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update-password') }}">
                    @csrf
                    @method('PATCH')
                    <div class="form-grid">

                        <div class="form-group full-width">
                            <label for="current_password">Current Password</label>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="form-control {{ $errors->updatePassword->has('current_password') ? 'is-invalid' : '' }}"
                                autocomplete="current-password"
                            />
                            @if($errors->updatePassword->has('current_password'))
                                <span class="form-error">{{ $errors->updatePassword->first('current_password') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control {{ $errors->updatePassword->has('password') ? 'is-invalid' : '' }}"
                                autocomplete="new-password"
                            />
                            @if($errors->updatePassword->has('password'))
                                <span class="form-error">{{ $errors->updatePassword->first('password') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control {{ $errors->updatePassword->has('password_confirmation') ? 'is-invalid' : '' }}"
                                autocomplete="new-password"
                            />
                        </div>

                    </div>

                    <!-- Password requirements hint -->
                    <div class="alert alert-info mt-3" style="font-size:12px;">
                        ⓘ &nbsp;Password must be at least 8 characters and include uppercase, lowercase, and a number.
                        Passwords found in known data breaches will be rejected.
                    </div>

                    <div style="display:flex; justify-content:flex-end; margin-top:16px;">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</x-layout>
