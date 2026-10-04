<x-layouts.shell title="Create account - TROV">
    <section class="screen login">
        <div>
            <h2>Create your account</h2>
            <p class="tiny" style="margin-top:6px">Set up your trov account once. You&rsquo;ll stay signed in &mdash; you only submit a devotional each day.</p>
        </div>

        @if ($errors->any())
            <div class="row-card" style="background:var(--red-soft);border-color:var(--red);color:var(--red);gap:10px">
                <svg style="width:18px;height:18px"><use href="#i-eye"/></svg>
                <span class="ss" style="color:var(--red)">{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('register.attempt') }}" style="display:flex;flex-direction:column;gap:14px">
            @csrf
            <div class="field">
                <label for="rgName">Full name</label>
                <input type="text" id="rgName" name="name" value="{{ old('name') }}" placeholder="Juan Dela Cruz" autocomplete="name" required autofocus>
            </div>
            <div class="field">
                <label for="rgEmail">Email</label>
                <input type="email" id="rgEmail" name="email" value="{{ old('email') }}" placeholder="you@gmail.com" autocomplete="username" required>
            </div>
            <div class="field">
                <label for="rgRole">I am a&hellip;</label>
                <select id="rgRole" name="role" required>
                    <option value="Video Editor" @selected(old('role') === 'Video Editor' || ! old('role'))>Video Editor</option>
                    <option value="Owner" @selected(old('role') === 'Owner')>Owner (admin)</option>
                </select>
            </div>
            <div class="field">
                <label for="rgPass">Password</label>
                <input type="password" id="rgPass" name="password" placeholder="At least 8 characters" autocomplete="new-password" required>
            </div>
            <div class="field">
                <label for="rgPass2">Confirm password</label>
                <input type="password" id="rgPass2" name="password_confirmation" placeholder="Re-type your password" autocomplete="new-password" required>
            </div>
            <button class="btn primary block" type="submit">Create account</button>
        </form>

        <p class="tiny" style="text-align:center;margin-top:4px">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
    </section>
</x-layouts.shell>
