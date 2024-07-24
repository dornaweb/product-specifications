<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\EntityUpdater;

class InvalidEntityPropertyException extends \Exception
{
    private string $where;

    public function __construct($message = "", string $where = '')
    {
        $this->where = $where;
        parent::__construct($message);
    }

    public function where(): string
    {
        return $this->where;
    }
}
