@extends('layouts.app')

@section('title', 'Nuevo diagnóstico')

@section('content')

<div
    x-data="{
        preview:  null,
        fileName: null,
        loading:  false,
        dragOver: false,

        updatePreview(e) {
            const f = e.target.files[0];
            if (!f) { this.preview = null; this.fileName = null; return; }
            this.preview  = URL.createObjectURL(f);
            this.fileName = f.name;
        },

        handleDrop(e) {
            this.dragOver = false;
            const f = e.dataTransfer.files[0];
            if (!f || !f.type.startsWith('image/')) return;
            const dt = new DataTransfer();
            dt.items.add(f);
            this.$refs.fileInput.files = dt.files;
            this.preview  = URL.createObjectURL(f);
            this.fileName = f.name;
        },
    }"
>

    {{-- ── Loading overlay ── --}}
    <div
        x-show="loading"
        x-cloak
        x-transition:enter="transition-opacity duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-50 flex items-center justify-center px-5"
        style="background: rgba(10, 22, 4, 0.80); backdrop-filter: blur(7px);"
    >
        <div class="w-full max-w-sm rounded-2xl bg-white px-8 py-9 text-center shadow-2xl"
             style="border: 1px solid #e4ddd1;">

            {{-- Radar rings --}}
            <div class="relative mx-auto mb-7 h-24 w-24">
                <div class="absolute inset-0 rounded-full"
                     style="border: 2px solid #6cb33e; animation: pulse-ring 2.2s ease-out 0s infinite;"></div>
                <div class="absolute inset-0 rounded-full"
                     style="border: 2px solid #6cb33e; animation: pulse-ring 2.2s ease-out 0.73s infinite;"></div>
                <div class="absolute inset-0 rounded-full"
                     style="border: 2px solid #6cb33e; animation: pulse-ring 2.2s ease-out 1.46s infinite;"></div>

                {{-- Center icon --}}
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full"
                         style="background: #eef7e4; border: 1.5px solid #c8e8a0;">
                        <svg width="34" height="34" viewBox="0 0 34 34" fill="none"
                             style="animation: leaf-breathe 2.2s ease-in-out infinite;">
                            <path d="M6 29C6 29 10 14 26 8C26 8 28 24 17 28C11.5 30 7 29 6 29Z" fill="#4e8a1e"/>
                            <path d="M6 29C11 21.5 16 16 26 8" stroke="#2a5c0f" stroke-width="2.2" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
            </div>

            <p class="mb-1.5 text-xl font-semibold tracking-tight" style="font-family: 'Fraunces', Georgia, serif; color: #192d0b;">
                Analizando tu cultivo
            </p>
            <p class="mb-7 text-sm leading-relaxed" style="color: #7a7264;">
                La IA está evaluando la imagen.<br>Esto puede tardar unos segundos.
            </p>

            <div class="flex justify-center gap-2.5">
                <div class="h-2.5 w-2.5 rounded-full"
                     style="background: #4e8a1e; animation: dot-pulse 1.5s ease-in-out 0s infinite;"></div>
                <div class="h-2.5 w-2.5 rounded-full"
                     style="background: #4e8a1e; animation: dot-pulse 1.5s ease-in-out 0.22s infinite;"></div>
                <div class="h-2.5 w-2.5 rounded-full"
                     style="background: #4e8a1e; animation: dot-pulse 1.5s ease-in-out 0.44s infinite;"></div>
            </div>
        </div>
    </div>

    {{-- ── Page content ── --}}
    <div class="mx-auto max-w-lg px-4 pb-12 pt-10">

        {{-- Title --}}
        <div class="mb-8 text-center" style="animation: slide-up 0.4s ease-out both;">
            <p class="mb-2 text-xs font-bold uppercase tracking-widest" style="color: #6cb33e; letter-spacing: 0.14em;">
                Diagnóstico con IA
            </p>
            <h1 class="text-4xl font-bold leading-tight tracking-tight"
                style="font-family: 'Fraunces', Georgia, serif; color: #192d0b;">
                Analiza tu cultivo
            </h1>
            <p class="mt-3 text-base leading-relaxed" style="color: #7a7264;">
                Sube una foto y detectamos plagas al instante.
            </p>
        </div>

        {{-- Error flash --}}
        @if (session('error'))
            <div class="mb-5 flex items-start gap-3 rounded-xl px-4 py-3.5"
                 style="background: #fff1f0; border: 1px solid #fecaca; animation: slide-up 0.4s ease-out both;">
                <svg class="mt-0.5 shrink-0" width="18" height="18" viewBox="0 0 20 20" fill="#ef4444">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a1 1 0 011 1v4a1 1 0 11-2 0V6a1 1 0 011-1zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm" style="color: #b91c1c;">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Form card --}}
        <form
            method="POST"
            action="{{ route('diagnosis.store') }}"
            enctype="multipart/form-data"
            @submit="loading = true"
            class="space-y-5 rounded-2xl bg-white px-6 py-7"
            style="border: 1px solid #e4ddd1; box-shadow: 0 2px 16px rgba(25,45,11,0.07); animation: slide-up 0.5s ease-out 0.1s both;"
        >
            @csrf

            {{-- Cultivo --}}
            <div>
                <label for="crop" class="mb-2 block text-sm font-semibold" style="color: #2d4016;">Cultivo</label>
                <div class="relative">
                    <select
                        id="crop"
                        name="crop"
                        required
                        class="w-full appearance-none rounded-xl px-4 py-3 pr-10 text-base transition-colors focus:outline-none"
                        style="border: 2px solid #ddd5c4; background: #fdfcf8; color: #1c1a12; cursor: pointer;"
                        onfocus="this.style.borderColor='#4e8a1e'"
                        onblur="this.style.borderColor='#ddd5c4'"
                    >
                        <option value="" disabled @selected(! old('crop'))>Selecciona un cultivo…</option>
                        @foreach ($crops as $crop)
                            <option value="{{ $crop }}" @selected(old('crop') === $crop)>{{ $crop }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M4 6l4 4 4-4" stroke="#8a8070" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                @error('crop')
                    <p class="mt-1.5 text-sm" style="color: #dc2626;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Drop zone --}}
            <div>
                <label for="image" class="mb-2 block text-sm font-semibold" style="color: #2d4016;">
                    Imagen del cultivo
                </label>

                <label
                    for="image"
                    class="relative flex min-h-56 cursor-pointer items-center justify-center overflow-hidden rounded-xl transition-all duration-200"
                    :style="dragOver
                        ? 'border: 2px dashed #4e8a1e; background: #eef7e4;'
                        : (preview
                            ? 'border: 2px solid #4e8a1e; background: #000;'
                            : 'border: 2px dashed #c6bcad; background: #faf7f0;')"
                    @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="handleDrop($event)"
                >
                    {{-- Empty state --}}
                    <template x-if="! preview">
                        <div class="flex flex-col items-center gap-3 px-4 py-8 text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl transition-colors duration-200"
                                 :style="dragOver ? 'background: #d0f0a0;' : 'background: #e8f5d4;'">
                                <svg width="34" height="34" viewBox="0 0 34 34" fill="none">
                                    <path d="M6 28C6 28 10 14 26 8C26 8 28 23 17 27C12 29 7.5 28 6 28Z"
                                          :fill="dragOver ? '#4e8a1e' : '#90c84a'"/>
                                    <path d="M6 28C11 21 16 15.5 26 8" stroke="#4e8a1e" stroke-width="2.2" stroke-linecap="round"/>
                                    <circle cx="26" cy="8" r="2.5" fill="#4e8a1e" opacity="0.5"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold" style="color: #2d4016;">
                                    <span x-text="dragOver ? 'Suelta la imagen aquí' : 'Toca para seleccionar'"></span>
                                </p>
                                <p class="mt-1 text-xs" style="color: #a09080;">JPG, PNG, WEBP · máx. 5 MB</p>
                            </div>
                        </div>
                    </template>

                    {{-- Preview state --}}
                    <template x-if="preview">
                        <div class="absolute inset-0">
                            <img :src="preview" alt="Vista previa"
                                 class="h-full w-full object-cover" style="opacity: 0.82;">
                            <div class="absolute inset-0"
                                 style="background: linear-gradient(to top, rgba(10,22,4,0.72) 0%, transparent 55%);">
                            </div>
                            {{-- Filename chip --}}
                            <div class="absolute bottom-3.5 left-0 right-0 flex justify-center">
                                <div class="flex items-center gap-1.5 rounded-full px-3 py-1.5"
                                     style="background: rgba(255,255,255,0.15); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.25);">
                                    <svg width="13" height="13" viewBox="0 0 14 14" fill="none">
                                        <circle cx="7" cy="7" r="6" stroke="#6da535" stroke-width="1.5"/>
                                        <path d="M4.5 7l2 2 3-3.5" stroke="#6da535" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="max-w-[180px] truncate text-xs font-medium text-white" x-text="fileName"></span>
                                </div>
                            </div>
                            {{-- Hover to change --}}
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity duration-200 hover:opacity-100"
                                 style="background: rgba(10,22,4,0.45);">
                                <span class="rounded-full bg-white px-5 py-2.5 text-sm font-semibold"
                                      style="color: #2a5c0f; box-shadow: 0 2px 12px rgba(0,0,0,0.2);">
                                    Cambiar imagen
                                </span>
                            </div>
                        </div>
                    </template>
                </label>

                <input
                    x-ref="fileInput"
                    id="image"
                    name="image"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    required
                    class="hidden"
                    @change="updatePreview($event)"
                >
                @error('image')
                    <p class="mt-1.5 text-sm" style="color: #dc2626;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Ubicación --}}
            <div>
                <label for="location" class="mb-2 block text-sm font-semibold" style="color: #2d4016;">
                    Ubicación
                    <span class="ml-1 font-normal" style="color: #a09080;">(opcional)</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="#a09080" stroke-width="1.6">
                            <path d="M10 11.5a3 3 0 100-6 3 3 0 000 6z"/>
                            <path d="M10 2C6.7 2 4 4.7 4 8c0 5 6 10 6 10s6-5 6-10c0-3.3-2.7-6-6-6z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <input
                        id="location"
                        name="location"
                        type="text"
                        value="{{ old('location') }}"
                        maxlength="150"
                        placeholder="Ej: Comunidad El Torno, Santa Cruz"
                        class="w-full rounded-xl py-3 pl-10 pr-4 text-base transition-colors focus:outline-none"
                        style="border: 2px solid #ddd5c4; background: #fdfcf8; color: #1c1a12;"
                        onfocus="this.style.borderColor='#4e8a1e'"
                        onblur="this.style.borderColor='#ddd5c4'"
                    >
                </div>
                @error('location')
                    <p class="mt-1.5 text-sm" style="color: #dc2626;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                :disabled="loading"
                class="w-full rounded-xl px-6 py-4 text-base font-semibold text-white transition-all duration-150"
                :class="loading
                    ? 'cursor-not-allowed opacity-75'
                    : 'hover:brightness-110 active:scale-[0.985]'"
                style="background: linear-gradient(135deg, #2a5c0f 0%, #1e4309 100%); box-shadow: 0 2px 12px rgba(25,45,11,0.35);"
            >
                <span x-show="! loading" class="flex items-center justify-center gap-2.5">
                    <svg width="19" height="19" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="9" cy="9" r="5.5"/>
                        <path d="M13.5 13.5l3.5 3.5" stroke-linecap="round"/>
                        <path d="M9 7v4M7 9h4" stroke-linecap="round"/>
                    </svg>
                    Analizar cultivo
                </span>
                <span x-show="loading" x-cloak class="flex items-center justify-center gap-2.5">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                         style="animation: spin 0.9s linear infinite;">
                        <circle cx="12" cy="12" r="10" stroke="white" stroke-width="3" opacity="0.25"/>
                        <path d="M12 2a10 10 0 0110 10" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    Analizando…
                </span>
            </button>
        </form>

        <p class="mt-5 text-center text-xs" style="color: #a09080;">
            Las imágenes se procesan de forma segura y no se comparten con terceros.
        </p>
    </div>

</div>
@endsection
