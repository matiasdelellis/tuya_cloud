<?php
declare(strict_types=1);

namespace OCA\TuyaCloud\Settings;

use OCA\TuyaCloud\AppInfo\Application;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class PersonalSection implements IIconSection
{
    /** @var IURLGenerator */
    private $urlGenerator;
    /** @var IL10N */
    private $l;

    public function __construct(IURLGenerator $urlGenerator, IL10N $l)
    {
        $this->urlGenerator = $urlGenerator;
        $this->l = $l;
    }

    /**
     * returns the relative path to an 16*16 icon describing the section.
     *
     * @returns string
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg');
    }

    /**
     * returns the ID of the section. It is supposed to be a lower case string,
     *
     * @returns string
     *
     * @return string
     *
     * @psalm-return 'tuya_cloud'
     */
    public function getID()
    {
        return Application::APP_ID;
    }

    /**
     * returns the translated name as it should be displayed
     *
     * @return string
     */
    public function getName()
    {
        return $this->l->t('Tuya Cloud Integration');
    }

    /**
     * returns priority for positioning
     *
     * @return int
     */
    public function getPriority()
    {
        return 10;
    }
}