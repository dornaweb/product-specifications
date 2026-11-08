<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification;

use Amiut\ProductSpecs\Specification\SpecificationDefinition;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionMapper;
use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\TextType;
use PHPUnit\Framework\TestCase;
use WP_Term;

/**
 * `attr_type` is the only record of which type owns a specification. Rewriting
 * an unrecognized id would make deactivating the plugin that registered it a
 * permanent loss, silently, on the next save.
 */
final class SpecificationDefinitionMapperTest extends TestCase
{
    private SpecificationDefinitionMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new SpecificationDefinitionMapper();
        $GLOBALS['wp_test_term_meta'] = [];
    }

    public function testAnUnregisteredTypeIdSurvivesARoundTrip(): void
    {
        $GLOBALS['wp_test_term_meta'][7] = [
            'attr_type' => 'rating',
            'attr_values' => ['max' => 5],
            'attr_default' => '',
        ];

        $specification = $this->mapper->fromTerm($this->term(7, 'Build quality', 'build-quality-9f3a2b71'));

        static::assertSame('rating', $specification->typeId());
        static::assertSame('rating', $this->mapper->termMeta($specification)['attr_type']);
        static::assertSame(['max' => 5], $this->mapper->termMeta($specification)['attr_values']);
    }

    public function testAMissingTypeIdReadsAsText(): void
    {
        $specification = $this->mapper->fromTerm($this->term(8, 'Weight', 'weight-1a2b3c4d'));

        static::assertSame(TextType::ID, $specification->typeId());
    }

    public function testAStoredChoiceListIsWrittenBackAsTheMap(): void
    {
        $GLOBALS['wp_test_term_meta'][9] = [
            'attr_type' => 'select',
            'attr_values' => ['5.5"', '6.1"'],
        ];

        $specification = $this->mapper->fromTerm($this->term(9, 'Screen size', 'screen-size-9f3a2b71'));

        static::assertSame(
            ['values' => ['5.5"', '6.1"']],
            $this->mapper->termMeta($specification)['attr_values']
        );
    }

    public function testInsertCarriesTheSlugAndUpdateNeverDoes(): void
    {
        $specification = $this->mapper->fromTerm($this->term(10, 'Screen size', 'screen-size-9f3a2b71'));

        static::assertSame(
            ['name' => 'Screen size', 'slug' => 'screen-size-9f3a2b71', 'description' => ''],
            $this->mapper->insertArguments($specification)
        );
        static::assertSame(
            ['name' => 'Screen size', 'description' => ''],
            $this->mapper->updateArguments($specification)
        );
    }

    private function term(int $id, string $name, string $slug): WP_Term
    {
        $term = new WP_Term();
        $term->term_id = $id;
        $term->name = $name;
        $term->slug = $slug;
        $term->description = '';

        return $term;
    }

    public function testABooleanDefaultIsStoredSoThatNoSurvives(): void
    {
        $options = SpecificationOptions::fromMap([]);
        $no = SpecificationDefinition::create('Wi-Fi', 'wifi', '', 'true_false', $options, false);
        $yes = SpecificationDefinition::create('Wi-Fi', 'wifi', '', 'true_false', $options, true);

        static::assertSame('0', $this->mapper->termMeta($no)['attr_default']);
        static::assertSame('1', $this->mapper->termMeta($yes)['attr_default']);
    }
}
