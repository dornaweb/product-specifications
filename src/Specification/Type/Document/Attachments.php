<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Document;

interface Attachments
{
    public function find(int $id): ?Attachment;

    public function idForFile(string $file): ?int;
}
