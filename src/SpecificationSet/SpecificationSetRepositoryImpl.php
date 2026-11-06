<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\SpecificationSet;

use WP_Error;
use WP_Post;

final class SpecificationSetRepositoryImpl implements SpecificationSetRepository
{
    public function __construct(private readonly SpecificationSetMapper $mapper)
    {
    }

    public function byId(int $id): ?SpecificationSet
    {
        $post = get_post($id);

        if (!$post instanceof WP_Post || $post->post_type !== SpecificationSetPostType::KEY) {
            return null;
        }

        return $this->mapper->fromPost($post);
    }

    public function all(array $args = []): array
    {
        /** @var WP_Post[] $posts */
        $posts = get_posts($this->queryArguments($args));

        return array_map(
            fn (WP_Post $post) => $this->mapper->fromPost($post),
            $posts
        );
    }

    public function save(SpecificationSet $set): int
    {
        $result = is_int($set->id()) && $set->id() > 0
            ? wp_update_post($this->mapper->postArguments($set), true)
            : wp_insert_post($this->mapper->postArguments($set), true);

        if ($result instanceof WP_Error) {
            return 0;
        }

        $postId = (int) $result;

        update_post_meta(
            $postId,
            StructureMetaDefinition::KEY,
            $this->mapper->structureToArray($set->structure())
        );

        return $postId;
    }

    public function delete(int $id): bool
    {
        $result = wp_delete_post($id, true);

        return $result instanceof WP_Post;
    }

    /**
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     * @phpstan-return array{post_type: string, post_status: string, numberposts: int, s?: string, orderby?: string, order?: string, offset?: int}
     */
    private function queryArguments(array $args): array
    {
        $queryArgs = [
            'post_type' => SpecificationSetPostType::KEY,
            'post_status' => 'any',
            'numberposts' => -1,
        ];

        foreach (['s', 'orderby', 'order'] as $key) {
            if (isset($args[$key]) && is_string($args[$key])) {
                $queryArgs[$key] = $args[$key];
            }
        }

        if (isset($args['offset']) && is_int($args['offset'])) {
            $queryArgs['offset'] = $args['offset'];
        }

        return $queryArgs;
    }
}
