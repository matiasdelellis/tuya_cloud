<?php
declare(strict_types=1);

namespace OCA\TuyaCloud\Settings;

use OCA\TuyaCloud\AppInfo\Application;
use OCA\TuyaCloud\Service\TuyaCloudService;
use OCA\TuyaCloud\Exceptions\AuthFailedException;
use OCA\TuyaCloud\Exceptions\QueryFailedException;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\IL10N;
use OCP\Util;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;

class Personal implements ISettings {

	protected IConfig $config;

	protected IInitialState $initialState;

	protected TuyaCloudService $service;

	protected ?string $userId;

	public function __construct(
		IConfig $config,
		IInitialState $initialState,
		TuyaCloudService $service,
		string $userId)
	{
		$this->config = $config;
		$this->initialState = $initialState;
		$this->service = $service;
		$this->userId = $userId;
	}

	public function getPriority()
	{
		return 20;
	}

	public function getSection()
	{
		return Application::APP_ID;
	}

	public function getSectionID(): string
	{
		return Application::APP_ID;
	}

	public function getForm()
	{
		Util::addScript(Application::APP_ID, 'tuyacloud-settings');

		$this->initialState->provideInitialState(
			'username',
			$this->config->getUserValue($this->userId, 'tuya_cloud', 'username', null)
		);
		$this->initialState->provideInitialState(
			'password',
			$this->config->getUserValue($this->userId, 'tuya_cloud', 'password', null)
		);
		$this->initialState->provideInitialState(
			'biz_type',
			$this->config->getUserValue($this->userId, 'tuya_cloud', 'biz_type', null)
		);
		$this->initialState->provideInitialState(
			'country_code',
			$this->config->getUserValue($this->userId, 'tuya_cloud', 'country_code', null)
		);

		try {
			$hasConnection = $this->service->hasConnection($this->userId);
			$devicesCount = count($this->service->getDevices($this->userId));
			$errorMsg = '';
		} catch (QueryFailedException $e) {
			$hasConnection = false;
			$devicesCount = 0;
			$errorMsg = $e->getMessage();
		} catch (AuthFailedException $e) {
			$hasConnection = false;
			$devicesCount = 0;
			$errorMsg = $e->getMessage();
		}

		$this->initialState->provideInitialState(
			'has_connection',
			$hasConnection
		);
		$this->initialState->provideInitialState(
			'devices_count',
			$devicesCount
		);

		$this->initialState->provideInitialState(
			'error_msg',
			$errorMsg
		);

		return new TemplateResponse(Application::APP_ID, 'settings/personal');
	}

	public function getPanel(): TemplateResponse
	{
		return $this->getForm();
	}

}