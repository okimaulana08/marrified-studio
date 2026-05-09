@extends('layouts.admin')

@section('title', 'Edit Theme: ' . $slug)
@section('full-width', 'max-w-none')

@section('content')
    <livewire:admin.theme-editor :slug="$slug" />
@endsection
