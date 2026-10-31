<x-admin::layouts>
    <x-slot:title>
        @lang('estateflow::app.qualifications.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="rounded-lg border border-gray-300 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <h1 class="text-xl font-bold dark:text-white">
                @lang('estateflow::app.qualifications.title')
            </h1>
        </div>

        <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            @if ($events->isEmpty())
                <p class="py-8 text-center text-gray-600 dark:text-gray-300">
                    @lang('estateflow::app.qualifications.empty')
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-200 text-sm dark:border-gray-800">
                                <th class="p-3">@lang('estateflow::app.qualifications.lead')</th>
                                <th class="p-3">@lang('estateflow::app.qualifications.score')</th>
                                <th class="p-3">@lang('estateflow::app.qualifications.status')</th>
                                <th class="p-3">@lang('estateflow::app.qualifications.recorded')</th>
                                <th class="p-3">@lang('estateflow::app.qualifications.review')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                                @php($result = $event->payload['result'] ?? [])
                                @php($lead = $leads->get($event->lead_id))
                                <tr class="border-b border-gray-100 text-sm dark:border-gray-800">
                                    <td class="p-3">
                                        @if ($lead && bouncer()->hasPermission('leads.view'))
                                            <a class="text-brandColor" href="{{ route('admin.leads.view', $lead->id) }}">
                                                {{ $lead->title }}
                                            </a>
                                        @else
                                            {{ $lead ? $lead->title : ($event->lead_id ? 'Lead #'.$event->lead_id : trans('estateflow::app.qualifications.unlinked')) }}
                                        @endif
                                    </td>
                                    <td class="p-3">{{ $result['score'] ?? '-' }}</td>
                                    <td class="p-3">{{ $result['status'] ?? '-' }}</td>
                                    <td class="p-3">{{ $event->occurred_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                    <td class="p-3">
                                        @lang(($result['needs_human_review'] ?? false)
                                            ? 'estateflow::app.qualifications.review'
                                            : 'estateflow::app.qualifications.no-review')
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin::layouts>
