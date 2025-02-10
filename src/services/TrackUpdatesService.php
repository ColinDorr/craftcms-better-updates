<?php

declare(strict_types=1);

namespace colindorr\craftcmsbetterupdates\services;

use Craft;
use craft\helpers\App;
use colindorr\craftcmsbetterupdates\helpers\VersionHelper;
use Symfony\Component\Yaml\Yaml;

class TrackUpdatesService
{
    public static function getCraftUpdateInfo(): array
    {
        $craftVersion = Craft::$app->getVersion();
        $updateInfo = Craft::$app->getUpdates()->getUpdates();
        $latestCraftVersion = ! empty($updateInfo->cms->releases) ? $updateInfo->cms->releases[0]->version : $craftVersion;
        $craftLicenseKey = VersionHelper::getCraftLicenseKey();
        $is_abandoned = isset($updateInfo->cms->abandoned) && $updateInfo->cms->abandoned;
        $is_expired = isset($updateInfo->cms->status) && $updateInfo->cms->status !== "eligible";

        $containsCritical = isset($updateInfo->cms->releases) && 
            array_reduce($updateInfo->cms->releases, function($carry, $release) {
                return $carry || (isset($release->critical) && $release->critical === true);
            }, false);

        return [(object) [
            'type' => 'craft',
            'handle' => 'craftcms',
            'name' => 'Craft CMS',
            'version' => $craftVersion,
            'update_available' => $latestCraftVersion !== $craftVersion,
            'update_version' => $latestCraftVersion !== $craftVersion ? $latestCraftVersion : null,
            'update_type' => VersionHelper::compareVersions($latestCraftVersion, $craftVersion),
            'license_key' => $craftLicenseKey,
            'is_expired' => $is_expired,
            'is_critical' => $containsCritical,
            'is_abandoned' => $is_abandoned,
        ]];
    }

    public static function getPluginUpdateInfo(): array
    {
        $plugins = Craft::$app->getPlugins()->getAllPlugins();
        $updateInfo = Craft::$app->getUpdates()->getUpdates();

        // Load plugins from project.yaml
        $projectYamlFilePath = Craft::getAlias('@config/project/project.yaml');
        $projectYamlPlugins = [];
        if (file_exists($projectYamlFilePath)) {
            $parsedData = Yaml::parseFile($projectYamlFilePath);
            $projectYamlPlugins = $parsedData['plugins'] ?? [];
        }

        // Iterate through all plugins and add plugin objects to the $versions array
        $plugins_updates = [];
        foreach ($plugins as $handle => $plugin) {
            $plugin_handle = $plugin->id;
            $plugin_data = $updateInfo->plugins[$plugin_handle] ?? null ;
            $latestPluginVersion = $plugin_data && !empty($updateInfo->plugins[$plugin_handle]->releases) ? $updateInfo->plugins[$plugin_handle]->releases[0]->version : $plugin->version;
            $is_abandoned = $plugin_data && isset($plugin_data->abandoned) && $plugin_data->abandoned;
            $is_expired = $plugin_data && isset($plugin_data->status) && $plugin_data->status !== "eligible";
            $containsCritical = $plugin_data && !empty($updateInfo->plugins[$plugin_handle]->releases) && !empty(array_filter($updateInfo->plugins[$plugin_handle]->releases, function($release) {
                return $release->critical === true;
            }));

            // Get plugin license key
            $pluginLicenseKey = $projectYamlPlugins[$plugin_handle]['licenseKey'] ?? null;
            if ($pluginLicenseKey && $pluginLicenseKey[0] === "$") {
                $pluginLicenseKey = App::env(ltrim($pluginLicenseKey, '$')) ?? null;
            }

            $plugins_updates[] = (object) [
                'type' => 'plugin',
                'handle' => $plugin_handle,
                'name' => $plugin->name,
                'version' => $plugin->version,
                'update_available' => $latestPluginVersion !== $plugin->version,
                'update_version' => $latestPluginVersion !== $plugin->version ? $latestPluginVersion : null,
                'update_type' => VersionHelper::compareVersions($latestPluginVersion, $plugin->version),
                'license_key' => $pluginLicenseKey,
                'is_expired' => $is_expired,
                'is_critical' => $containsCritical,
                'is_abandoned' => $is_abandoned,
            ];
        }
        return $plugins_updates;
    }

    public static function getUpdateInfo(): array
    {
        $craft_updates = self::getCraftUpdateInfo();
        $plugin_updates = self::getPluginUpdateInfo();
        $updates = array_merge($craft_updates, $plugin_updates);

        $available_update = [];
        $up_to_date = [];

        if (! empty($updates)) {
            foreach ($updates as $item) {
                if ($item->update_available) {
                    $available_update[] = VersionHelper::getItemDescription($item);
                } else {
                    $up_to_date[] = VersionHelper::getItemDescription($item);
                }
            }
        }

        return [
            "available_update" => VersionHelper::orderUpdateResults($available_update),
            "up_to_date" => VersionHelper::orderUpdateResults($up_to_date),
        ];
    }
}
