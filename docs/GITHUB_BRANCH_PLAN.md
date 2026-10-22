# EstateFlow GitHub-ready branch plan

## Repository

Private repository name: `estateflow-revenue-os`.

Krayin remains the upstream CRM foundation. EstateFlow is an isolated package under `packages/Webkul/EstateFlow`; do not merge Twenty or rewrite Krayin core.

## Branches

- `main`: production-ready code only, protected, CI required.
- `develop`: integration branch for reviewed work.
- `feature/estateflow-foundation`: package, migrations, qualification service, provider, routes, and tests.
- `feature/webform-qualification`: WebForm event listener and qualification integration.
- `feature/property-inventory`: property model, admin UI, import, and tenant authorization.
- `feature/follow-up-routing`: assignment rules, queues, reminders, and notifications.
- `feature/viewing-booking`: calendar integration and viewing lifecycle.
- `feature/roi-reporting`: event aggregation, attribution, and dashboard metrics.
- `feature/security-consent`: consent, opt-out, rate limiting, signed webhooks, audit review.
- `feature/billing`: Stripe pilot plan, subscription webhooks, entitlements, and failed-payment handling.
- `release/pilot-0.1`: staging candidate for one US agency.

## Commit order

1. `chore: import Krayin 2.2 baseline`
2. `docs: add EstateFlow architecture and attribution`
3. `feat: add EstateFlow package foundation`
4. `feat: qualify WebForm leads through EstateFlow`
5. `test: cover tenant isolation and qualification`
6. `feat: add property inventory and viewing lifecycle`
7. `feat: add follow-up routing and notifications`
8. `feat: add ROI attribution dashboard`
9. `security: enforce consent and webhook safeguards`
10. `feat: add pilot billing and entitlements`
11. `ops: add staging deployment and monitoring`
12. `release: EstateFlow pilot 0.1`

## Safe bootstrap commands

```bash
unzip laravel-crm-2.2.zip
cd laravel-crm-2.2
git init
git checkout -b main
git add .
git commit -m "chore: import Krayin 2.2 baseline"
git remote add origin git@github.com:YOUR_ACCOUNT/estateflow-revenue-os.git
git push -u origin main

git checkout -b develop
git push -u origin develop
```

Before the first commit, inspect `git status`, remove `.env`, customer data, database dumps, `vendor`, and `node_modules`, then run a secret scan. Keep the upstream Krayin license and add `ESTATEFLOW_ATTRIBUTIONS.md`.

## Pull request gates

Every PR must pass Composer install, Laravel migrations, EstateFlow tests, baseline tests, frontend build, formatting, and secret scanning. A pilot release additionally requires tenant-isolation tests, consent/opt-out tests, webhook replay protection, backup/restore verification, and a staging smoke test.

## Rollback

Tag every release. Roll back application code and database migrations together. Never run destructive migrations against production without a tested backup and downgrade/forward plan.

## Current limitation

No GitHub repository was created or pushed because no user-owned GitHub URL or authorization is available. PHP and Composer are also unavailable in the current execution environment, so CI is prepared but not locally executed.
