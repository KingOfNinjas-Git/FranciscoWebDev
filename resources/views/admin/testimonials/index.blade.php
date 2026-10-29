@extends('admin.layout')

@section('title', 'Testimonials')

@section('content')
@php
    $askEn = "Hi [name],\n\nI hope the website is working well for you. I am collecting a few words from the people I have worked with, to show on my own site. Could you write two or three sentences about what it was like to work together, and what changed for your business? Your name and role are enough; no photo needed.\n\nThank you very much,\nFrancisco";
    $askPt = "Olá [nome],\n\nEspero que o site esteja a correr bem. Estou a juntar umas palavras das pessoas com quem trabalhei, para mostrar no meu site. Podia escrever duas ou três frases sobre como foi trabalharmos juntos e o que mudou no seu negócio? O nome e a função chegam; não preciso de fotografia.\n\nMuito obrigado,\nFrancisco";
@endphp
<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="eyebrow">Admin</p>
        <h1 class="section-title">Testimonials</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-400">Real words from people you worked with. A quote only appears on the home page when it is marked <strong>published</strong>; the section stays hidden while there is nothing to show. Only add quotes somebody actually wrote and agreed to share.</p>
    </div>
    <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary px-5 py-2.5 text-sm font-semibold">+ Add a quote</a>
</div>

@if ($testimonials->isEmpty())
    <div class="glass rounded-3xl p-10 text-center text-slate-400">No quotes yet. Ask a client with the message below, then add what they send.</div>
@else
    <ul class="glass divide-y divide-white/10 overflow-hidden rounded-3xl">
        @foreach ($testimonials as $t)
            <li class="flex flex-col gap-3 p-5 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="font-semibold text-white">{{ $t->name }} @if ($t->role)<span class="font-normal text-slate-400">&middot; {{ $t->role }}</span>@endif</p>
                    <p class="mt-1 line-clamp-2 text-sm text-slate-300">&ldquo;{{ $t->quote }}&rdquo;</p>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $t->published ? 'bg-emerald-400/15 text-emerald-300' : 'bg-white/10 text-slate-400' }}">{{ $t->published ? 'Published' : 'Hidden' }}</span>
                    <a href="{{ route('admin.testimonials.edit', $t->id) }}" class="btn btn-ghost px-4 py-1.5 text-xs font-medium">Edit</a>
                    <form action="{{ route('admin.testimonials.destroy', $t->id) }}" method="POST" data-confirm="Delete this quote?">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-full px-2.5 py-1 text-xs font-medium text-rose-300 hover:bg-white/10">Delete</button>
                    </form>
                </div>
            </li>
        @endforeach
    </ul>
@endif

<section class="glass mt-10 rounded-3xl p-6 md:p-8" aria-labelledby="ask-title">
    <h2 id="ask-title" class="text-xl font-semibold text-white">Asking for a quote</h2>
    <p class="mt-1 text-sm text-slate-400">Copy one of these messages and send it to a client who was happy. Replace the name in brackets.</p>
    <div class="mt-5 grid gap-5 lg:grid-cols-2">
        <label class="field"><span>English</span><textarea readonly rows="9" onfocus="this.select()">{{ $askEn }}</textarea></label>
        <label class="field"><span>Português</span><textarea readonly rows="9" onfocus="this.select()">{{ $askPt }}</textarea></label>
    </div>
</section>
@endsection
