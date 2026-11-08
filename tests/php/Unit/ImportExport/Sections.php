<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\ImportExport;

use Amiut\ProductSpecs\ImportExport\Section\SetsSection;
use Amiut\ProductSpecs\ImportExport\Section\SpecificationsSection;
use Amiut\ProductSpecs\ImportExport\SectionRegistry;
use Amiut\ProductSpecs\ProductValues\WordPressProductValueRepository;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionMapper;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use Amiut\ProductSpecs\Specification\Type\OptionsChecker;
use Amiut\ProductSpecs\Specification\Type\SelectType;
use Amiut\ProductSpecs\Specification\Type\SpecificationTypeRegistry;
use Amiut\ProductSpecs\Specification\Type\TextType;
use Amiut\ProductSpecs\Specification\Type\TrueFalseType;
use WP_Term;

final class Sections
{
    public static function specifications(): SpecificationsSection
    {
        $types = new SpecificationTypeRegistry(new TextType());
        $types->register(new SelectType());
        $types->register(new TrueFalseType());

        return new SpecificationsSection(
            self::definitions(),
            $types,
            new OptionsChecker($types),
            new WordPressProductValueRepository()
        );
    }

    public static function registry(InMemorySetRepository $sets): SectionRegistry
    {
        $registry = new SectionRegistry();
        $registry->register(new SetsSection($sets, self::definitions()));
        $registry->register(self::specifications());

        return $registry;
    }

    /**
     * @param list<string> $choices
     */
    public static function storedSpecification(
        int $id,
        string $slug,
        string $name,
        string $type,
        array $choices = []
    ): void {
        $term = new WP_Term();
        $term->term_id = $id;
        $term->slug = $slug;
        $term->name = $name;
        $GLOBALS['wp_test_terms'][$id] = $term;
        $GLOBALS['wp_test_term_meta'][$id] = [
            'attr_type' => $type,
            'attr_values' => $choices === [] ? [] : ['values' => $choices],
            'attr_default' => '',
        ];
    }

    private static function definitions(): SpecificationDefinitionRepository
    {
        return new SpecificationDefinitionRepository(new SpecificationDefinitionMapper());
    }
}
