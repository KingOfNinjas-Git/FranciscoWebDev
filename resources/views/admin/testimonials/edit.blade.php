@extends('admin.layout')

@section('title', 'Edit quote')

@section('content')
<a href="{{ route('admin.testimonials.index') }}" class="btn btn-ghost mb-8 px-4 py-2 text-sm font-medium">&larr; Back to testimonials</a>

<section class="glass mx-auto max-w-3xl rounded-3xl p-6 md:p-8">
    <h1 class="text-3xl font-bold text-white">{{ $testimonial->name }}</h1>
    <p class="mb-6 mt-2 text-sm text-slate-400">Edit the quote and decide whether it is shown on the site.</p>
    @include('admin.testimonials._form')
</section>
@endsection
