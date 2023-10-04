<?php
namespace OCA\TuyaCloud\Service;

use OCA\TuyaCloud\AppInfo\Application;
use OCA\TuyaCloud\Exceptions\AuthFailedException;
use OCA\TuyaCloud\Exceptions\FrequentlyInvokeException;
use OCA\TuyaCloud\Exceptions\QueryFailedException;

use OCP\App\IAppManager;
use OCP\ICache;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\ILogger;

class TuyaCloudService {

	/** @var IConfig */
	private $config;

	/** @var ILogger */
	private $logger;

	/** @var ICache */
	private $cache;

	public function __construct(
		IConfig $config,
		ILogger $logger,
		ICacheFactory $cacheFactory,
	) {
		$this->config = $config;
		$this->logger = $logger;
		$this->cache  = $cacheFactory->createDistributed(Application::APP_ID);
	}

	/**
	 * Checks if exists a valid access token in cache or try to
	 * obtain it with the access settings.
	 */
	public function hasConnection(string $userId) {
		return strlen($this->getAccessToken($userId)) > 0;
	}

	/**
	 * Removee all cached information, and force reconnection with
	 * the access settings.
	 */
	public function forceReconnect(string $userId) {
		$this->cache->remove($userId . ':access_token');
		$this->cache->remove($userId . ':skills');
	}

	/**
	 * Discovery all asociated devices and skills like scenes:
	 * NOTE: Once in 1020 seconds
	 */
	public function getSkills(string $userId) {
		$skills = $this->cache->get($userId . ':skills');
		if ($skills !== null)
			return json_decode($skills);

		$body = [
			"header" => [
				"name" => 'Discovery',
				"namespace" => 'discovery',
				"payloadVersion" => 1
			],
			"payload" => [
				"accessToken" => $this->getAccessToken($userId)
			]
		];

		$payload = $this->query($this->getBaseUrl($userId) . '/skill', 'GET', $body);

		$this->cache->set($userId . ':skills', json_encode($payload->devices), 1020);

		return $payload->devices;
	}

	/**
	 * Discovery all asociated devices:
	 * NOTE: Once in 1020 seconds
	 */
	public function getDevices(string $userId) {
		$skills = $this->getSkills($userId);
		$devices = array_filter($skills, function($skill) {
			return $skill->dev_type !== 'scene';
		});
		return $devices;
	}

	/**
	 * Get Device by id
	 */
	public function getDevice(string $userId, string $deviceId) {
		$devices = $this->getDevices($userId);
		foreach ($devices as $device) {
			if ($device->id === $deviceId) return $device;
		}
		return null;
	}

	/**
	 * Get device state:
	 * NOTE: Once in 180 seconds
	 */
	public function getDeviceState($userId, $deviceId) {
		$body = [
			"header" => [
				"name" => 'QueryDevice',
				"namespace" => 'query',
				"payloadVersion" => 1
			],
			"payload" => [
				"accessToken" => $this->getAccessToken($userId),
				"devId" => $deviceId,
				"value" => 1
			]
		];

		$payload = $this->query($this->getBaseUrl($userId) . '/skill', 'GET', $body);
		return $payload->devices;
	}

	/**
	 * Set device state:
	 */
	public function setDeviceState($userId, $deviceId, $command, $value) {
		if ($value === true || $value === 'on') $value = 1;
		else if ($value === false || $value === 'off') $value = 0;

		$body = [
			"header" => [
				"name" => $command,
				"namespace" => 'control',
				"payloadVersion" => 1
			],
			"payload" => [
				"accessToken" => $this->getAccessToken($userId),
				"devId" => $deviceId,
				"value" => $value
			]
		];

		$applied = $this->query($this->getBaseUrl($userId) . '/skill', 'POST', $body);
		// TODO: Update local cache
		return $applied;
	}

	private function getBaseUrl(string $userId): string
	{
		$region = $this->config->getUserValue($userId, 'tuya_cloud', 'region', null);
		return 'https://px1.tuya' . $region . '.com/homeassistant';
	}

	private function getAccessToken(string $userId): string
	{
		$userName    = $this->config->getUserValue($userId, 'tuya_cloud', 'username', null);
		$password    = $this->config->getUserValue($userId, 'tuya_cloud', 'password', null);
		$bizType     = $this->config->getUserValue($userId, 'tuya_cloud', 'biz_type', null);
		$countryCode = $this->config->getUserValue($userId, 'tuya_cloud', 'country_code', null);
		$region      = $this->config->getUserValue($userId, 'tuya_cloud', 'region', null);
		// TODO: Validate these

		$access_token = $this->cache->get($userId . ':access_token');
		if ($access_token != null)
			return $access_token;

		$ch = curl_init($this->getBaseUrl($userId) . "/auth.do");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,
			http_build_query([
				"userName"    => $userName,
				"password"    => $password,
				"bizType"     => $bizType,
				"countryCode" => $countryCode,
				"region"      => $region,
				"from"        => 'tuya'
			])
		);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/x-www-form-urlencoded'
		));

		$response = curl_exec($ch);
		$tokens = json_decode($response);

		if (@$tokens->responseStatus === "error") {
			throw new AuthFailedException($tokens->errorMsg);
		}

		$this->cache->set($userId . ':access_token', $tokens->access_token, $tokens->expires_in - 100);

		return $tokens->access_token;
	}

	private function query($url, $request, $body) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json'
		));

		$response = curl_exec($ch);
		if ($response === false)
			throw new QueryFailedException(curl_error($ch));

		$json = json_decode($response);
		if ($json->header->code == "FrequentlyInvoke")
			throw new FrequentlyInvokeException($json->header->msg);

		if ($request === 'POST')
			return ($json->header->code === "SUCCESS");
		else
			return $json->payload;
	}

}
