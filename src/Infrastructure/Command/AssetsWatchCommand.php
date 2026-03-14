<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

#[AsCommand(
    name: 'app:assets:watch',
    description: 'Watch assets on the current site, clean public/assets and recompile automatically'
)]
class AssetsWatchCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('interval', 'i', InputOption::VALUE_REQUIRED, 'Polling interval in seconds', '1');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io       = new SymfonyStyle($input, $output);
        $fs       = new Filesystem();
        $dirs     = ['assets'];
        $outDir   = 'public/assets';
        $interval = max(1, (int)$input->getOption('interval'));

        $fingerprint = function (array $dirs): string {
            $parts = [];
            foreach ($dirs as $dir) {
                $it = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
                );
                /** @var \SplFileInfo $file */
                foreach ($it as $file) {
                    if ($file->isFile()) {
                        $rel     = substr($file->getPathname(), strlen($dir));
                        $parts[] = $rel . '|' . $file->getSize() . '|' . $file->getMTime();
                    }
                }
            }
            sort($parts);

            return hash('xxh3', implode("\n", $parts));
        };

        $compile = function () use ($io, $fs, $outDir) {
            $io->writeln('Cleaning ' . $outDir);
            if (is_dir($outDir)) {
                $fs->remove($outDir);
            }
            $fs->mkdir($outDir);

            $io->section('Executing: php bin/console asset-map:compile');
            $process = new Process(['php', 'bin/console', 'asset-map:compile']);
            $process->setTimeout(null);
            $process->run(function ($type, $buffer) use ($io) {
                $io->write($buffer);
            });

            if (!$process->isSuccessful()) {
                $io->error('Error in asset-map:compile');
            } else {
                $io->success('Assets compiled');
            }
        };

        $io->title('Assets Watch');
        $io->writeln(sprintf('Watching %s every %ds for changes. Ctrl+C to exit.', implode(', ', $dirs), $interval));
        $io->newLine();

        $compile();

        $prev = $fingerprint($dirs);
        $io->writeln('Watching for changes...');
        while (true) {
            sleep($interval);
            $curr = $fingerprint($dirs);
            if ($curr !== $prev) {
                $io->newLine();
                $io->writeln('Changes detected, recompiling...');
                $compile();
                $prev = $curr;
                $io->writeln('Watching for changes...');
            }
        }
    }
}
