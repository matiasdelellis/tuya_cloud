<?php

declare(strict_types=1);

namespace OCA\TuyaCloud\Dashboard;

use OCA\TuyaCloud\AppInfo\Application;
use OCP\Dashboard\IWidget;
use OCP\IL10N;
use OCP\IURLGenerator;

class DevicesWidget implements IWidget {

	private $url;
	private $l10n;

	public function __construct(IURLGenerator $url,
	                            IL10N         $l10n)
	{
		$this->url = $url;
		$this->l10n = $l10n;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return Application::APP_ID;
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		return $this->l10n->t('Devices in Tuya');
	}

	/**
	 * @inheritDoc
	 */
	public function getOrder(): int {
		return 30;
	}

	/**
	 * @inheritDoc
	 */
	public function getIconClass(): string {
		return 'dashboard-tuya-icon';
	}

	/**
	 * @inheritDoc
	 */
	public function getUrl(): ?string {
		return $this->url->linkToRoute('settings.PersonalSettings.index', ['section' => Application::APP_ID]);
	}

	/**
	 * @inheritDoc
	 */
	public function load(): void {
		\OCP\Util::addStyle(Application::APP_ID, 'icons');
		\OCP\Util::addScript(Application::APP_ID, 'tuyacloud-dashboard');
	}
}
