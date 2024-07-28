<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Attribute;

use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;

final class Module implements ServiceModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        return [
            AttributeFieldFactory::class => static fn () => new AttributeFieldFactory(),
        ];
    }
}
