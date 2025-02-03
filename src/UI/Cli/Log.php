<?php

declare(strict_types=1);

namespace Bratikov\MQ\UI\Cli;

use Bratikov\MQ\Stream;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Log extends Command
{
	/**
	 * @return array<string, string>
	 */
	protected function getColorMapping(): array
	{
		return [
			'emr' => 'red',
			'alt' => 'red',
			'crt' => 'red',
			'err' => 'bright-red',
			'wrn' => 'yellow',
			'ntc' => 'bright-yellow',
			'inf' => 'white',
			'dbg' => 'gray',
		];
	}

	protected function configure(): void
	{
		$this
			->setDescription('shows task log')
			->addArgument('uuid', InputArgument::REQUIRED, 'UUID of task')
			->addOption('follow', 'f', InputOption::VALUE_NONE, 'Follow log');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$logs = $this->getTaskLogs($input, $output);
		if (empty($logs)) {
			$output->writeln('<comment>Task not found or has empty log</>');

			return Command::SUCCESS;
		}
		foreach ($this->getColorMapping() as $level => $color) {
			$output->getFormatter()->setStyle($level, new OutputFormatterStyle($color, null));
		}

		foreach ($logs as $msg) {
			$output->writeln($this->colorize($msg));
		}
		if ($input->getOption('follow')) {
			// @phpstan-ignore-next-line
			while (true) {
				$newLogs = $this->getTaskLogs($input, $output);
				$diff = array_diff($newLogs, $logs);
				foreach ($diff as $msg) {
					$output->writeln($this->colorize($msg));
				}
				$logs = $newLogs;
				sleep(1);
			}
		}

		return Command::SUCCESS;
	}

	private function colorize(string $msg): string
	{
		foreach ($this->getColorMapping() as $level => $color) {
			if (str_contains($msg, strtoupper($level).':')) {
				return sprintf('<%s>%s</>', $level, $msg);
			}
		}

		return $msg;
	}

	/**
	 * @return array<int, string>
	 */
	private function getTaskLogs(InputInterface $input, OutputInterface $output): array
	{
		/** @var string $host */
		$host = $input->getOption('host');
		/** @var int $port */
		$port = $input->getOption('port');
		/** @var string $uuid */
		$uuid = $input->getArgument('uuid');

		$response = (new Stream($host, $port))->setTimeout(1)->getResponse($uuid);
		if (false === $response) {
			$output->writeln('<error>Factory is not running</>');
			exit;
		}

		/** @var array<int, string> $logs */
		$logs = json_decode($response, true);
		if (false === $logs) {
			$output->writeln('<error>Invalid factory response</>');

			return [];
		}

		return $logs;
	}
}
