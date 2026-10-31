<?php

namespace Tests\Feature\EstateFlow;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Webkul\EstateFlow\Models\Event;
use Webkul\Lead\Models\Lead;
use Webkul\WebForm\Models\WebForm;
use Webkul\WebForm\Models\WebFormAttribute;

class WebFormQualificationIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_web_form_intake_records_estateflow_qualification(): void
    {
        securityTestPipeline();

        $webForm = WebForm::create([
            'form_id' => bin2hex(random_bytes(8)),
            'title' => 'EstateFlow qualification integration',
            'description' => 'Qualification test form.',
            'submit_button_label' => 'Send',
            'submit_success_action' => 'message',
            'submit_success_content' => 'Thanks.',
            'create_lead' => 1,
        ]);

        foreach ([
            'leads.title',
            'leads.budget',
            'leads.location',
            'leads.property_type',
            'leads.timeline',
            'leads.wants_viewing',
            'persons.name',
            'persons.emails',
        ] as $sortOrder => $qualifiedCode) {
            [$entityType, $code] = explode('.', $qualifiedCode, 2);

            $attribute = webFormAttributeFor($entityType, $code);

            WebFormAttribute::create([
                'name' => $code,
                'is_required' => 0,
                'is_hidden' => 0,
                'sort_order' => $sortOrder + 1,
                'attribute_id' => $attribute->id,
                'web_form_id' => $webForm->id,
            ]);
        }

        $title = 'EstateFlow intake '.bin2hex(random_bytes(5));

        $this->post('/web-forms/forms/'.$webForm->id, [
            'leads' => [
                'title' => $title,
                'budget' => '450000',
                'location' => 'Austin',
                'property_type' => 'Condo',
                'timeline' => '30 days',
                'wants_viewing' => true,
            ],
            'persons' => [
                'name' => 'EstateFlow Visitor',
                'emails' => [[
                    'value' => 'estateflow-'.bin2hex(random_bytes(5)).'@example.invalid',
                    'label' => 'work',
                ]],
            ],
        ])->assertOk()->assertJson(['message' => 'Thanks.']);

        $lead = Lead::query()->where('title', $title)->firstOrFail();

        $event = Event::query()
            ->where('lead_id', $lead->id)
            ->where('event_type', 'lead.qualified')
            ->firstOrFail();

        $this->assertSame('Hot', $event->payload['result']['status']);
        $this->assertSame(100, $event->payload['result']['score']);
        $this->assertSame('webform', $event->channel);
    }
}
