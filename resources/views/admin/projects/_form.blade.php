@php
    $editing = isset($project);
    $tr = $editing ? ($project->translations ?? []) : [];
    $langs = ['en' => 'English', 'pt' => 'Português', 'es' => 'Español'];
    $tech = $editing ? (is_array($project->technologies) ? implode(', ', $project->technologies) : $project->technologies) : '';
@endphp

<form action="{{ $editing ? route('admin.projects.update', $project->id) : route('admin.projects.store') }}" method="POST" class="space-y-6">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div>
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">Text</h2>
            <div class="lang-switch flex items-center rounded-full p-1" role="tablist" aria-label="Language of the text">
                @foreach ($langs as $code => $label)
                    <button type="button" role="tab" data-tab="{{ $code }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" class="lang-btn rounded-full px-3 py-1 text-xs font-semibold {{ $loop->first ? 'is-active' : 'text-slate-400 hover:text-white' }}">{{ strtoupper($code) }}</button>
                @endforeach
            </div>
        </div>
        <p class="mb-4 text-xs text-slate-400">English is the default and is shown whenever a translation is empty.</p>

        @foreach ($langs as $code => $label)
            @php
                $n = fn ($f) => $code === 'en' ? $f : "translations[$code][$f]";
                $o = fn ($f) => $code === 'en'
                    ? old($f, $editing ? $project->{$f} : '')
                    : old("translations.$code.$f", $tr[$code][$f] ?? '');
            @endphp
            <div data-panel="{{ $code }}" role="tabpanel" class="space-y-5 {{ $loop->first ? '' : 'hidden' }}">
                <label class="field">
                    <span>Title ({{ $label }}){{ $code === 'en' ? ' *' : '' }}</span>
                    <input type="text" name="{{ $n('title') }}" data-safe value="{{ $o('title') }}" maxlength="255" @if ($code === 'en') required @endif placeholder="{{ $code === 'en' ? '' : 'Leave empty to use the English title' }}">
                </label>
                <label class="field">
                    <span>Short description ({{ $label }})</span>
                    <textarea name="{{ $n('description') }}" data-safe rows="3" placeholder="Brief summary shown in the project pop-up...">{{ $o('description') }}</textarea>
                </label>
                <label class="field">
                    <span>Project details ({{ $label }})</span>
                    <textarea name="{{ $n('notes') }}" data-safe rows="8" placeholder="Longer write-up: goals, challenges, what you learned...">{{ $o('notes') }}</textarea>
                </label>
            </div>
        @endforeach
    </div>

    <div class="space-y-5 border-t border-white/10 pt-6">
        <h2 class="font-mono text-xs uppercase tracking-[0.25rem] text-sky-300">Shared details</h2>
        <div class="grid gap-5 md:grid-cols-2">
            <label class="field">
                <span>Project date</span>
                <input type="date" name="created_at" value="{{ old('created_at', $editing && $project->created_at ? $project->created_at->format('Y-m-d') : '') }}">
            </label>
            <label class="field">
                <span>Technologies (comma-separated)</span>
                <input type="text" name="technologies" value="{{ old('technologies', $tech) }}" placeholder="Laravel, Tailwind, JavaScript">
            </label>
            <label class="field">
                <span>Live URL</span>
                <input type="url" name="live_url" value="{{ old('live_url', $editing ? $project->live_url : '') }}" placeholder="https://">
            </label>
            <label class="field">
                <span>Repository URL</span>
                <input type="url" name="repo_url" value="{{ old('repo_url', $editing ? $project->repo_url : '') }}" placeholder="https://github.com/...">
            </label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-full px-4 py-3 text-sm font-semibold">{{ $editing ? 'Save project' : 'Create project' }}</button>
</form>
