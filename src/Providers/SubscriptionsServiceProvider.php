<?php

declare(strict_types=1);

namespace Wakjoko\Subscriptions\Providers;

use Wakjoko\Subscriptions\Models\Plan;
use Illuminate\Support\ServiceProvider;
use Rinvex\Support\Traits\ConsoleTools;
use Wakjoko\Subscriptions\Models\PlanFeature;
use Wakjoko\Subscriptions\Models\PlanSubscription;
use Wakjoko\Subscriptions\Models\PlanSubscriptionUsage;
use Wakjoko\Subscriptions\Console\Commands\MigrateCommand;
use Wakjoko\Subscriptions\Console\Commands\PublishCommand;
use Wakjoko\Subscriptions\Console\Commands\RollbackCommand;

class SubscriptionsServiceProvider extends ServiceProvider
{
    use ConsoleTools;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        MigrateCommand::class => 'command.wakjoko.subscriptions.migrate',
        PublishCommand::class => 'command.wakjoko.subscriptions.publish',
        RollbackCommand::class => 'command.wakjoko.subscriptions.rollback',
    ];

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'wakjoko.subscriptions');

        // Bind eloquent models to IoC container
        $this->app->singleton('wakjoko.subscriptions.plan', $planModel = $this->app['config']['wakjoko.subscriptions.models.plan']);
        $planModel === Plan::class || $this->app->alias('wakjoko.subscriptions.plan', Plan::class);

        $this->app->singleton('wakjoko.subscriptions.plan_feature', $planFeatureModel = $this->app['config']['wakjoko.subscriptions.models.plan_feature']);
        $planFeatureModel === PlanFeature::class || $this->app->alias('wakjoko.subscriptions.plan_feature', PlanFeature::class);

        $this->app->singleton('wakjoko.subscriptions.plan_subscription', $planSubscriptionModel = $this->app['config']['wakjoko.subscriptions.models.plan_subscription']);
        $planSubscriptionModel === PlanSubscription::class || $this->app->alias('wakjoko.subscriptions.plan_subscription', PlanSubscription::class);

        $this->app->singleton('wakjoko.subscriptions.plan_subscription_usage', $planSubscriptionUsageModel = $this->app['config']['wakjoko.subscriptions.models.plan_subscription_usage']);
        $planSubscriptionUsageModel === PlanSubscriptionUsage::class || $this->app->alias('wakjoko.subscriptions.plan_subscription_usage', PlanSubscriptionUsage::class);

        // Register console commands
        $this->registerCommands($this->commands);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish Resources
        $this->publishesConfig('wakjoko/laravel-subscriptions');
        $this->publishesMigrations('wakjoko/laravel-subscriptions');
        ! $this->autoloadMigrations('wakjoko/laravel-subscriptions') || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
