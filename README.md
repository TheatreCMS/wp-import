# WordPress Import plugin for TheatreCMS

Imports WordPress content into TheatreCMS.

## Installing

TheatreCMS plugins are folders in the core install's `plugins/` directory:

```sh
git clone git@github.com:TheatreCMS/wp-import.git plugins/wp-import
bin/composer-local            # inside DDEV: ddev exec bin/composer-local
```

See `documentation/plugins.md` in [TheatreCMS](https://github.com/TheatreCMS/theatrecms) for how plugins are discovered, installed and loaded.

## Developing

```sh
composer config repositories.theatrecms vcs https://github.com/TheatreCMS/theatrecms   # once; core isn't on Packagist yet
composer install       # installs TheatreCMS core from GitHub as a dependency
vendor/bin/phpunit
vendor/bin/phpstan analyse -c phpstan.neon.dist
vendor/bin/phpcs
```
