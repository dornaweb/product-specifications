<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\ImportExport\Import;

use Amiut\ProductSpecs\Background\BackgroundWorker;

final class BackgroundImport
{
    public const HOOK = 'product_specifications_import_chunk';

    private readonly BackgroundWorker $worker;

    public function __construct(private readonly ImportRunner $runner)
    {
        $this->worker = new BackgroundWorker(
            self::HOOK,
            fn (): bool => $this->runner->isRunning(),
            function (): void {
                $this->runner->runChunk();
            }
        );
    }

    public function start(): ?ImportRun
    {
        $run = $this->runner->start();
        $this->worker->schedule();

        return $run;
    }

    public function process(): void
    {
        $this->worker->process();
    }

    public function nudge(): ?ImportRun
    {
        $this->worker->nudge();

        return $this->runner->run();
    }
}
