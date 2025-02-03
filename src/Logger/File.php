<?php

declare(strict_types=1);

namespace Bratikov\MQ\Logger;

class File extends Generic
{
	public function __construct(readonly string $file)
	{
		error_reporting(E_ERROR);
		ini_set('error_log', $file);
		ini_set('log_errors', true);
		ini_set('display_errors', false);
	}

	/**
	 * @param string $level
	 */
	public function log($level, string|\Stringable $message, array $context = []): void
	{
		if (null !== $this->channelStorage) {
			$this->appendToChannelStorage(sprintf('[%s] %s: %s', date('Y-m-d H:i:s'), strtoupper((string) $level), $message));
		}

		if (null !== $this->identity) {
			$message = sprintf('[%s] %s', $this->identity, $message);
		}

		file_put_contents($this->file, sprintf('[%s] %s: %s', date('Y-m-d H:i:s'), strtoupper($level), $message).PHP_EOL, FILE_APPEND | LOCK_EX);
	}
}
