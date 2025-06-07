<?php

namespace App\Providers;

use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Twitter\Services\TwitterService;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TwitterService::class, function ($app) {
            return new TwitterService();
        });

        Collection::macro('optimizeReferences', function () {
            /** @var Collection $this */
            $keyed = $this->keyBy('id');
            $tree = collect();

            foreach ($this as $item) {
                $item->setRelation('prefetchedReferences', new \Illuminate\Database\Eloquent\Collection);
                $pathSegments = array_filter(explode('/', $item->ref_path));
                $parentId = $pathSegments ? end($pathSegments) : null;

                if ($parentId && ($parentId = prev($pathSegments)) && $keyed->has($parentId)) {
                    $item->pivot = new \stdClass;
                    $item->pivot->ref_type = ReferenceType::from($item->ref_type);
                    $keyed[$parentId]->prefetchedReferences->push($item);
                } else {
                    $tree->push($item);
                }
            }

            return $tree;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
