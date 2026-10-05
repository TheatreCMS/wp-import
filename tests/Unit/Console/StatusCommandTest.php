<?php

namespace TheatreCMS\WpImport\Tests\Unit\Console;

use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TheatreCMS\WpImport\Console\StatusCommand;
use TheatreCMS\WpImport\Tests\Unit\WpSourceTest;
use TheatreCMS\WpImport\WpSource;

class StatusCommandTest extends TestCase
{
    public function testPrintsCountsAndATotal(): void
    {
        $source = new WpSource(WpSourceTest::connectionWithPosts('wp_', [
            ['page', 'publish'],
            ['post', 'draft'],
            ['post', 'publish'],
            ['post', 'publish'],
        ]));
        $tester = new CommandTester(new StatusCommand($source));

        $this->assertSame(Command::SUCCESS, $tester->execute([]));
        $display = $tester->getDisplay(true);
        $this->assertMatchesRegularExpression('/\| page +\| publish +\| 1 +\|/', $display);
        $this->assertMatchesRegularExpression('/\| post +\| draft +\| 1 +\|/', $display);
        $this->assertMatchesRegularExpression('/\| post +\| publish +\| 2 +\|/', $display);
        $this->assertMatchesRegularExpression('/\| Total +\| +\| 4 +\|/', $display);
    }

    public function testReportsAnEmptySource(): void
    {
        $tester = new CommandTester(new StatusCommand(new WpSource(WpSourceTest::connectionWithPosts('wp_', []))));

        $this->assertSame(Command::SUCCESS, $tester->execute([]));
        $this->assertSame("The WordPress source has no posts.\n", $tester->getDisplay(true));
    }

    public function testFailsHelpfullyWithoutASourceDatabase(): void
    {
        $source = new WpSource(DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]));
        $tester = new CommandTester(new StatusCommand($source));

        $this->assertSame(Command::FAILURE, $tester->execute([]));
        $display = $tester->getDisplay(true);
        $this->assertStringContainsString('Cannot read wp_posts from the WordPress source database', $display);
        $this->assertStringContainsString('ddev import-db --database=wp_source', $display);
    }
}
