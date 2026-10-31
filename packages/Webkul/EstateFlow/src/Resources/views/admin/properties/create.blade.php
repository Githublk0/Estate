<x-admin::layouts>
    <x-slot:title>
        @lang('estateflow::app.properties.create.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <h1 class="text-xl font-bold dark:text-white">
                @lang('estateflow::app.properties.create.title')
            </h1>
            <a class="secondary-button" href="{{ route('admin.estateflow.properties.index') }}">
                @lang('estateflow::app.properties.show.back')
            </a>
        </div>

        @include('estateflow::admin.properties.form', [
            'action' => route('admin.estateflow.properties.store'),
            'method' => 'POST',
            'submitLabel' => trans('estateflow::app.properties.create.save'),
        ])
    </div>
</x-admin::layouts>
