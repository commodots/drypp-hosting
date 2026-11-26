@extends('layouts.app')
@section('title', 'Profile settings')
@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <header>
            <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">Account</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Profile settings</h1>
            <p class="mt-2 text-sm text-slate-600">Manage your profile, password, and account access.</p>
        </header>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
            @include('profile.partials.update-profile-information-form')
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
            @include('profile.partials.update-password-form')
        </section>

        <section class="rounded-xl border border-red-200 bg-white p-5 shadow-sm md:p-6">
            @include('profile.partials.delete-user-form')
        </section>
    </div>
@endsection
