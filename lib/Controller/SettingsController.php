<?php
declare(strict_types=1);

namespace OCA\TuyaCloud\Controller;

use OCA\TuyaCloud\AppInfo\Application;
use OCA\TuyaCloud\Exceptions\AuthFailedException;
use OCA\TuyaCloud\Service\TuyaCloudService;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;

class SettingsController extends Controller {

	private IConfig $config;
	private TuyaCloudService $service;
	private $userId;

	public function __construct(
		IRequest $request,
		IConfig $config,
		TuyaCloudService $service,
		$userId)
	{
		parent::__construct(Application::APP_ID, $request);

		$this->config = $config;
		$this->service = $service;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 * Set config values
	 *
	 * @return DataResponse
	 */
	public function setConfig(array $values): DataResponse {
		if ($this->userId === null) {
			return new DataResponse([], Http::STATUS_BAD_REQUEST);
		}

		foreach ($values as $key => $value) {
			$this->config->setUserValue($this->userId, Application::APP_ID, $key, $value);
		}

		$this->service->forceReconnect($this->userId);

		try {
			$hasConnection = $this->service->hasConnection($this->userId);
			$devicesCount = count($this->service->getDevices($this->userId));
			$errorMsg = '';
		} catch (AuthFailedException $e) {
			$hasConnection = false;
			$devicesCount = 0;
			$errorMsg = $e->getMessage();
		}

		return new DataResponse([
			'devices_count' => $devicesCount,
			'has_connection' => $hasConnection,
			'error_msg' => $errorMsg
		]);
	}

}
