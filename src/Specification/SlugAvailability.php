<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification;

use Amiut\ProductSpecs\Specification\Deletion\PurgeQueue;
use WP_Term;

/**
 * Whether a key can be given to a new specification. A key whose values are still being
 * removed from products counts as taken, so no specification can adopt what is left.
 */
final class SlugAvailability
{
    public function __construct(private readonly PurgeQueue $queue)
    {
    }

    public function taken(string $slug): bool
    {
        return get_term_by('slug', $slug, SpecificationTaxonomy::KEY) instanceof WP_Term
            || $this->queue->holds($slug);
    }

    /**
     * The slug itself when it can be stored, otherwise its first free numeric form.
     */
    public function free(SpecificationSlug $slug): SpecificationSlug
    {
        if (!$slug->isCanonicalInteger() && !$this->taken($slug->value())) {
            return $slug;
        }

        return $this->nextFree($slug);
    }

    public function nextFree(SpecificationSlug $slug): SpecificationSlug
    {
        $number = 1;

        do {
            $candidate = $slug->withNumericSuffix(++$number);
        } while ($this->taken($candidate->value()));

        return $candidate;
    }
}
