<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Specification\Type\Document;

use Amiut\ProductSpecs\Specification\SpecificationOptions;
use Amiut\ProductSpecs\Specification\Type\OptionsSchema;
use Amiut\ProductSpecs\Specification\Type\PortableValue;
use Amiut\ProductSpecs\Specification\Type\SpecificationType;
use WC_Product;

/**
 * Stored as `[{"id": 482, "label": "Datasheet"}, {"id": 517}]`, and read as the files the
 * media library still holds.
 *
 * @phpstan-type StoredFile array{id: int, label?: string}
 * @phpstan-type ShownFile array{id: int, label: string, url: string, type: string, size: ?int}
 */
final class DocumentType implements SpecificationType, PortableValue
{
    public const ID = 'document';

    private const LINE = "\n";

    public function __construct(private readonly Attachments $attachments)
    {
    }

    public function id(): string
    {
        return self::ID;
    }

    public function label(): string
    {
        return __('Document', 'product-specifications');
    }

    /**
     * @return array<string, mixed>
     */
    public function optionsSchema(): array
    {
        return OptionsSchema::none();
    }

    /**
     * @return list<StoredFile>|null
     */
    public function sanitize(mixed $input, SpecificationOptions $options): ?array
    {
        if (!is_array($input) || !array_is_list($input)) {
            return null;
        }

        $files = [];

        foreach ($input as $item) {
            $id = is_array($item) && is_numeric($item['id'] ?? null) ? (int) $item['id'] : 0;

            if ($id < 1 || array_key_exists($id, $files)) {
                continue;
            }

            $label = is_array($item) && is_scalar($item['label'] ?? null)
                ? sanitize_text_field((string) $item['label'])
                : '';
            $files[$id] = $label === '' ? ['id' => $id] : ['id' => $id, 'label' => $label];
        }

        return $files === [] ? null : array_values($files);
    }

    public function validate(mixed $input, SpecificationOptions $options): ?string
    {
        return null;
    }

    /**
     * @return list<ShownFile>|null
     */
    public function value(WC_Product $product, mixed $stored, SpecificationOptions $options): ?array
    {
        $shown = [];

        foreach ($this->sanitize($stored, $options) ?? [] as $file) {
            $attachment = $this->attachments->find($file['id']);

            if ($attachment === null) {
                continue;
            }

            $shown[] = [
                'id' => $attachment->id(),
                'label' => $file['label'] ?? $attachment->title(),
                'url' => $attachment->url(),
                'type' => $attachment->extension(),
                'size' => $attachment->size(),
            ];
        }

        return $shown === [] ? null : $shown;
    }

    public function plainValue(mixed $value, SpecificationOptions $options): string
    {
        return implode(
            self::LINE,
            array_map(
                static fn (array $file): string => "{$file['label']}: {$file['url']}",
                self::shownFiles($value)
            )
        );
    }

    public function render(mixed $value, SpecificationOptions $options): string
    {
        return implode(
            '<br>',
            array_map(
                static function (array $file): string {
                    $details = array_filter([
                        $file['type'],
                        $file['size'] === null ? '' : size_format($file['size'], 1),
                    ]);

                    return sprintf(
                        '<a href="%s">%s</a>%s',
                        esc_url($file['url']),
                        esc_html($file['label']),
                        $details === [] ? '' : esc_html(' (' . implode(', ', $details) . ')')
                    );
                },
                self::shownFiles($value)
            )
        );
    }

    /**
     * @return list<array{file: string, label?: string}>|null
     */
    public function exportValue(mixed $stored, SpecificationOptions $options): ?array
    {
        $exported = [];

        foreach ($this->sanitize($stored, $options) ?? [] as $file) {
            $path = $this->attachments->find($file['id'])?->file() ?? '';

            if ($path !== '') {
                $exported[] = array_key_exists('label', $file)
                    ? ['file' => $path, 'label' => $file['label']]
                    : ['file' => $path];
            }
        }

        return $exported === [] ? null : $exported;
    }

    /**
     * @return list<StoredFile>|null
     */
    public function importValue(mixed $exported, SpecificationOptions $options): ?array
    {
        if (!is_array($exported) || !array_is_list($exported)) {
            return null;
        }

        $files = [];

        foreach ($exported as $item) {
            $path = is_array($item) && is_string($item['file'] ?? null) ? $item['file'] : '';
            $id = $path === '' ? null : $this->attachments->idForFile($path);

            if ($id !== null) {
                $files[] = ['id' => $id, 'label' => is_array($item) ? ($item['label'] ?? '') : ''];
            }
        }

        return $this->sanitize($files, $options);
    }

    /**
     * @return list<ShownFile>
     */
    private static function shownFiles(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $files = [];

        foreach ($value as $file) {
            if (
                is_array($file)
                && is_int($file['id'] ?? null)
                && is_string($file['label'] ?? null)
                && is_string($file['url'] ?? null)
                && is_string($file['type'] ?? null)
                && (is_int($file['size'] ?? null) || ($file['size'] ?? null) === null)
            ) {
                $files[] = [
                    'id' => $file['id'],
                    'label' => $file['label'],
                    'url' => $file['url'],
                    'type' => $file['type'],
                    'size' => $file['size'] ?? null,
                ];
            }
        }

        return $files;
    }
}
