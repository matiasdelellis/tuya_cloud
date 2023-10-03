<?php

declare(strict_types=1);

namespace OCA\TuyaCloud\Service;

use Exception;
use OCP\Http\Client\IClientService;
use function is_resource;
use function stream_get_contents;

class DownloaderService {
	/** @var IClientService */
	private $clientService;

	public function __construct(IClientService $clientService) {
		$this->clientService = $clientService;
	}

	/**
	 * @param string $url
	 * @return string|null
	 */
	public function get(string $url) {
		$client = $this->clientService->newClient();

		try {
			$resp = $client->get($url);
		} catch (Exception $e) {
			return null;
		}

		$body = $resp->getBody();
		if (is_resource($body)) {
			return stream_get_contents($body);
		}
		return $body;
	}
}
