<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Capability;

use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;

final class Module implements ServiceModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            Capabilities::class => static fn () => Capabilities::fromFilterValue(
                apply_filters(Capabilities::FILTER, Capabilities::DEFAULT_CAPABILITY)
            ),
        ];
    }
}
