<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Migration;

/**
 * @phpstan-type SkippedEntry array{step: string, item: int}
 * @phpstan-type ErrorEntry array{step: string, item: int, message: string}
 */
final class MigrationRun
{
    public const IDLE = 'idle';
    public const RUNNING = 'running';
    public const FAILED = 'failed';
    public const COMPLETE = 'complete';

    private const STATUSES = [self::IDLE, self::RUNNING, self::FAILED, self::COMPLETE];

    /**
     * @param list<SkippedEntry> $skipped
     * @param list<ErrorEntry> $errors
     */
    private function __construct(
        private readonly string $status,
        private readonly string $step,
        private readonly int $cursor,
        private readonly int $processed,
        private readonly int $total,
        private readonly ?int $startedAt,
        private readonly array $skipped,
        private readonly array $errors,
        private readonly ?string $failure
    ) {
    }

    public static function idle(): self
    {
        return new self(self::IDLE, '', 0, 0, 0, null, [], [], null);
    }

    public static function started(string $firstStep, int $total, int $startedAt): self
    {
        return new self(self::RUNNING, $firstStep, 0, 0, $total, $startedAt, [], [], null);
    }

    public static function fromStorage(mixed $stored): self
    {
        if (!is_array($stored)) {
            return self::idle();
        }

        $status = self::stringAt($stored, 'status');

        if (!in_array($status, self::STATUSES, true)) {
            return self::idle();
        }

        $startedAt = $stored['started_at'] ?? null;
        $failure = $stored['failure'] ?? null;

        return new self(
            $status,
            self::stringAt($stored, 'step'),
            self::intAt($stored, 'cursor'),
            self::intAt($stored, 'processed'),
            self::intAt($stored, 'total'),
            is_int($startedAt) ? $startedAt : null,
            self::skippedFrom($stored['skipped'] ?? null),
            self::errorsFrom($stored['errors'] ?? null),
            is_string($failure) ? $failure : null
        );
    }

    /**
     * @return array{
     *     status: string,
     *     step: string,
     *     cursor: int,
     *     processed: int,
     *     total: int,
     *     started_at: int|null,
     *     skipped: list<SkippedEntry>,
     *     errors: list<ErrorEntry>,
     *     failure: string|null
     * }
     */
    public function toStorage(): array
    {
        return [
            'status' => $this->status,
            'step' => $this->step,
            'cursor' => $this->cursor,
            'processed' => $this->processed,
            'total' => $this->total,
            'started_at' => $this->startedAt,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
            'failure' => $this->failure,
        ];
    }

    public function status(): string
    {
        return $this->status;
    }

    public function isRunning(): bool
    {
        return $this->status === self::RUNNING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::FAILED;
    }

    public function isComplete(): bool
    {
        return $this->status === self::COMPLETE;
    }

    public function step(): string
    {
        return $this->step;
    }

    public function cursor(): int
    {
        return $this->cursor;
    }

    public function processed(): int
    {
        return $this->processed;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function startedAt(): ?int
    {
        return $this->startedAt;
    }

    /**
     * @return list<SkippedEntry>
     */
    public function skipped(): array
    {
        return $this->skipped;
    }

    /**
     * @return list<ErrorEntry>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function failure(): ?string
    {
        return $this->failure;
    }

    public function withChunk(ChunkResult $result): self
    {
        $skipped = $this->skipped;
        foreach ($result->skipped() as $item) {
            $skipped[] = ['step' => $this->step, 'item' => $item];
        }

        $errors = $this->errors;
        foreach ($result->errors() as $item => $message) {
            $errors[] = ['step' => $this->step, 'item' => $item, 'message' => $message];
        }

        return new self(
            $this->status,
            $this->step,
            $result->nextCursor() ?? $this->cursor,
            $this->processed + $result->processed(),
            $this->total,
            $this->startedAt,
            $skipped,
            $errors,
            $this->failure
        );
    }

    public function atStep(string $step): self
    {
        return new self(
            $this->status,
            $step,
            0,
            $this->processed,
            $this->total,
            $this->startedAt,
            $this->skipped,
            $this->errors,
            $this->failure
        );
    }

    public function completed(): self
    {
        return $this->withStatus(self::COMPLETE, null);
    }

    public function failed(string $message): self
    {
        return $this->withStatus(self::FAILED, $message);
    }

    public function resumed(): self
    {
        return $this->withStatus(self::RUNNING, null);
    }

    private function withStatus(string $status, ?string $failure): self
    {
        return new self(
            $status,
            $this->step,
            $this->cursor,
            $this->processed,
            $this->total,
            $this->startedAt,
            $this->skipped,
            $this->errors,
            $failure
        );
    }

    /**
     * @param array<mixed> $stored
     */
    private static function stringAt(array $stored, string $key): string
    {
        $value = $stored[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    /**
     * @param array<mixed> $stored
     */
    private static function intAt(array $stored, string $key): int
    {
        $value = $stored[$key] ?? 0;

        return is_int($value) ? $value : 0;
    }

    /**
     * @return list<SkippedEntry>
     */
    private static function skippedFrom(mixed $entries): array
    {
        $skipped = [];

        foreach (is_array($entries) ? $entries : [] as $entry) {
            if (
                is_array($entry)
                && isset($entry['step'], $entry['item'])
                && is_string($entry['step'])
                && is_int($entry['item'])
            ) {
                $skipped[] = ['step' => $entry['step'], 'item' => $entry['item']];
            }
        }

        return $skipped;
    }

    /**
     * @return list<ErrorEntry>
     */
    private static function errorsFrom(mixed $entries): array
    {
        $errors = [];

        foreach (is_array($entries) ? $entries : [] as $entry) {
            if (
                is_array($entry)
                && isset($entry['step'], $entry['item'], $entry['message'])
                && is_string($entry['step'])
                && is_int($entry['item'])
                && is_string($entry['message'])
            ) {
                $errors[] = ['step' => $entry['step'], 'item' => $entry['item'], 'message' => $entry['message']];
            }
        }

        return $errors;
    }
}
