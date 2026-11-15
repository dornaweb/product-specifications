<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

use Amiut\ProductSpecs\Capability\Capabilities;

final class StructureMetaRegistrar
{
    public function __construct(
        private readonly StructureMetaDefinition $definition,
        private readonly StructureSanitizer $sanitizer,
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
            StructureMetaDefinition::KEY,
            [
                'type' => 'object',
                'single' => true,
                'default' => StructureMetaDefinition::default(),
                'show_in_rest' => [
                    'schema' => $this->definition->schema(),
                ],
                'sanitize_callback' => [$this->sanitizer, 'sanitize'],
                'auth_callback' => fn (): bool => $this->capabilities->currentUserCanManage(),
            ]
        );
    }
}
