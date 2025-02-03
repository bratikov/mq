<?php

namespace Bratikov\MQ;

class Stream
{
	/**
	 * @var array<int|string|bool>
	 */
	private array $opts = [];

	/**
	 * @var array<int|string>
	 */
	private array $headers = [];

	public function __construct(
		private readonly string $host,
		private readonly int|string $port,
		private string $method = 'GET',
	) {
	}

	public function withContent(string $content, string $method = 'POST'): self
	{
		$this->method = $method;
		$this->opts['content'] = $content;
		$this->headers['Content-Length'] = strlen($content);
		$this->headers['Content-Type'] = 'text/plain';

		return $this;
	}

	public function setTimeout(int $timeout): self
	{
		$this->opts['timeout'] = $timeout;

		return $this;
	}

	public function ignoreErrors(bool $ignoreErrors = true): self
	{
		$this->opts['ignore_errors'] = $ignoreErrors;

		return $this;
	}

	public function getResponse(string $route = ''): false|string
	{
		$this->opts = [
			'method' => $this->method,
		];

		if ($this->headers) {
			$this->opts['headers'] = $this->buildHeaders();
		}

		$ctx = stream_context_create([
			'http' => $this->opts,
		]);

		$host = $this->buildHostUrl($route);

		return @file_get_contents($host, false, $ctx);
	}

	private function buildHostUrl(string $route = ''): string
	{
		$host = sprintf('http://%s', $this->host);

		if ($this->port > 0) {
			$host .= ':'.$this->port;
		}

		$host .= '/';

		if ($route) {
			$host .= ltrim($route, '/');
		}

		return $host;
	}

	private function buildHeaders(): string
	{
		$allHeaders = '';

		foreach ($this->headers as $name => $value) {
			$allHeaders .= sprintf('%s: %s', $name, $value)."\r\n";
		}

		return $allHeaders;
	}
}
