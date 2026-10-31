<form
    method="POST"
    action="{{ $action }}"
    class="flex flex-col gap-4 rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-300 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-100" role="alert">
            <p class="font-semibold">{{ $errors->first() }}</p>
        </div>
    @endif

    <div class="flex flex-col gap-1">
        <label for="name" class="font-medium dark:text-white">
            @lang('estateflow::app.properties.fields.name')
        </label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $property->name) }}"
            maxlength="255"
            required
            class="rounded border border-gray-300 px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        />
        @error('name')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-col gap-1">
        <label for="address" class="font-medium dark:text-white">
            @lang('estateflow::app.properties.fields.address')
        </label>
        <input
            id="address"
            name="address"
            type="text"
            value="{{ old('address', $property->address) }}"
            maxlength="255"
            class="rounded border border-gray-300 px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        />
        @error('address')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="flex flex-col gap-1">
            <label for="price_cents" class="font-medium dark:text-white">
                @lang('estateflow::app.properties.fields.price-cents')
            </label>
            <input
                id="price_cents"
                name="price_cents"
                type="number"
                min="0"
                step="1"
                value="{{ old('price_cents', $property->price_cents) }}"
                class="rounded border border-gray-300 px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
            @error('price_cents')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col gap-1">
            <label for="bedrooms" class="font-medium dark:text-white">
                @lang('estateflow::app.properties.fields.bedrooms')
            </label>
            <input
                id="bedrooms"
                name="bedrooms"
                type="number"
                min="0"
                max="65535"
                step="1"
                value="{{ old('bedrooms', $property->bedrooms) }}"
                class="rounded border border-gray-300 px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            />
            @error('bedrooms')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="primary-button">
            {{ $submitLabel }}
        </button>
    </div>
</form>
