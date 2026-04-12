<?php
declare(strict_types=1);

namespace Assessment\SimpleQueue\Console\Command;

use Assessment\SimpleQueue\Model\MessagePublisher;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PublishCommand extends Command
{
    public function __construct(
        private readonly MessagePublisher $messagePublisher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('simple-queue:publish');
        $this->setDescription('Publish a SimpleQueue test message to Magento message queue.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->messagePublisher->execute();
        $output->writeln('OK');

        return Cli::RETURN_SUCCESS;
    }
}
