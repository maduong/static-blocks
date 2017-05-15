<?php namespace Edutalk\Base\StaticBlocks\Providers;

use Illuminate\Support\ServiceProvider;
use Edutalk\Base\StaticBlocks\Models\StaticBlock;
use Edutalk\Base\StaticBlocks\Repositories\Contracts\StaticBlockRepositoryContract;
use Edutalk\Base\StaticBlocks\Repositories\StaticBlockRepository;
use Edutalk\Base\StaticBlocks\Repositories\StaticBlockRepositoryCacheDecorator;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(StaticBlockRepositoryContract::class, function () {
            $repository = new StaticBlockRepository(new StaticBlock());

            if (config('edutalk-caching.repository.enabled')) {
                return new StaticBlockRepositoryCacheDecorator($repository);
            }

            return $repository;
        });
    }
}
