<x-layout title="System Login">

    <div style="max-width: 500px; margin: 40px auto;">
        <div class="page-header" style="justify-content: center; text-align: center; margin-bottom: 24px;">
            <div class="page-title-group">
                <div class="page-eyebrow">// authorization required</div>
                <h1 class="page-title">Login</h1>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                ✕ &nbsp;{{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-card">
                <div class="form-card-title">// Credentials</div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                            class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                            value="{{ old('email') }}" required autofocus autocomplete="email" />
                    </div>

                    <div class="form-group full-width">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password" />
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">
                        Access System
                    </button>
                </div>
            </div>
        </form>
    </div>

</x-layout>
