<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Document;

final class Attachment
{
    /**
     * @param string $file The path inside the uploads folder, the same on every store that
     *                     holds the file.
     */
    public function __construct(
        private readonly int $id,
        private readonly string $title,
        private readonly string $url,
        private readonly string $file,
        private readonly ?int $size
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function file(): string
    {
        return $this->file;
    }

    public function extension(): string
    {
        return strtoupper(pathinfo($this->file !== '' ? $this->file : $this->url, PATHINFO_EXTENSION));
    }

    public function size(): ?int
    {
        return $this->size;
    }
}
