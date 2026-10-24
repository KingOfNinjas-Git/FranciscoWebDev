@extends('admin.layout')

@section('title', 'New project')

@section('content')
<a href="{{ route('admin.projects.index') }}" class="btn btn-ghost mb-8 px-4 py-2 text-sm font-medium">&larr; Back to projects</a>

<section class="glass mx-auto max-w-3xl rounded-3xl p-6 md:p-8">
    <h1 class="text-3xl font-bold text-white">New project</h1>
    <p class="mb-6 mt-2 text-sm text-slate-400">Fill in the details first; you can upload images right after saving.</p>
    @include('admin.projects._form')
</section>
@endsection
