<?php

declare(strict_types=1);

namespace Bratikov\MQ\UI\Cli;

use Bratikov\MQ\Stream;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Status extends Command
{
	protected function configure(): void
	{
		$this
			->setDescription('shows actual factory status')
			->addOption('follow', 'f', InputOption::VALUE_NONE, 'Display status realtime');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$channels = $this->getChannels($input, $output);
		if (empty($channels)) {
			return Command::FAILURE;
		}

		$follow = $input->getOption('follow');
		if (!$follow) {
			$this->render($channels, $output);

			return Command::SUCCESS;
		}

		echo str_repeat(PHP_EOL, count($channels) + 4);
		while (true) {
			$tableHeight = count($channels) + 4;
			echo "\033[{$tableHeight}A";
			$this->render($channels, $output);
			sleep(1);
			$channels = $this->getChannels($input, $output);
			if (empty($channels)) {
				return Command::FAILURE;
			}
		}
	}

	/**
	 * @return array<int, string[]>
	 */
	private function getChannels(InputInterface $input, OutputInterface $output): array
	{
		/** @var string $host */
		$host = $input->getOption('host');
		/** @var int $port */
		$port = $input->getOption('port');

		$status = (new Stream($host, $port))->setTimeout(1)->getResponse();

		if (false === $status) {
			$output->writeln('<error>Factory is not running</>');
			exit;
		}

		/** @var array<int, string[]> $channels */
		$channels = json_decode($status, true);
		if (false === $channels) {
			$output->writeln('<error>Invalid factory response</>');

			return [];
		}

		return is_array($channels) ? $channels : [];
	}

	/**
	 * @param array<int, string[]> $channels
	 */
	private function render(array $channels, OutputInterface $output): void
	{
		$table = new Table($output);
		$table->setHeaders(['Channel', 'Waiting', 'Processing', 'Success', 'Failed', 'Total', 'Status']);
		foreach ($channels as $channel) {
			$chStatus = 'active' == $channel['status'] ? '<info>' : '<error>';
			$table->addRow([
				$channel['name'],
				$channel['queue'],
				$channel['pool'],
				$channel['success'],
				$channel['failed'],
				$channel['total'],
				$chStatus.$channel['status'].'</>',
			]);
		}
		$table->render();
	}
}
