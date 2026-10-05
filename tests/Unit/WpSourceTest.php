<?php

namespace TheatreCMS\WpImport\Tests\Unit;

use Doctrine\DBAL\DriverManager;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TheatreCMS\WpImport\WpSource;

class WpSourceTest extends TestCase
{
    public function testCountsPostsByTypeAndStatus(): void
    {
        $source = new WpSource(self::connectionWithPosts('wp_', [
            ['page', 'publish'],
            ['dso_prod_season', 'draft'],
            ['dso_prod_season', 'publish'],
            ['dso_prod_season', 'publish'],
        ]));

        $this->assertSame([
            ['post_type' => 'dso_prod_season', 'post_status' => 'draft', 'count' => 1],
            ['post_type' => 'dso_prod_season', 'post_status' => 'publish', 'count' => 2],
            ['post_type' => 'page', 'post_status' => 'publish', 'count' => 1],
        ], $source->postCounts());
    }

    public function testUsesTheTablePrefix(): void
    {
        $source = new WpSource(self::connectionWithPosts('dso_', [['post', 'publish']]), 'dso_');

        $this->assertSame('"dso_posts"', $source->table('posts'));
        $this->assertSame(1, $source->postCounts()[0]['count']);
    }

    public function testRejectsAnUnsafeTablePrefix(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new WpSource(DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]), 'wp_; DROP');
    }

    public function testSettingsOverrideTheCoreConnectionAndDefaultTheDatabase(): void
    {
        $core = ['driver' => 'pdo_sqlite', 'memory' => true, 'dbname' => 'db', 'user' => 'db'];

        $defaults = WpSource::fromSettings($core, []);
        $this->assertSame(WpSource::DEFAULT_PREFIX, $defaults->tablePrefix());
        $this->assertSame('wp_source', $defaults->connection()->getParams()['dbname']);
        $this->assertSame('db', $defaults->connection()->getParams()['user']);

        $custom = WpSource::fromSettings($core, ['dbname' => 'dso_wp', 'user' => 'reader', 'table_prefix' => 'dso_']);
        $this->assertSame('dso_', $custom->tablePrefix());
        $this->assertSame('dso_wp', $custom->connection()->getParams()['dbname']);
        $this->assertSame('reader', $custom->connection()->getParams()['user']);
        $this->assertArrayNotHasKey('table_prefix', $custom->connection()->getParams());
    }

    /**
     * @param array<int, array{0: string, 1: string}> $posts post type, post status
     */
    public static function connectionWithPosts(string $prefix, array $posts): \Doctrine\DBAL\Connection
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement(
            "CREATE TABLE {$prefix}posts (ID INTEGER PRIMARY KEY, post_type VARCHAR(20), post_status VARCHAR(20))"
        );
        foreach ($posts as [$type, $status]) {
            $connection->insert($prefix . 'posts', ['post_type' => $type, 'post_status' => $status]);
        }

        return $connection;
    }
}
