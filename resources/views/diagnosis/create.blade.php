@extends('layouts.app')

@section('title', 'Nuevo diagnóstico')

@section('content')
<div class="mx-auto max-w-2xl px-4 py-10">
    <header class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-900">AgroScan</h1>
        <p class="mt-2 text-gray-600">Sube una foto de tu cultivo y obtén un diagnóstico de plagas al instante.</p>
    </header>

    @if (session('error'))
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('diagnosis.store') }}"
        enctype="multipart/form-data"
        x-data="imagePreview()"
        class="space-y-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200"
    >
        @csrf

        {{-- Cultivo --}}
        <div>
            <label for="crop" class="mb-1 block text-sm font-medium text-gray-700">Cultivo</label>
            <select
                id="crop"
                name="crop"
                required
                class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-green-500 focus:ring-green-500"
            >
                <option value="" disabled @selected(! old('crop'))>Selecciona un cultivo…</option>
                @foreach ($crops as $crop)
                    <option value="{{ $crop }}" @selected(old('crop') === $crop)>{{ $crop }}</option>
                @endforeach
            </select>
            @error('crop')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Imagen + preview --}}
        <div>
            <label for="image" class="mb-1 block text-sm font-medium text-gray-700">Imagen del cultivo</label>

            <label
                for="image"
                class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center transition hover:border-green-400 hover:bg-green-50"
            >
                <template x-if="! preview">
                    <span class="text-sm text-gray-500">Toca para seleccionar una imagen (JPG, PNG, WEBP · máx. 5 MB)</span>
                </template>
                <template x-if="preview">
                    <img :src="preview" alt="Vista previa" class="max-h-64 rounded-lg object-contain">
                </template>
            </label>

            <input
                id="image"
                name="image"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                required
                class="hidden"
                @change="updatePreview($event)"
            >
            @error('image')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Ubicación (opcional) --}}
        <div>
            <label for="location" class="mb-1 block text-sm font-medium text-gray-700">
                Ubicación <span class="text-gray-400">(opcional)</span>
            </label>
            <input
                id="location"
                name="location"
                type="text"
                value="{{ old('location') }}"
                maxlength="150"
                placeholder="Ej: Comunidad El Torno, Santa Cruz"
                class="w-full rounded-lg border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-green-500 focus:ring-green-500"
            >
            @error('location')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="w-full rounded-lg bg-green-600 px-4 py-3 font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
        >
            Analizar cultivo
        </button>
    </form>
</div>

@push('scripts')
<script>
    function imagePreview() {
        return {
            preview: null,
            updatePreview(event) {
                const file = event.target.files[0];
                if (! file) { this.preview = null; return; }
                this.preview = URL.createObjectURL(file);
            },
        };
    }
</script>
@endpush
@endsection
