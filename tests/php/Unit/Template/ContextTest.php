<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit;

use Amiut\ProductSpecs\Template\Context;
use Amiut\ProductSpecs\Template\TemplateNotfoundException;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    private const TEMPLATES_DIRECTORY = __DIR__ . DIRECTORY_SEPARATOR . '../../assets/template';

    public function testNonExistingDirectory(): void
    {
        $this->expectExceptionMessage('Directory "non-existing-path" does not exist');
        new Context(['non-existing-path']);
    }

    public function testResolveTemplatePath(): void
    {
        $context = new Context([self::TEMPLATES_DIRECTORY]);
        $this->assertSame(
            self::TEMPLATES_DIRECTORY . DIRECTORY_SEPARATOR . 'static.php',
            $context->resolveTemplatePath('static')
        );
        $this->assertSame(
            self::TEMPLATES_DIRECTORY . DIRECTORY_SEPARATOR . 'static.php',
            $context->resolveTemplatePath('static.php')
        );
    }

    public function testNonExistingTemplate(): void
    {
        $this->expectException(TemplateNotfoundException::class);
        (new Context([self::TEMPLATES_DIRECTORY]))
            ->resolveTemplatePath('notFound.php');
    }
}
