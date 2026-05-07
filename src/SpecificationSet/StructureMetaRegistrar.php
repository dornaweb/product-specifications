<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

final class StructureMetaRegistrar
{
    private StructureMetaDefinition $definition;
    private StructureSanitizer $sanitizer;

    public function __construct(
        StructureMetaDefinition $definition,
        StructureSanitizer $sanitizer
    ) {
        $this->definition = $definition;
        $this->sanitizer = $sanitizer;
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
                'default' => $this->definition->default(),
                'show_in_rest' => [
                    'schema' => $this->definition->schema(),
                ],
                'sanitize_callback' => [$this->sanitizer, 'sanitize'],
                'auth_callback' => static function () {
                    return current_user_can('shop_manager');
                },
            ]
        );
    }
}
