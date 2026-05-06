<x-layout title="{{ isset($staff) ? 'Edit Staff Account' : 'Add Staff Account' }}">

    <div class="page-header">
        <div class="page-title-group">
            <div class="page-eyebrow">// admin / staff / {{ isset($staff) ? 'edit' : 'create' }}</div>
            <h1 class="page-title">{{ isset($staff) ? 'Edit Staff Account' : 'Add Staff Account' }}</h1>
            @if(isset($staff))
                <div class="page-subtitle">Editing account for {{ $staff->name }}</div>
            @endif
        </div>
        <a href="{{ route('staff.index') }}" class="btn btn-secondary">← Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-error mb-3">
            ✕ &nbsp;
            <ul style="margin:0; padding:0; list-style:none;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(isset($staff))
        <form method="POST" action="{{ route('staff.update', $staff) }}">
            @csrf
            @method('PUT')
    @else
        <form method="POST" action="{{ route('staff.store') }}">
            @csrf
    @endif

        <!-- Account Info -->
        <div class="form-card">
            <div class="form-card-title">// Account Information</div>
            <div class="form-grid">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                        class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        value="{{ old('name', $staff->name ?? '') }}"
                        required autocomplete="name" />
                    @error('name') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        value="{{ old('email', $staff->email ?? '') }}"
                        required autocomplete="email" />
                    @error('email') <span class="form-error">{{ $message }}</span> @enderror
                </div>

            </div>
        </div>

        <!-- Password -->
        <div class="form-card">
            <div class="form-card-title">
                // {{ isset($staff) ? 'Change Password' : 'Set Password' }}
            </div>

            @if(isset($staff))
                <div class="alert alert-info mb-3" style="font-size:12px;">
                    ⓘ &nbsp;Leave password fields blank to keep the current password unchanged.
                </div>
            @endif

            <div class="form-grid">
                <div class="form-group">
                    <label for="password">{{ isset($staff) ? 'New Password' : 'Password' }}</label>
                    <input type="password" id="password" name="password"
                        class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        autocomplete="new-password"
                        {{ isset($staff) ? '' : 'required' }} />
                    @error('password') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="form-control"
                        autocomplete="new-password"
                        {{ isset($staff) ? '' : 'required' }} />
                </div>
            </div>

            <div class="alert alert-info mt-3" style="font-size:12px;">
                ⓘ &nbsp;Minimum 8 characters with uppercase, lowercase, and a number.
            </div>
        </div>

        <div style="display:flex; gap:12px; justify-content:flex-end;">
            <a href="{{ route('staff.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                {{ isset($staff) ? 'Save Changes' : 'Create Staff Account' }}
            </button>
        </div>

    </form>

</x-layout>
