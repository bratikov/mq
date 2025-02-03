<?php

declare(strict_types=1);

namespace Bratikov\MQ\Logger;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

class Stdout extends Generic
{
	private ConsoleOutput $out;

	public function __construct()
	{
		error_reporting(E_ERROR);
		ini_set('display_errors', true);
		$mapping = [
			self::EMERGENCY => 'red',
			self::ALERT => 'red',
			self::CRITICAL => 'red',
			self::ERROR => 'bright-red',
			self::WARNING => 'yellow',
			self::NOTICE => 'bright-yellow',
			self::INFO => 'white',
			self::DEBUG => 'gray',
		];
		$this->out = new ConsoleOutput();
		foreach ($mapping as $level => $color) {
			$this->out->getFormatter()->setStyle($level, new OutputFormatterStyle($color, null));
		}
	}

	/**
	 * @param string $level
	 */
	public function log($level, string|\Stringable $message, array $context = []): void
	{
		if (null !== $this->channelStorage) {
			$this->appendToChannelStorage(sprintf('[%s] %s: %s', date('Y-m-d H:i:s'), strtoupper($level), $message));
		}

		if (null !== $this->identity) {
			$message = sprintf('[%s] %s', $this->identity, $message);
		}
		$this->out->writeln(sprintf('<%s>[%s] %s: %s</%s>', $level, date('Y-m-d H:i:s'), strtoupper($level), $message, $level));
	}
}
