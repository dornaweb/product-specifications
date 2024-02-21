<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Repository;

use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;

final class Module implements ServiceModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            SpecificationsTableRepository::class => static fn () => new SpecificationsTableRepository(),
        ];
    }
}
