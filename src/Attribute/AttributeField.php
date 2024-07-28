<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Attribute;

interface AttributeField
{
    public function name(): string;

    public function description(): string;

    public function id(): int;

    public function slug(): string;

    /**
     * @return mixed
     */
    public function default();

    /**
     * @return mixed
     */
    public function value();

    public function templatePath(): string;
}
