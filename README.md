# WordPress Import plugin for TheatreCMS

Imports WordPress content into TheatreCMS.

## Installing

TheatreCMS plugins are folders in the core install's `plugins/` directory:

```sh
git clone git@github.com:TheatreCMS/wp-import.git plugins/wp-import
bin/composer-local            # inside DDEV: ddev exec bin/composer-local
```

See `documentation/plugins.md` in [TheatreCMS](https://github.com/TheatreCMS/theatrecms) for how plugins are discovered, installed and loaded.

## The WordPress source database

The importer reads a copy of the WordPress site's database, loaded next to TheatreCMS's own.
With DDEV, load a dump (`.sql`, `.sql.gz`, `.sql.bz2` or `.sql.xz`) into a `wp_source` database;
this creates it, or drops and recreates it if it exists:

```sh
ddev import-db --database=wp_source --file=/path/to/wordpress-dump.sql
ddev exec bin/theatrecms wp-import:status    # post counts by post type and status
```

**Dumps contain secrets** (password hashes, API keys in `wp_options`, personal data). Keep them
outside every repository; never commit one.

### Settings

By default the plugin connects with TheatreCMS's own database settings, using the database
`wp_source` and the table prefix `wp_`. Override any of them in TheatreCMS's `app/config.yaml`:

```yaml
plugins:
    theatrecms/wp-import:
        source:
            dbname: wp_source       # any Doctrine DBAL connection parameter: host, user, password, ...
            table_prefix: wp_       # the WordPress $table_prefix
```

## Commands

| Command | What it does |
|---|---|
| `bin/theatrecms wp-import:status` | Count the source's posts by post type and status, with a total |

## Developing

```sh
composer config repositories.theatrecms vcs https://github.com/TheatreCMS/theatrecms   # once; core isn't on Packagist yet
composer install       # installs TheatreCMS core from GitHub as a dependency
vendor/bin/phpunit
vendor/bin/phpstan analyse -c phpstan.neon.dist
vendor/bin/phpcs
```
