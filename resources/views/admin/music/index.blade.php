@extends('layouts.admin')

@section('title', 'Music Library')
@section('full-width', 'max-w-none')

@section('content')
    <div class="py-8">
        <div class="flex items-end justify-between mb-6">
            <div>
                <p class="text-xs uppercase tracking-[0.25em] text-emerald-400/70 font-semibold mb-2 flex items-center gap-2">
                    <span class="inline-block w-6 h-px bg-emerald-400/50"></span>
                    Music Library
                </p>
                <h1 class="font-display text-4xl font-bold tracking-display text-gradient leading-tight">
                    Background Music
                </h1>
                <p class="text-sm text-white/40 mt-2">
                    Upload MP3 yang couple bisa pilih untuk backsound undangan mereka.
                </p>
            </div>
        </div>

        <livewire:admin.music.music-library-manager />
    </div>
@endsection
