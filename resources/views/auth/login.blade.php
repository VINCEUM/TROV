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
                <input type="email" id="lgEmail" name="email" value="{{ old('email', 'vince@kpc.test') }}" autocomplete="username" required autofocus>
            </div>
            <div class="field">
                <label for="lgPass">Password</label>
                <input type="password" id="lgPass" name="password" value="password" autocomplete="current-password" required>
            </div>
            <label class="check"><input type="checkbox" name="remember" checked>Remember me on this device</label>
            <button class="btn primary block" type="submit">Log in</button>
            <div class="divider">or</div>
            <button class="btn block" type="button" id="lgGoogle">Continue with Google</button>
            <p class="tiny" style="text-align:center">Forgot password? &middot; No account yet? Ask an admin for an invite.</p>
        </form>
    </section>
</x-layouts.shell>
