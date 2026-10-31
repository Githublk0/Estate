<?php

namespace Webkul\EstateFlow\Http\Controllers\Admin;

use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\EstateFlow\Models\Event;
use Webkul\Lead\Repositories\LeadRepository;

class QualificationHistoryController extends Controller
{
    public function __invoke(LeadRepository $leadRepository): View
    {
        $events = Event::query()
            ->where('user_id', auth()->guard('user')->id())
            ->where('event_type', 'lead.qualified')
            ->latest('occurred_at')
            ->paginate(30);

        $leadIds = $events->getCollection()
            ->pluck('lead_id')
            ->filter()
            ->unique()
            ->values();

        $leads = collect();

        if ($leadIds->isNotEmpty()) {
            $query = $leadRepository->getModel()
                ->newQuery()
                ->whereIn('id', $leadIds);

            $authorizedUserIds = bouncer()->getAuthorizedUserIds();

            if ($authorizedUserIds !== null) {
                $query->whereIn('user_id', $authorizedUserIds);
            }

            $leads = $query->get(['id', 'title'])->keyBy('id');
        }

        return view('estateflow::admin.qualifications.index', [
            'events' => $events,
            'leads' => $leads,
        ]);
    }
}
