@extends('layouts.admin')

@section('title', 'New Invitation')
@section('full-width', 'max-w-none')

@section('content')
    <div class="py-8">
        <livewire:invitations.invitation-editor />
    </div>
@endsection
