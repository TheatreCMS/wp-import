<?php

namespace TheatreCMS\WpImport;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use InvalidArgumentException;

/**
 * Read access to the WordPress database being imported (by default `wp_source` on the same server
 * as TheatreCMS, with the `wp_` table prefix). Load a dump into it with
 * `ddev import-db --database=wp_source --file=<dump>`; see README.md.
 */
class WpSource
{
    public const DEFAULT_DATABASE = 'wp_source';
    public const DEFAULT_PREFIX = 'wp_';

    public function __construct(
        private readonly Connection $connection,
        private readonly string $tablePrefix = self::DEFAULT_PREFIX,
    ) {
        if (!preg_match('/^[A-Za-z0-9_]*$/', $tablePrefix)) {
            throw new InvalidArgumentException(sprintf('Invalid WordPress table prefix "%s".', $tablePrefix));
        }
    }

    /**
     * Builds the source from the plugin's `source` settings, falling back to TheatreCMS's own
     * connection parameters for anything not set (so on DDEV only the database name differs).
     *
     * @param array<string, mixed> $coreConnection TheatreCMS's Doctrine connection parameters
     * @param array<string, mixed> $settings       the plugin's `source` settings from app/config.yaml
     */
    public static function fromSettings(array $coreConnection, array $settings): self
    {
        $prefix = (string) ($settings['table_prefix'] ?? self::DEFAULT_PREFIX);
        unset($settings['table_prefix']);

        $parameters = $settings + ['dbname' => self::DEFAULT_DATABASE] + $coreConnection;

        return new self(DriverManager::getConnection($parameters), $prefix);
    }

    public function connection(): Connection
    {
        return $this->connection;
    }

    public function tablePrefix(): string
    {
        return $this->tablePrefix;
    }

    /**
     * The prefixed, quoted name of a WordPress table, e.g. `posts` → `wp_posts`.
     */
    public function table(string $name): string
    {
        return $this->connection->quoteSingleIdentifier($this->tablePrefix . $name);
    }

    /**
     * @return array<int, array{post_type: string, post_status: string, count: int}> ordered by type then status
     */
    public function postCounts(): array
    {
        $rows = $this->connection->fetchAllAssociative(sprintf(
            'SELECT post_type, post_status, COUNT(*) AS count FROM %s'
            . ' GROUP BY post_type, post_status ORDER BY post_type, post_status',
            $this->table('posts'),
        ));

        return array_map(static fn(array $row): array => [
            'post_type' => (string) $row['post_type'],
            'post_status' => (string) $row['post_status'],
            'count' => (int) $row['count'],
        ], $rows);
    }
}
