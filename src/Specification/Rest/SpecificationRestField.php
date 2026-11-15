<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Rest;

use Amiut\ProductSpecs\Specification\SpecificationDefinition;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\SpecificationTaxonomy;
use Amiut\ProductSpecs\Specification\Type\OptionsChecker;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use WP_Error;
use WP_REST_Request;
use WP_Term;

/**
 * The public shape of a specification. Term meta stays internal: `attr_type`,
 * `attr_values` and `attr_default` are storage names, and registering them
 * would make them a permanent public contract.
 */
final class SpecificationRestField
{
    public const FIELD = 'specification';

    public function __construct(
        private readonly SpecificationDefinitionRepository $repository,
        private readonly SpecificationTypeRegistry $types,
        private readonly SetUsageCounter $usage,
        private readonly OptionsChecker $options
    ) {
    }

    public function register(): void
    {
        register_rest_field(
            SpecificationTaxonomy::KEY,
            self::FIELD,
            [
                'get_callback' => [$this, 'read'],
                'update_callback' => [$this, 'write'],
                'schema' => $this->schema(),
            ]
        );
    }

    /**
     * @param array<string, mixed> $term
     * @return array<string, mixed>
     */
    public function read(array $term, string $field, WP_REST_Request $request): array
    {
        $termId = is_numeric($term['id'] ?? null) ? (int) $term['id'] : 0;
        $specification = $this->repository->find($termId);

        if (!$specification instanceof SpecificationDefinition) {
            return [];
        }

        return [
            'type' => $specification->typeId(),
            'type_label' => $this->types->get($specification->typeId())->label(),
            'options' => (object) $specification->options()->toArray(),
            'default' => $this->types->get($specification->typeId())->sanitize(
                $specification->defaultValue(),
                $specification->options()
            ),
            'usage_count' => $this->usageCount($specification, $request),
        ];
    }

    /**
     * @param mixed $value
     */
    public function write($value, WP_Term $term): ?WP_Error
    {
        if (!is_array($value)) {
            return null;
        }

        $specification = $this->repository->find($term->term_id);

        if (!$specification instanceof SpecificationDefinition) {
            return new WP_Error(
                'product_specifications_not_found',
                __('Specification not found.', 'product-specifications'),
                ['status' => 404]
            );
        }

        $typeId = array_key_exists('type', $value) && is_string($value['type'])
            ? $value['type']
            : $specification->typeId();
        $options = $specification->options()->toArray();

        if (array_key_exists('options', $value)) {
            $checked = $this->checkOptions($typeId, $value['options']);

            if ($checked instanceof WP_Error) {
                return $checked;
            }

            $options = $checked;
        }

        $type = $this->types->get($typeId);
        $default = $specification->defaultValue();

        if (array_key_exists('default', $value)) {
            $rejection = $type->validate($value['default'], SpecificationOptions::fromMap($options));

            if ($rejection !== null) {
                return new WP_Error('product_specifications_invalid_default', $rejection, ['status' => 400]);
            }

            $default = $type->sanitize($value['default'], SpecificationOptions::fromMap($options));
        }

        $this->repository->save($specification->withDefinition($typeId, $options, $default));

        return null;
    }

    /**
     * @return array<string, mixed>|WP_Error
     */
    private function checkOptions(string $typeId, mixed $options): array|WP_Error
    {
        $checked = $this->options->check($typeId, $options, self::FIELD . '[options]');

        if ($checked instanceof WP_Error) {
            return new WP_Error($checked->get_error_code(), $checked->get_error_message(), ['status' => 400]);
        }

        return $checked;
    }

    /**
     * `_fields` trims the response but does not stop this callback running, so
     * the expensive member guards itself.
     */
    private function usageCount(
        SpecificationDefinition $specification,
        WP_REST_Request $request
    ): ?int {
        $fields = $request->get_param('_fields');

        if (is_string($fields) && $fields !== '' && !str_contains($fields, 'usage_count')) {
            return null;
        }

        return $this->usage->forSlug($specification->key());
    }

    /**
     * @return array<string, mixed>
     */
    private function schema(): array
    {
        return [
            'description' => __('The specification definition.', 'product-specifications'),
            'type' => 'object',
            'context' => ['view', 'edit'],
            'properties' => [
                'type' => ['type' => 'string'],
                'type_label' => ['type' => 'string', 'readonly' => true],
                'options' => ['type' => 'object', 'additionalProperties' => true],
                'default' => [
                    'type' => ['string', 'number', 'boolean', 'null', 'array'],
                    'items' => ['type' => 'string'],
                ],
                'usage_count' => ['type' => ['integer', 'null'], 'readonly' => true],
            ],
        ];
    }
}
