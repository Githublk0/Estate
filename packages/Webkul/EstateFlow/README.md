# EstateFlow package for Krayin

This package adds the first production boundary for EstateFlow without modifying Krayin core:

`WebFormController::formStore` → `lead.create.after` → `QualifyWebFormLead` → `LeadQualificationService` → `estateflow_events`.

It owns property and event migrations, deterministic qualification, an API route, and a WebForm listener. The score stays deterministic until an approved model, retrieval corpus, evaluation set, and human-escalation policy are configured.

## Install
```bash
composer dump-autoload
php artisan migrate
php artisan test packages/Webkul/EstateFlow/tests
```

## Production guardrails
- The `qualify-lead` endpoint is restricted to authenticated Krayin users.
- Property queries are globally scoped to the authenticated Krayin user's ID as `agency_id`; unauthenticated property queries return no records.
- Scope every event query to the authenticated tenant.
- Use signed auth context, never client-provided owner IDs.
- Add rate limits, webhook signatures, consent and opt-out checks.
- Keep Krayin's lead record authoritative; EstateFlow stores derived events.
- Do not send AI messages until property facts are approved and retrieval-tested.
- Test rollback against a disposable database before deployment.
