<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Link;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\OptionsSchema;
use Amiut\ProductSpecs\Specification\Type\SpecificationType;
use WC_Product;

final class LinkType implements SpecificationType
{
    public const ID = 'link';

    public function id(): string
    {
        return self::ID;
    }

    public function label(): string
    {
        return __('Link', 'product-specifications');
    }

    /**
     * @return array<string, mixed>
     */
    public function optionsSchema(): array
    {
        return OptionsSchema::none();
    }

    public function sanitize(mixed $input, SpecificationOptions $options): mixed
    {
        return Link::fromData($input)?->toStorage();
    }

    public function validate(mixed $input, SpecificationOptions $options): ?string
    {
        if (Link::isEmpty($input) || Link::fromData($input) !== null) {
            return null;
        }

        return __('Enter a URL that starts with https://, http://, mailto: or tel:.', 'product-specifications');
    }

    public function value(WC_Product $product, mixed $stored, SpecificationOptions $options): mixed
    {
        return Link::fromData($stored)?->toStorage();
    }

    public function plainValue(mixed $value, SpecificationOptions $options): string
    {
        return Link::fromData($value)?->plainText() ?? '';
    }

    public function render(mixed $value, SpecificationOptions $options): string
    {
        return Link::fromData($value)?->html() ?? '';
    }
}
