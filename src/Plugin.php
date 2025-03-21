<?php

declare(strict_types=1);

namespace ColinDorr\BetterUpdates;

use Craft;
use craft\base\Plugin as PluginBase;
use craft\base\Model;
use ColinDorr\BetterUpdates\models\Settings;
use ColinDorr\BetterUpdates\events\RegisterAssetBundle;
use ColinDorr\BetterUpdates\events\RegisterUpdateValidations;
use ColinDorr\BetterUpdates\handlers\Notifications;

class Plugin extends PluginBase
{
    public static $plugin_handle = 'better-updates';
    public string $schemaVersion = '3.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;

    public function init(): void
    {
        parent::init();

        Craft::setAlias('@' . self::$plugin_handle , __DIR__);

        Notifications::LogMessage("Loaded: Better updates plugin");

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            RegisterAssetBundle::register();
        }

        RegisterUpdateValidations::register();
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    public function getCpNavItem(): ?array
    {
        $user = Craft::$app->getUser()->getIdentity();

        if (!$user) {
            return null;
        }

        return [
            'label' => 'Better Updates',
            'url' => self::$plugin_handle,
            'icon' => Craft::getAlias('@' . self::$plugin_handle . '/Resources/assets/icon-cp.svg')
        ];
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'better-updates/_settings.twig',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}