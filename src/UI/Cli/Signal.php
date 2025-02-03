<?php

declare(strict_types=1);

namespace Bratikov\MQ\UI\Cli;

use Bratikov\MQ\Stream;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Signal extends Command
{
	protected function configure(): void
	{
		$this
			->setDescription('signal to the factory')
			->addArgument('channel', InputArgument::OPTIONAL, 'channel name');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$action = $this->getName();
		/** @var string $channelName */
		$channelName = $input->getArgument('channel');
		if ('flush' === $action) {
			$output->write(sprintf('<comment>Do you want to %s %s? (y/n): </>', $action, $channelName ? $channelName.' channel' : 'factory'));
			if ('y' !== trim((string) fgets(STDIN))) {
				$output->writeln('<info>Operation cancelled</>');

				return Command::SUCCESS;
			}
		}

		/** @var string $host */
		$host = $input->getOption('host');
		/** @var int $port */
		$port = $input->getOption('port');

		$response = (new Stream($host, $port, 'PATCH'))
			->setTimeout(1)
			->ignoreErrors()
			->getResponse($this->getName().'/'.$channelName);

		if (false === $response) {
			$output->writeln('<error>Factory is not running</>');

			return Command::FAILURE;
		}

		if ('ack' !== $response) {
			$output->writeln('<error>Channel not found</>');

			return Command::FAILURE;
		}

		$output->writeln('<info>Action applied!</>');

		return Command::SUCCESS;
	}
}
