<x-admin::layouts>
    <x-slot:title>
        @lang('estateflow::app.properties.index.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <h1 class="text-xl font-bold dark:text-white">
                @lang('estateflow::app.properties.index.title')
            </h1>

            @if (bouncer()->hasPermission('estateflow.properties.create'))
                <a
                    href="{{ route('admin.estateflow.properties.create') }}"
                    class="primary-button"
                >
                    @lang('estateflow::app.properties.index.create-btn')
                </a>
            @endif
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-300 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-900 dark:text-green-100" role="status">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            @if ($properties->isEmpty())
                <p class="py-8 text-center text-gray-600 dark:text-gray-300">
                    @lang('estateflow::app.properties.index.empty')
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-200 text-sm dark:border-gray-800">
                                <th class="p-3">@lang('estateflow::app.properties.index.name')</th>
                                <th class="p-3">@lang('estateflow::app.properties.index.address')</th>
                                <th class="p-3">@lang('estateflow::app.properties.index.price')</th>
                                <th class="p-3">@lang('estateflow::app.properties.index.bedrooms')</th>
                                <th class="p-3">@lang('estateflow::app.properties.index.status')</th>
                                <th class="p-3">@lang('estateflow::app.properties.index.actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($properties as $property)
                                <tr class="border-b border-gray-100 text-sm dark:border-gray-800">
                                    <td class="p-3 font-medium">{{ $property->name }}</td>
                                    <td class="p-3">{{ $property->address ?: '-' }}</td>
                                    <td class="p-3">{{ $property->price_cents ?? '-' }}</td>
                                    <td class="p-3">{{ $property->bedrooms ?? '-' }}</td>
                                    <td class="p-3">
                                        @lang($property->active ? 'estateflow::app.properties.index.active' : 'estateflow::app.properties.index.inactive')
                                    </td>
                                    <td class="p-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a class="secondary-button" href="{{ route('admin.estateflow.properties.show', $property->id) }}">
                                                @lang('estateflow::app.properties.index.view')
                                            </a>
                                            @if (bouncer()->hasPermission('estateflow.properties.edit'))
                                                <a class="secondary-button" href="{{ route('admin.estateflow.properties.edit', $property->id) }}">
                                                    @lang('estateflow::app.properties.index.edit')
                                                </a>
                                                <form method="POST" action="{{ route('admin.estateflow.properties.status', $property->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="secondary-button" type="submit">
                                                        @lang($property->active ? 'estateflow::app.properties.index.deactivate' : 'estateflow::app.properties.index.activate')
                                                    </button>
                                                </form>
                                            @endif
                                            @if (bouncer()->hasPermission('estateflow.properties.delete'))
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.estateflow.properties.destroy', $property->id) }}"
                                                    onsubmit="return confirm(@js(trans('estateflow::app.properties.index.delete-confirm')))"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="secondary-button" type="submit">
                                                        @lang('estateflow::app.properties.index.delete')
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $properties->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin::layouts>
