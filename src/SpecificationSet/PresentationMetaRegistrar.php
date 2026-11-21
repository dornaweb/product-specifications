<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

use Amiut\ProductSpecs\Capability\Capabilities;

final class PresentationMetaRegistrar
{
    public function __construct(
        private readonly PresentationMetaDefinition $definition,
        private readonly Capabilities $capabilities
    ) {
    }

    public function init(): void
    {
        add_action('init', [$this, 'registerMeta']);
    }

    public function registerMeta(): void
    {
        register_post_meta(
            SpecificationSetPostType::KEY,
            PresentationMetaDefinition::KEY,
            [
                'type' => 'object',
                'single' => true,
                'default' => PresentationMetaDefinition::default(),
                'show_in_rest' => [
                    'schema' => $this->definition->schema(),
                ],
                'sanitize_callback' => static fn (mixed $value): array => SpecificationSetPresentation::fromStored(
                    $value
                )->toArray(),
                'auth_callback' => fn (): bool => $this->capabilities->currentUserCanManage(),
            ]
        );
    }
}
