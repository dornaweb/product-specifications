<?php

declare(strict_types=1);

namespace Amiut\ProductSpecs\Tests\Unit\Template;

use Amiut\ProductSpecs\Template\Context;
use Amiut\ProductSpecs\Template\PhpTemplateRenderer;
use Amiut\ProductSpecs\Template\TemplateRenderer;
use PHPUnit\Framework\TestCase;

class TemplateRendererTest extends TestCase
{
    private const TEMPLATES_DIRECTORY = __DIR__ . DIRECTORY_SEPARATOR . '../../assets/template';
    private TemplateRenderer $renderer;

    protected function setUp(): void
    {
        $context = new Context([self::TEMPLATES_DIRECTORY]);
        $this->renderer = new PhpTemplateRenderer($context);
    }

    public function testRenderStatic(): void
    {
        $this->assertStringContainsString(
            'foobar',
            $this->renderer->render('static')
        );
    }

    public function testRenderWithData(): void
    {
        $output = $this->renderer->render(
            'dynamic',
            [
                'name' => 'foo',
                'age' => 20,
            ]
        );

        $this->assertStringContainsString('name: foo', $output);
        $this->assertStringContainsString('age: 20', $output);
    }
}
