{{-- Alt text + caption in English, Portuguese and Spanish. $img is null when uploading. --}}
@php
    $tr = $img?->translations ?? [];
    $rows = ['en' => 'English', 'pt' => 'Português', 'es' => 'Español'];
@endphp
<div class="space-y-3">
    @foreach ($rows as $code => $label)
        @php
            $altName = $code === 'en' ? 'alt' : "translations[$code][alt]";
            $capName = $code === 'en' ? 'caption' : "translations[$code][caption]";
            $altVal = $code === 'en' ? ($img->alt ?? '') : ($tr[$code]['alt'] ?? '');
            $capVal = $code === 'en' ? ($img->caption ?? '') : ($tr[$code]['caption'] ?? '');
        @endphp
        <div class="rounded-2xl border border-white/10 p-3">
            <p class="mb-2 font-mono text-[10px] uppercase tracking-widest text-sky-300">{{ $label }}</p>
            <label class="field mb-2">
                <span>Alt text</span>
                <input type="text" name="{{ $altName }}" value="{{ $altVal }}" maxlength="255" placeholder="{{ $code === 'en' ? 'Describe the image for accessibility' : 'Optional, English is used if empty' }}">
            </label>
            <label class="field">
                <span>Caption (shown under the image)</span>
                <input type="text" name="{{ $capName }}" value="{{ $capVal }}" maxlength="500" placeholder="Optional">
            </label>
        </div>
    @endforeach
</div>
