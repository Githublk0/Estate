<?php
namespace Webkul\EstateFlow\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Webkul\EstateFlow\Listeners\QualifyWebFormLead;
class EstateFlowServiceProvider extends ServiceProvider {
 public function register(): void { $this->app->singleton(\Webkul\EstateFlow\Services\LeadQualificationService::class); }
 public function boot(): void {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/routes.php');
        Event::listen('lead.create.after', QualifyWebFormLead::class);
    }
}
