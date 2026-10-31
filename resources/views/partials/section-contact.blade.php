@php
    $contactEmail = config('portfolio.contact_email');
    $oldFeatures = old('features', []);
@endphp

<section id="contact" class="section-anchor px-4 py-24">
    <div class="mx-auto max-w-6xl">
        <header class="reveal mb-12">
            <p class="eyebrow">{{ __('site.contact.eyebrow') }}</p>
            <h2 class="section-title">{{ __('site.contact.title') }}</h2>
            <p class="mt-3 max-w-2xl text-slate-400">{{ __('site.contact.intro') }}</p>
        </header>

        <div class="grid gap-8 lg:grid-cols-[1.4fr_0.6fr]">
            <form method="POST" action="{{ route('contact.send') }}" class="reveal glass space-y-5 rounded-3xl p-6 md:p-8">
                <input type="hidden" name="_ft" value="{{ \App\Support\FormToken::make() }}">
                <input type="hidden" name="_locale" value="{{ app()->getLocale() }}">
                <div class="hidden" aria-hidden="true">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                @if (session('contact_sent'))
                    <p role="status" tabindex="-1" data-feedback class="rounded-2xl border border-emerald-400/40 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">{{ __('site.contact.sent') }}</p>
                @endif
                @if (session('contact_expired'))
                    <p role="alert" tabindex="-1" data-feedback class="rounded-2xl border border-amber-400/40 bg-amber-400/10 px-4 py-3 text-sm text-amber-200">{{ __('site.contact.expired') }}</p>
                @endif
                @if (session('contact_error'))
                    <p role="alert" tabindex="-1" data-feedback class="rounded-2xl border border-rose-400/40 bg-rose-400/10 px-4 py-3 text-sm text-rose-200">{{ __('site.contact.error') }}</p>
                @endif
                @if ($errors->any())
                    @php $fieldIds = ['name' => 'c-name', 'email' => 'c-email', 'project_type' => 'c-type', 'description' => 'c-description']; @endphp
                    <div role="alert" tabindex="-1" data-feedback class="rounded-2xl border border-rose-400/40 bg-rose-400/10 px-4 py-3 text-sm text-rose-200">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->keys() as $key)
                                <li>
                                    @if (isset($fieldIds[$key]))
                                        <a href="#{{ $fieldIds[$key] }}" class="underline underline-offset-2">{{ $errors->first($key) }}</a>
                                    @else
                                        {{ $errors->first($key) }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="field">
                        <span>{{ __('site.contact.name') }}</span>
                        <input type="text" id="c-name" name="name" value="{{ old('name') }}" required aria-required="true" maxlength="120" autocomplete="name" @error('name') aria-invalid="true" aria-describedby="err-name" @enderror>
                        @error('name')<small id="err-name" class="field-error">{{ $message }}</small>@enderror
                    </label>
                    <label class="field">
                        <span>{{ __('site.contact.email') }}</span>
                        <input type="email" id="c-email" name="email" value="{{ old('email') }}" required aria-required="true" maxlength="190" autocomplete="email" @error('email') aria-invalid="true" aria-describedby="err-email" @enderror>
                        @error('email')<small id="err-email" class="field-error">{{ $message }}</small>@enderror
                    </label>
                </div>

                <label class="field">
                    <span>{{ __('site.contact.type') }}</span>
                    <select id="c-type" name="project_type" required aria-required="true" @error('project_type') aria-invalid="true" aria-describedby="err-type" @enderror>
                        <option value="" disabled @selected(!old('project_type'))>{{ __('site.contact.select') }}</option>
                        @foreach (__('site.contact.types') as $value => $label)
                            <option value="{{ $value }}" @selected(old('project_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('project_type')<small id="err-type" class="field-error">{{ $message }}</small>@enderror
                </label>

                <label class="field">
                    <span>{{ __('site.contact.description') }}</span>
                    <textarea id="c-description" name="description" rows="5" required aria-required="true" maxlength="3000" placeholder="{{ __('site.contact.description_ph') }}" @error('description') aria-invalid="true" aria-describedby="err-description" @enderror>{{ old('description') }}</textarea>
                    @error('description')<small id="err-description" class="field-error">{{ $message }}</small>@enderror
                </label>

                <label class="field">
                    <span>{{ __('site.contact.method') }}</span>
                    <select name="contact_method">
                        <option value="">{{ __('site.contact.select') }}</option>
                        @foreach (__('site.contact.methods') as $value => $label)
                            <option value="{{ $value }}" @selected(old('contact_method') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <details class="more-fields" @if (old('style') || old('features') || old('deadline')) open @endif>
                    <summary class="cursor-pointer font-semibold text-sky-300 hover:text-white">{{ __('site.contact.more') }}</summary>
                    <div class="mt-5 space-y-5">
                <label class="field">
                    <span>{{ __('site.contact.style') }}</span>
                    <textarea name="style" rows="3" maxlength="1000" placeholder="{{ __('site.contact.style_ph') }}">{{ old('style') }}</textarea>
                </label>

                <fieldset>
                    <legend class="field-label">{{ __('site.contact.features') }}</legend>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach (__('site.contact.feat') as $value => $label)
                            <label class="check-chip">
                                <input type="checkbox" name="features[]" value="{{ $value }}" @checked(in_array($value, $oldFeatures))>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="field">
                        <span>{{ __('site.contact.deadline') }}</span>
                        <input type="text" name="deadline" value="{{ old('deadline') }}" maxlength="120" placeholder="{{ __('site.contact.deadline_ph') }}">
                    </label>
                </div>

                    </div>
                </details>

                <button type="submit" class="btn btn-primary px-8 py-3 text-sm font-semibold">{{ __('site.contact.send') }}</button>
                <p class="text-xs leading-5 text-slate-400">{!! str_replace(':link', '<a href="' . e(\App\Http\Middleware\SetLocale::privacyUrl()) . '" class="text-sky-300 underline underline-offset-2 hover:text-white">' . e(__('site.privacy.link_text')) . '</a>', e(__('site.contact.privacy_note'))) !!}</p>
            </form>

            <aside class="reveal glass h-fit space-y-5 rounded-3xl p-6" style="--d:120ms">
                <h3 class="text-lg font-semibold text-white">{{ __('site.contact.direct') }}</h3>
                <a href="https://wa.me/{{ preg_replace('/\D+/', '', config('portfolio.phone')) }}?text={{ rawurlencode(__('site.contact.whatsapp_text')) }}" target="_blank" rel="noopener noreferrer" data-track="whatsapp" class="btn btn-primary flex w-full items-center justify-center px-5 py-3 text-sm font-semibold">{{ __('site.contact.whatsapp') }}</a>
                <a href="mailto:{{ $contactEmail }}" data-track="email" class="contact-link">
                    <span class="text-sm text-slate-400">Email</span>
                    <span class="break-all">{{ $contactEmail }}</span>
                </a>
                <a href="tel:{{ preg_replace('/\s+/', '', config('portfolio.phone')) }}" data-track="phone" class="contact-link">
                    <span class="text-sm text-slate-400">Phone</span>
                    <span>{{ config('portfolio.phone') }}</span>
                </a>
                <div class="contact-link">
                    <span class="text-sm text-slate-400">Location</span>
                    <span>{{ __('site.contact.location') }}</span>
                </div>
                <div class="flex flex-wrap gap-2 pt-2">
                    <a href="{{ config('portfolio.linkedin') }}" target="_blank" rel="noopener noreferrer" class="btn btn-ghost px-4 py-2 text-sm">LinkedIn</a>
                    <a href="{{ config('portfolio.github') }}" target="_blank" rel="noopener noreferrer" class="btn btn-ghost px-4 py-2 text-sm">GitHub</a>
                </div>
            </aside>
        </div>
    </div>
</section>
