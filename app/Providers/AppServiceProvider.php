<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Observers\OrderObserver;
use App\Observers\ProductVariantObserver;
use Asignua\FilamentActivityLogPlus\ActivityBatch;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Actions\ActionGroup;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('checkout', fn (Request $request): array => [
            Limit::perMinute(5)->by('customer:'.$request->user()->getAuthIdentifier()),
            Limit::perMinute(20)->by('ip:'.$request->ip()),
        ]);

        LanguageSwitch::configureUsing(fn (LanguageSwitch $switch): LanguageSwitch => $switch
            ->locales(['en', 'km'])
            ->labels([
                'en' => 'English',
                'km' => 'ខ្មែរ',
            ])
            ->visible(outsidePanels: true));

        FilamentShield::enforcePolicies();
        ActionGroup::configureUsing(function (ActionGroup $group): void {
            foreach ($group->getFlatActions() as $action) {
                if ($action->getName() === 'view_batch') {
                    $action->grouped();
                }
            }
        });
        ProductVariant::observe(ProductVariantObserver::class);
        Order::observe(OrderObserver::class);

        $activityModel = config('activitylog.activity_model');
        $activityModel::creating(function (Model $activity): void {
            if ((! app()->runningInConsole() || app(ActivityBatch::class)->isHttp()) && app()->bound('request')) {
                $activity->setAttribute('properties', collect($activity->getAttribute('properties'))
                    ->put('user_agent', mb_substr(request()->userAgent() ?? '', 0, 2048)));
            }
        });
    }
}
