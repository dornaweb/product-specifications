<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

use Amiut\ProductSpecs\Upgrade\DataVersionRepository;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Psr\Container\ContainerInterface;

final class Module implements ServiceModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            MigrationRunRepository::class => static fn () => new MigrationRunRepository(),
            MigrationRunner::class => static fn (ContainerInterface $container) => new MigrationRunner(
                $container->get(MigrationRunRepository::class),
                $container->get(DataVersionRepository::class),
                []
            ),
        ];
    }
}
