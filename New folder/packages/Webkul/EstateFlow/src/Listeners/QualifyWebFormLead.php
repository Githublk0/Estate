<?php
namespace Webkul\EstateFlow\Listeners;

use Illuminate\Http\Request;
use Webkul\EstateFlow\Services\LeadQualificationService;

class QualifyWebFormLead
{
    public function __construct(private LeadQualificationService $qualification) {}

    public function handle($lead): void
    {
        $request = app(Request::class);
        $input = (array) $request->input('leads', []);
        $answers = [
            'budget'        => $input['budget'] ?? $input['budget_range'] ?? null,
            'location'      => $input['location'] ?? null,
            'property_type' => $input['property_type'] ?? $input['type'] ?? null,
            'timeline'      => $input['timeline'] ?? null,
            'wants_viewing' => filter_var($input['wants_viewing'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];

        $result = $this->qualification->record(
            (int) $lead->id,
            $lead->user_id ? (int) $lead->user_id : null,
            $answers,
            'webform'
        );

        // Keep Krayin's lead model authoritative while adding EstateFlow metadata.
        $lead->updateQuietly([
            'lead_value' => $lead->lead_value ?? 0,
        ]);
    }
}
