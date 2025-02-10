<?php

declare(strict_types=1);

namespace colindorr\craftcmsbetterupdates\helpers;

use Craft;
use colindorr\craftcmsbetterupdates\models\Settings;

class SettingsHelpers
{
    public static string $plugin_handle = "better-updates";

    public static function getPluginSettings()
    {
        // Get the plugin's settings
        $pluginSettings = Craft::$app->plugins->getPlugin(self::$plugin_handle)->settings;

        // Create an instance of Settings for default values
        $defaultSettings = new Settings();

        // Return settings with fallback to defaults if not set
        return (object) [
            "email" => $pluginSettings->email ?? $defaultSettings->email ?? "",
            "version_type" => $pluginSettings->version_type ?? $defaultSettings->version_type ?? "Minor",
            "day_of_week" => $pluginSettings->day_of_week ?? $defaultSettings->day_of_week ?? "Monday",
            "frequency" => $pluginSettings->frequency ?? $defaultSettings->frequency ?? "Weekly",
        ];
    }
}
