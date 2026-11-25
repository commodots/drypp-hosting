<x-guest-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold"><span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-400 to-violet-600 text-slate-950">◒</span>drypp<span class="text-cyan-400">.</span></a>
            <div class="text-sm text-slate-400">Already a customer? <a href="{{ route('login', request()->query()) }}" class="ml-1 font-semibold text-cyan-300">Login</a></div>
        </header>
        <main class="mx-auto grid max-w-7xl items-center gap-12 px-6 py-10 lg:grid-cols-2">
            <section class="hidden lg:block">
                <p class="mb-5 inline-flex rounded-full border border-violet-400/20 bg-violet-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[.18em] text-violet-200">Start with Drypp</p>
                <h1 class="text-5xl font-bold leading-tight">Get your domain.<br><span class="bg-gradient-to-r from-cyan-300 to-violet-400 bg-clip-text text-transparent">Build your presence.</span></h1>
                <p class="mt-5 max-w-lg text-lg leading-8 text-slate-400">Create your account to manage hosting, domains, billing and services in one place.</p>
            </section>
            <section class="mx-auto w-full max-w-md rounded-3xl border border-white/10 bg-white/[.04] p-8 shadow-2xl">
                <p class="text-sm font-semibold text-cyan-300">CUSTOMER PORTAL</p>
                <h2 class="mt-2 text-3xl font-bold">Create your account</h2>
                <p class="mt-2 mb-7 text-sm text-slate-400">Set up your Drypp customer workspace.</p>
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf
                    @foreach (['name'=>'Full name','email'=>'Email address'] as $field=>$label)
                        <div>
                            <label for="{{ $field }}" class="mb-2 block text-sm font-medium text-slate-300">{{ $label }}</label>
                            <input id="{{ $field }}" name="{{ $field }}" type="{{ $field==='email'?'email':'text' }}" value="{{ old($field) }}" required autocomplete="{{ $field==='name'?'name':'email' }}"
                                class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/20">
                            <x-input-error :messages="$errors->get($field)" class="mt-2" />
                        </div>
                    @endforeach
                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-slate-300">Password</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/20">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-300">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/20">
                    </div>
                    <button type="submit" class="mt-2 w-full rounded-xl bg-gradient-to-r from-cyan-400 to-violet-500 px-4 py-3 font-bold text-slate-950 hover:brightness-110">Create account</button>
                </form>
                <p class="mt-6 text-center text-xs text-slate-500">By creating an account, you can manage your Drypp services and orders.</p>
            </section>
        </main>
    </div>
</x-guest-layout>
