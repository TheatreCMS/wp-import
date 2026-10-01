<?php

namespace TheatreCMS\WpImport\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TheatreCMS\WpImport\Plugin;
use TheatreCMS\Plugin\PluginInterface;

class PluginTest extends TestCase
{
    public function testComposerManifestNamesThePluginClass(): void
    {
        $manifest = json_decode((string) file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true);

        $this->assertSame('theatrecms-plugin', $manifest['type']);
        $this->assertSame(Plugin::class, $manifest['extra']['theatrecms']['plugin']);
    }

    public function testPluginImplementsThePluginInterface(): void
    {
        $this->assertInstanceOf(PluginInterface::class, new Plugin());
    }
}
