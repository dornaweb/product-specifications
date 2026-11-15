<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ProductValues;

use Amiut\ProductSpecs\Migration\MigrationState;
use Amiut\ProductSpecs\Specification\SpecificationDefinitionRepository;
use RuntimeException;

final class SpecificationsUpdater
{
    public function __construct(
        private readonly ProductValueWriter $writer,
        private readonly ProductValueRepository $repository,
        private readonly SpecificationDefinitionRepository $definitions,
        private readonly MigrationState $migration
    ) {
    }

    /**
     * @return array<string, mixed>
     * @throws RejectedValues
     */
    public function save(SpecificationsUpdate $update): array
    {
        if ($this->migration->requiresMigration()) {
            throw new RuntimeException(
                __('Specification values can be saved once the migration is complete.', 'product-specifications')
            );
        }

        $productId = $update->productId();
        $setSlug = $update->resultingSet($this->repository->assignedSet($productId));

        if ($setSlug === null && $update->values() !== []) {
            throw new RuntimeException(
                __('Specification values require a specification set.', 'product-specifications')
            );
        }

        $unknown = $this->unknownKeys($update);

        if ($unknown !== []) {
            throw new RuntimeException(
                sprintf(
                    /* translators: %s: comma-separated specification keys. */
                    __('No specification has the key %s.', 'product-specifications'),
                    implode(', ', $unknown)
                )
            );
        }

        return $this->writer->replace($productId, $setSlug, $update->values());
    }

    /**
     * @return list<string>
     */
    private function unknownKeys(SpecificationsUpdate $update): array
    {
        $unknown = [];

        foreach (array_keys($update->values()) as $key) {
            if ($this->definitions->findByKey((string) $key) === null) {
                $unknown[] = (string) $key;
            }
        }

        return $unknown;
    }
}
