<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Deletion;

use Amiut\ProductSpecs\ProductValues\ProductValueRepository;

/**
 * Removes one specification's key from the value maps that hold it. A storage operation,
 * not a save: no other specification's values change, so no save action fires.
 */
final class ValuePurge
{
    public function __construct(private readonly ProductValueRepository $values)
    {
    }

    /**
     * Removes the key from up to $limit products after the given id. A write that fails
     * stops the run where it is, so the next one continues from the last product done.
     */
    public function purge(string $key, int $afterPostId, int $limit): PurgeProgress
    {
        $limit = max(1, $limit);
        $postIds = $this->values->postIdsMentioning($key, $afterPostId, $limit + 1);
        $cursor = $afterPostId;

        foreach (array_slice($postIds, 0, $limit) as $postId) {
            $document = $this->values->read($postId);

            if ($document->has($key) && !$this->values->replace($postId, $document->without($key))) {
                return PurgeProgress::failed(
                    $cursor,
                    sprintf(
                        /* translators: 1: a specification key, 2: a product id. */
                        __('The value of "%1$s" could not be removed from product %2$d.', 'product-specifications'),
                        $key,
                        $postId
                    )
                );
            }

            $cursor = $postId;
        }

        return count($postIds) > $limit ? PurgeProgress::continuing($cursor) : PurgeProgress::finished();
    }
}
