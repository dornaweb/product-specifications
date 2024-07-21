<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Attribute;

use WP_Term;

final class AttributeFieldFactory
{
    public function createFromWpTerm(WP_Term $term, ?int $contextPostId): ?AttributeField
    {
        $type = (string) get_term_meta($term->term_id, 'attr_type', true);
        $default = get_term_meta($term->term_id, 'attr_default', true);
        $value = !is_null($contextPostId)
            ? ((array) dwspecs_attr_value_by($contextPostId, 'id', $term->term_id))['value'] ?? null
            : null;

        switch ($type) {
            case 'text':
                return new AttributeFieldText(
                    $term->name,
                    $term->slug,
                    $term->term_id,
                    (string) $default,
                    $term->description,
                    $value
                );
            case 'textarea':
                return new AttributeFieldTextArea(
                    $term->name,
                    $term->slug,
                    $term->term_id,
                    (string) $default,
                    $term->description,
                    $value
                );
            case 'select':
            case 'radio':
                /** @var array<string> $options */
                $options = array_filter((array) get_term_meta($term->term_id, 'attr_values', true));

                return new AttributeFieldSelect(
                    $term->name,
                    $term->slug,
                    $term->term_id,
                    $options,
                    (string) $default,
                    $term->description,
                    $value
                );
            case 'true_false':
                return new AttributeFieldTrueFalse(
                    $term->name,
                    $term->slug,
                    $term->term_id,
                    (bool) $default,
                    $term->description,
                    $value === AttributeFieldTrueFalse::OPTION_TRUE
                );
            default:
                return null;
        }
    }
}
