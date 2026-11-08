<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration\Step;

use Amiut\ProductSpecs\Specification\SpecificationDefinition;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\SpecificationSlug;
use Amiut\ProductSpecs\Specification\SpecificationTaxonomy;
use Amiut\ProductSpecs\Specification\Type\TextType;

final class WordPressSpecificationLookup implements SpecificationLookup
{
    public const RECREATED_FROM_META = '_product_specifications_recreated_from';

    public function __construct(private readonly SpecificationDefinitionRepository $definitions)
    {
    }

    public function find(int $id): ?SpecificationDefinition
    {
        return $id > 0 ? $this->definitions->find($id) : null;
    }

    public function recreate(int $formerId, string $name, string $slug, string $description): ?SpecificationDefinition
    {
        if ($formerId < 1 || trim($name) === '') {
            return null;
        }

        $recreated = $this->recreatedFor($formerId);

        if ($recreated !== null) {
            return $recreated;
        }

        $termId = $this->definitions->save(
            SpecificationDefinition::create(
                $name,
                $this->freeSlug($slug, $name),
                $description,
                TextType::ID,
                SpecificationOptions::fromMap([]),
                null
            )
        );

        if ($termId < 1) {
            return null;
        }

        add_term_meta($termId, self::RECREATED_FROM_META, $formerId, true);

        return $this->definitions->find($termId);
    }

    private function recreatedFor(int $formerId): ?SpecificationDefinition
    {
        $ids = get_terms([
            'taxonomy' => SpecificationTaxonomy::KEY,
            'hide_empty' => false,
            'fields' => 'ids',
            'number' => 1,
            'meta_key' => self::RECREATED_FROM_META,
            'meta_value' => (string) $formerId,
        ]);

        $id = is_array($ids) && isset($ids[0]) && is_numeric($ids[0]) ? (int) $ids[0] : 0;

        return $id > 0 ? $this->definitions->find($id) : null;
    }

    private function freeSlug(string $slug, string $name): string
    {
        $preserved = $slug === '' ? '' : SpecificationSlug::preserved($slug)->value();

        if ($preserved !== '' && $this->definitions->findByKey($preserved) === null) {
            return $preserved;
        }

        return SpecificationSlug::forName($name)->value();
    }
}
