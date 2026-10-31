<x-admin::layouts>
    <x-slot:title>
        @lang('estateflow::app.properties.show.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <div>
                <a class="text-sm text-brandColor" href="{{ route('admin.estateflow.properties.index') }}">
                    @lang('estateflow::app.properties.show.back')
                </a>
                <h1 class="mt-1 text-xl font-bold dark:text-white">{{ $property->name }}</h1>
            </div>
            @if (bouncer()->hasPermission('estateflow.properties.edit'))
                <a class="primary-button" href="{{ route('admin.estateflow.properties.edit', $property->id) }}">
                    @lang('estateflow::app.properties.show.edit')
                </a>
            @endif
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-300 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-900 dark:text-green-100" role="status">
                {{ session('success') }}
            </div>
        @endif

        <dl class="grid gap-4 rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 md:grid-cols-2">
            <div>
                <dt class="text-sm text-gray-600 dark:text-gray-400">@lang('estateflow::app.properties.fields.address')</dt>
                <dd class="mt-1 dark:text-white">{{ $property->address ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-600 dark:text-gray-400">@lang('estateflow::app.properties.fields.price-cents')</dt>
                <dd class="mt-1 dark:text-white">{{ $property->price_cents ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-600 dark:text-gray-400">@lang('estateflow::app.properties.fields.bedrooms')</dt>
                <dd class="mt-1 dark:text-white">{{ $property->bedrooms ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-600 dark:text-gray-400">@lang('estateflow::app.properties.index.status')</dt>
                <dd class="mt-1 dark:text-white">
                    @lang($property->active ? 'estateflow::app.properties.index.active' : 'estateflow::app.properties.index.inactive')
                </dd>
            </div>
        </dl>
    </div>
</x-admin::layouts>
