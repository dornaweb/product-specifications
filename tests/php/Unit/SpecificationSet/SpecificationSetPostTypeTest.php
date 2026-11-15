<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\SpecificationSet;

use Amiut\ProductSpecs\Capability\Capabilities;
use Amiut\ProductSpecs\SpecificationSet\SpecificationSetPostType;
use PHPUnit\Framework\TestCase;

final class SpecificationSetPostTypeTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function requiredSupportProvider(): array
    {
        return [
            'meta reaches REST only with custom-fields' => ['custom-fields'],
            'the editor sends the description as content' => ['editor'],
            'the editor sends the name as title' => ['title'],
        ];
    }

    /**
     * Declaring `supports` at all replaces the ['title', 'editor'] that
     * register_post_type() defaults to, so a missing entry silently drops a
     * field the editor is already sending.
     *
     * @dataProvider requiredSupportProvider
     */
    public function testPostTypeSupports(string $support): void
    {
        static::assertContains($support, $this->args()['supports']);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function gatedCapabilityProvider(): array
    {
        return [
            'edit_post' => ['edit_post'],
            'read_post' => ['read_post'],
            'delete_post' => ['delete_post'],
            'edit_posts' => ['edit_posts'],
            'edit_others_posts' => ['edit_others_posts'],
            'delete_posts' => ['delete_posts'],
            'publish_posts' => ['publish_posts'],
            'read_private_posts' => ['read_private_posts'],
            'create_posts' => ['create_posts'],
        ];
    }

    /**
     * A key left out of the map falls back to the stock post capability, which
     * is what let anyone holding edit_posts reach these posts.
     *
     * @dataProvider gatedCapabilityProvider
     */
    public function testEveryCapabilityIsGatedByThePluginCapability(string $key): void
    {
        static::assertSame(
            Capabilities::DEFAULT_CAPABILITY,
            $this->args()['capabilities'][$key] ?? null
        );
    }

    public function testCapabilityMapFollowsTheFilteredCapability(): void
    {
        $postType = new SpecificationSetPostType(
            Capabilities::fromFilterValue('manage_options')
        );

        static::assertSame(
            ['manage_options'],
            array_values(array_unique($postType->args()['capabilities']))
        );
    }

    /**
     * @return array{supports: list<string>, capabilities: array<string, string>}
     */
    private function args(): array
    {
        $postType = new SpecificationSetPostType(
            Capabilities::fromFilterValue(Capabilities::DEFAULT_CAPABILITY)
        );

        return $postType->args();
    }
}
