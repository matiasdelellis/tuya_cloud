<?php
declare(strict_types=1);

namespace OCA\TuyaCloud\Controller;

use OCA\TuyaCloud\AppInfo\Application;
use OCA\TuyaCloud\Service\TuyaCloudService;
use OCA\TuyaCloud\Service\DownloaderService;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\IRequest;

class DevicesController extends Controller {

	/** @var TuyaCloudService */
	private $service;

	/** @var DownloaderService */
	private $downloader;

	/** @var string */
	private $userId;

	public function __construct(
		IRequest $request,
		TuyaCloudService $service,
		DownloaderService $downloader,
		$userId)
	{
		parent::__construct(Application::APP_ID, $request);

		$this->service = $service;
		$this->downloader = $downloader;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index(): DataResponse {
		return new DataResponse($this->service->getDevices($this->userId));
	}

	/**
	 * @NoAdminRequired
	 */
	public function setDeviceState($id, $command, $value): DataResponse {
		return new DataResponse($this->service->setDeviceState($this->userId, $id, $command, $value));
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getDeviceIcon($id) {
		$device = $this->service->getDevice($this->userId, $id);

		$icon = $this->downloader->get($device->icon);

		$response = new DataDisplayResponse($icon);
		$response->cacheFor(60*60*24);
		return $response;
	}

	/**
	 * @NoAdminRequired
	 */
	public function dashboard(): DataResponse {
		return new DataResponse([
			'devices' => $this->service->getDevices($this->userId)
		]);
	}

}
