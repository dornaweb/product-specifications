<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Attribute;

final class AttributeFieldTextArea extends AttributeFieldText
{
    public function templatePath(): string
    {
        return 'attribute-field-controls/textarea';
    }
}
