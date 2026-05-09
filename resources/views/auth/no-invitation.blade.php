@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="max-w-xl mx-auto py-12 text-center">
        <h1 class="font-display text-3xl font-semibold text-white mb-3">
            Halo, {{ $user->name }}
        </h1>
        <p class="text-white/50 text-sm leading-relaxed">
            Akun kamu sudah aktif, tapi tim studio belum men-link invitation
            ke akun ini. Silakan hubungi admin untuk menyelesaikan setup.
        </p>

        <form method="POST" action="{{ route('logout') }}" class="mt-8">
            @csrf
            <button type="submit" class="btn-ghost text-xs">Keluar</button>
        </form>
    </div>
@endsection
