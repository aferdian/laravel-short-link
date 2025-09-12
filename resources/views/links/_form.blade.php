@csrf
@isset($link)
    @method('PATCH')
@endisset

<!-- Original URL -->
<div>
    <x-input-label for="original_url" :value="__('Original URL')" />
    <x-text-input id="original_url" class="block mt-1 w-full" type="text" name="original_url" :value="old('original_url', $link->original_url ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('original_url')" class="mt-2" />
</div>

<!-- Custom Alias -->
<div class="mt-4">
    <x-input-label for="alias" :value="__('Custom Alias (Optional)')" />
    <x-text-input id="alias" class="block mt-1 w-full" type="text" name="alias" :value="old('alias', $link->alias ?? '')" />
    <x-input-error :messages="$errors->get('alias')" class="mt-2" />
</div>

<!-- Name -->
<div class="mt-4">
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $link->name ?? '')" />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<!-- Description -->
<div class="mt-4">
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $link->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<!-- Image -->
<div class="mt-4">
    <x-input-label for="image" :value="__('Image URL')" />
    <x-text-input id="image" class="block mt-1 w-full" type="text" name="image" :value="old('image', $link->image ?? '')" />
    <x-input-error :messages="$errors->get('image')" class="mt-2" />
</div>

<!-- Categories -->
<div class="mt-4">
    <x-input-label for="categories" :value="__('Categories')" />
    <select id="categories" name="categories[]" class="block mt-1 w-full" multiple>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @if(isset($link) && $link->categories->contains($category)) selected @endif>{{ $category->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('categories')" class="mt-2" />
</div>

<div class="flex items-center justify-end mt-4">
    <x-secondary-button class="ms-3" onclick="event.preventDefault(); window.location.href='{{ route('links.index') }}'">
        {{ __('Cancel') }}
    </x-secondary-button>
    <x-primary-button class="ms-3">
        {{ isset($link) ? __('Update') : __('Create') }}
    </x-primary-button>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#categories').select2({
            tags: true,
            tokenSeparators: [',']
        });
    });
</script>
@endpush