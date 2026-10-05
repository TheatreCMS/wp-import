<?php

namespace TheatreCMS\WpImport\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TheatreCMS\WpImport\WpSource;

#[AsCommand(name: 'wp-import:status', description: 'Count the source WordPress posts by post type and status')]
class StatusCommand extends Command
{
    public function __construct(private readonly WpSource $source)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $counts = $this->source->postCounts();
        } catch (\Throwable $e) {
            $errors = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
            $errors->writeln(sprintf(
                '<error>Cannot read %s from the WordPress source database: %s</error>',
                $this->source->tablePrefix() . 'posts',
                $e->getMessage(),
            ));
            $errors->writeln(
                'Load a dump with `ddev import-db --database=wp_source --file=<dump>`; see the wp-import README.'
            );

            return Command::FAILURE;
        }

        if ($counts === []) {
            $output->writeln('The WordPress source has no posts.');
            return Command::SUCCESS;
        }

        $table = (new Table($output))->setHeaders(['Post type', 'Status', 'Count']);
        $total = 0;
        $previousType = null;
        foreach ($counts as $row) {
            if ($previousType !== null && $row['post_type'] !== $previousType) {
                $table->addRow(new TableSeparator());
            }
            $table->addRow([$row['post_type'], $row['post_status'], $row['count']]);
            $total += $row['count'];
            $previousType = $row['post_type'];
        }
        $table->addRow(new TableSeparator());
        $table->addRow(['Total', '', $total]);
        $table->render();

        return Command::SUCCESS;
    }
}
