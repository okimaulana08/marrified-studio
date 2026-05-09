@extends('layouts.admin')

@section('title', 'Credentials — '.$invitation->slug)

@section('content')
    <div class="py-8">
        <livewire:admin.invitations.credential-manager :slug="$invitation->slug" />
    </div>
@endsection
