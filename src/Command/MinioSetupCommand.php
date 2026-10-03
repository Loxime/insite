<?php

namespace App\Command;

use App\Service\MinioStorage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:minio:setup',
    description: 'Vérifie MinIO et crée le bucket applicatif.',
)]
final class MinioSetupCommand extends Command
{
    public function __construct(
        private readonly MinioStorage $storage,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $this->storage->ensureBucketExists();

        $output->writeln(
            '<info>Bucket MinIO prêt.</info>',
        );

        return Command::SUCCESS;
    }
}
