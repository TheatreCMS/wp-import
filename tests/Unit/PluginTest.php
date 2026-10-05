<?php

namespace TheatreCMS\WpImport\Tests\Unit;

use DI\Container;
use PHPUnit\Framework\TestCase;
use TheatreCMS\Plugin\PluginInterface;
use TheatreCMS\WpImport\Console\StatusCommand;
use TheatreCMS\WpImport\Plugin;
use TheatreCMS\WpImport\WpSource;

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

    public function testDeclaresTheStatusCommand(): void
    {
        $this->assertSame([StatusCommand::class], (new Plugin())->commands());
    }

    public function testRegistersTheSourceFromItsSettings(): void
    {
        $container = new Container();
        $container->set('settings', [
            'doctrine' => ['connection' => ['driver' => 'pdo_sqlite', 'memory' => true]],
            'plugins' => ['config' => [Plugin::PACKAGE => ['source' => ['table_prefix' => 'dso_']]]],
        ]);

        (new Plugin())->register($container);

        $source = $container->get(WpSource::class);
        $this->assertInstanceOf(WpSource::class, $source);
        $this->assertSame('dso_', $source->tablePrefix());
    }
}
