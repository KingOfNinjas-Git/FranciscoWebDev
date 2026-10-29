@extends('admin.layout')

@section('title', 'New quote')

@section('content')
<a href="{{ route('admin.testimonials.index') }}" class="btn btn-ghost mb-8 px-4 py-2 text-sm font-medium">&larr; Back to testimonials</a>

<section class="glass mx-auto max-w-3xl rounded-3xl p-6 md:p-8">
    <h1 class="text-3xl font-bold text-white">New quote</h1>
    <p class="mb-6 mt-2 text-sm text-slate-400">Add what a client wrote. It stays hidden until you tick &ldquo;Show this quote on the site&rdquo;.</p>
    @include('admin.testimonials._form')
</section>
@endsection
