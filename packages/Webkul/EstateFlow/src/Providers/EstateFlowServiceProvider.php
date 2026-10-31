<?php

namespace Webkul\EstateFlow\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webkul\EstateFlow\Console\Commands\BackfillPropertyAgencies;
use Webkul\EstateFlow\Listeners\QualifyWebFormLead;
use Webkul\EstateFlow\Services\LeadQualificationService;

class EstateFlowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LeadQualificationService::class);

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/menu.php', 'menu.admin');
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/acl.php', 'acl');

        if ($this->app->runningInConsole()) {
            $this->commands([
                BackfillPropertyAgencies::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/routes.php');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'estateflow');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'estateflow');

        Event::listen('lead.create.after', QualifyWebFormLead::class);
    }
}
