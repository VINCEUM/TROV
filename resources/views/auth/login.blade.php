<x-layouts.shell title="Sign in - TROV">
    <section class="screen login">
        <div>
            <h2>Welcome back</h2>
            <p class="tiny" style="margin-top:6px">Sign in once. trov keeps you signed in &mdash; you only submit a devotional each day.</p>
        </div>

        @if ($errors->any())
            <div class="row-card" style="background:var(--red-soft);border-color:var(--red);color:var(--red);gap:10px">
                <svg style="width:18px;height:18px"><use href="#i-eye"/></svg>
                <span class="ss" style="color:var(--red)">{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" style="display:flex;flex-direction:column;gap:14px">
            @csrf
            <div class="field">
                <label for="lgEmail">Email</label>
                <input type="email" id="lgEmail" name="email" value="{{ old('email') }}" placeholder="you@gmail.com" autocomplete="username" required autofocus>
            </div>
            <div class="field">
                <label for="lgPass">Password</label>
                <input type="password" id="lgPass" name="password" placeholder="Your password" autocomplete="current-password" required>
            </div>
            <label class="check"><input type="checkbox" name="remember" checked>Remember me on this device</label>
            <button class="btn primary block" type="submit">Log in</button>
        </form>

        @if (config('services.google.client_id') && config('services.google.client_secret'))
            <div class="divider">or</div>
            <a class="btn block" href="{{ route('google.redirect') }}" style="text-decoration:none">
                <svg viewBox="0 0 48 48" style="width:18px;height:18px" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9 3.6l6.7-6.7C35.6 2.4 30.2 0 24 0 14.6 0 6.4 5.4 2.5 13.2l7.9 6.1C12.2 13.3 17.6 9.5 24 9.5z"/><path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v9h12.7c-.6 3-2.3 5.5-4.8 7.2l7.4 5.7C43.9 37.9 46.5 31.8 46.5 24.5z"/><path fill="#FBBC05" d="M10.4 28.7c-.5-1.5-.8-3-.8-4.7s.3-3.2.8-4.7l-7.9-6.1C.9 16.5 0 20.1 0 24s.9 7.5 2.5 10.8l7.9-6.1z"/><path fill="#34A853" d="M24 48c6.2 0 11.4-2 15.2-5.6l-7.4-5.7c-2 1.4-4.7 2.3-7.8 2.3-6.4 0-11.8-3.8-13.6-9.3l-7.9 6.1C6.4 42.6 14.6 48 24 48z"/></svg>
                Continue with Google
            </a>
            <p class="tiny" style="text-align:center;margin-top:4px">Sign in with your work Google account. No account yet? Ask an owner to add you.</p>
        @endif
    </section>
</x-layouts.shell>
