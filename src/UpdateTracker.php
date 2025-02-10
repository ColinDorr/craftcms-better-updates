<?php

declare(strict_types=1);

namespace colindorr\craftcmsbetterupdates;

use Craft;
use craft\base\Plugin;
use craft\base\Model;
use craft\web\Application;
use colindorr\craftcmsbetterupdates\models\Settings;

use colindorr\craftcmsbetterupdates\services\UpdateNotificationServices;
use colindorr\craftcmsbetterupdates\services\TrackUpdatesService;

/**
 * better-updates plugin
 *
 * @method static TrackUpdates getInstance()
 * @method Settings getSettings()
 */
class UpdateTracker extends Plugin
{
    public static string $plugin_handle = "better-updates";
    public bool $hasCpSettings = true;
    public string $schemaVersion = '2.0.0';
    
    public function init(): void
    {
        parent::init();

        Craft::$app->on(Application::EVENT_INIT, function () {
            UpdateNotificationServices::checkForUpdatesAndSendEmail();
        });

        if (Craft::$app->request->getIsConsoleRequest()) {
            // Set the correct namespace for console commands
            $this->controllerNamespace = 'colindorr\craftcmsbetterupdates\console';
        }
    }

    public function iconPath(): ?string
    {
        return __DIR__ . '/icon.svg';
    }
    
    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate(self::$plugin_handle . "/_settings.twig", [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }
}
