<?php

namespace Blackbit\PimcoreBigcommerceSyncBundle\Services;

use Pimcore\Config\Config;
use Pimcore\Model\WebsiteSetting;
use Pimcore\Tool;

use function Blackbit\DataDirectorBundle\lib\Pim\Import\self;

class PimcoreService
{
    const WEBSITE_SETTING_TYPE_TEXT = 'TEXT';

    private WebsiteSetting $websiteSetting;

    public function __construct(WebsiteSetting $websiteSetting)
    {
        $this->websiteSetting = $websiteSetting;
    }

    private static function prepareWebsiteSettings(): array
    {
        $data = [];
        $validLanguages = Tool::getValidLanguages();

        foreach ( $validLanguages as $leng) {
            foreach (BigcommerceService::AUTH_SETTINGS as $authSetting) {
                $data[] = [
                    'name' => $authSetting,
                    'leng' => $leng,
                    'type' => self::WEBSITE_SETTING_TYPE_TEXT
                ];
            }
        }

        return $data;
    }

    public static function createWebsiteSetting(): void
    {
        $authSettings = self::prepareWebsiteSettings();

        foreach ($authSettings as $authSetting) {
            if (!WebsiteSetting::getByName($authSetting['name'], null, $authSetting['leng'])) {
                $websiteSetting = new WebsiteSetting();
                $websiteSetting->setName($authSetting['name']);
                $websiteSetting->setLanguage($authSetting['leng']);
                $websiteSetting->setType($authSetting['type']);
                $websiteSetting->save();
            }
        }
    }

    public static function deleteWebsiteSetting(): void
    {
        $authSettings = self::prepareWebsiteSettings();

        foreach ($authSettings as $authSetting) {
            $websiteSetting = WebsiteSetting::getByName($authSetting['name'], null, $authSetting['leng']);

            if ($websiteSetting) {
                $websiteSetting->delete();
            }
        }
    }
}
