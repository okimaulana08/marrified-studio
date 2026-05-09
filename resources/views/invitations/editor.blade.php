@extends('layouts.admin')

@section('title', 'Edit Invitation — '.$invitation->slug)
@section('full-width', 'max-w-none')

@section('content')
    <div class="py-8">
        <livewire:invitations.invitation-editor :invitation="$invitation" :key="'editor-'.$invitation->id" />
    </div>
@endsection
