<?php

namespace TheatreCMS\WpImport;

use DI\Container;
use TheatreCMS\Plugin\AbstractPlugin;
use TheatreCMS\WpImport\Console\StatusCommand;

/**
 * WordPress Import plugin for TheatreCMS. See documentation/plugins.md in TheatreCMS core for the lifecycle
 * and extension points.
 */
class Plugin extends AbstractPlugin
{
    public const PACKAGE = 'theatrecms/wp-import';

    public function register(Container $container): void
    {
        $container->set(WpSource::class, static function (Container $c): WpSource {
            $settings = $c->get('settings');

            return WpSource::fromSettings(
                $settings['doctrine']['connection'] ?? [],
                $settings['plugins']['config'][self::PACKAGE]['source'] ?? [],
            );
        });
    }

    public function commands(): array
    {
        return [StatusCommand::class];
    }
}
