<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Specification\Type\Document;

use Amiut\ProductSpecs\Specification\Type\Document\Attachment;
use Amiut\ProductSpecs\Specification\Type\Document\Attachments;

final class InMemoryAttachments implements Attachments
{
    /**
     * @var array<int, Attachment>
     */
    private array $attachments = [];

    public function with(Attachment $attachment): self
    {
        $this->attachments[$attachment->id()] = $attachment;

        return $this;
    }

    public function find(int $id): ?Attachment
    {
        return $this->attachments[$id] ?? null;
    }

    public function idForFile(string $file): ?int
    {
        foreach ($this->attachments as $attachment) {
            if ($attachment->file() === $file) {
                return $attachment->id();
            }
        }

        return null;
    }
}
