<x-guest-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold tracking-tight">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-400 to-violet-600 text-slate-950">◒</span>
                <span>drypp<span class="text-cyan-400">.</span></span>
            </a>
            <div class="text-sm text-slate-400">New to Drypp?
                <a href="{{ route('register', request()->query()) }}" class="ml-1 font-semibold text-cyan-300 hover:text-cyan-200">Create account</a>
            </div>
        </header>
        <main class="mx-auto grid min-h-[calc(100vh-80px)] max-w-7xl items-center gap-12 px-6 py-10 lg:grid-cols-2">
            <section class="hidden lg:block">
                <div class="mb-5 inline-flex rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[.18em] text-cyan-200">Domains · Hosting · Simple</div>
                <h1 class="max-w-xl text-5xl font-bold leading-tight">Your online home,<br><span class="bg-gradient-to-r from-cyan-300 to-violet-400 bg-clip-text text-transparent">ready to grow.</span></h1>
                <p class="mt-5 max-w-lg text-lg leading-8 text-slate-400">Manage your domains, hosting services, orders and renewals from one clear workspace.</p>
                <div class="mt-10 grid max-w-lg grid-cols-3 gap-3">
                    @foreach (['Fast setup','Secure hosting','One dashboard'] as $feature)
                        <div class="rounded-2xl border border-white/10 bg-white/[.03] p-4 text-sm text-slate-300">{{ $feature }}</div>
                    @endforeach
                </div>
            </section>
            <section class="mx-auto w-full max-w-md">
                <div class="rounded-3xl border border-white/10 bg-white/[.04] p-8 shadow-2xl shadow-cyan-950/20">
                    <div class="mb-7">
                        <p class="text-sm font-semibold text-cyan-300">CUSTOMER PORTAL</p>
                        <h2 class="mt-2 text-3xl font-bold">Welcome back</h2>
                        <p class="mt-2 text-sm text-slate-400">Sign in to manage your Drypp services.</p>
                    </div>
                    <x-auth-session-status class="mb-4 text-sm text-emerald-300" :status="session('status')" />
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf
                        @if(request()->query('redirect'))
                            <input type="hidden" name="redirect" value="{{ request()->query('redirect') }}">
                        @endif
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-slate-300">Email address</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/20" placeholder="you@example.com">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label for="password" class="text-sm font-medium text-slate-300">Password</label>
                                @if (Route::has('password.request'))
                                    <a class="text-xs text-cyan-300 hover:text-cyan-200" href="{{ route('password.request') }}">Forgot password?</a>
                                @endif
                            </div>
                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/20" placeholder="Enter your password">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <label class="flex items-center gap-2 text-sm text-slate-400">
                            <input type="checkbox" name="remember" class="rounded border-white/20 bg-slate-900 text-cyan-400 focus:ring-cyan-400">
                            Keep me signed in
                        </label>
                        <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-cyan-400 to-violet-500 px-4 py-3 font-bold text-slate-950 transition hover:brightness-110">Sign in</button>
                    </form>
                    <p class="mt-6 text-center text-xs text-slate-500">Protected access to your hosting and domain services.</p>
                </div>
                <p class="mt-5 text-center text-sm text-slate-500"><a href="{{ route('home') }}" class="hover:text-cyan-300">← Back to Drypp</a></p>
            </section>
        </main>
    </div>
</x-guest-layout>
