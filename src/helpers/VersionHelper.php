<?php

declare(strict_types=1);

namespace colindorr\craftcmsbetterupdates\helpers;

use Craft;
use craft\helpers\App;

class VersionHelper
{
    /**
     * Check if a version string is valid (e.g., "1.0.0").
     *
     */
    private static function isValidVersion(string $version): bool
    {
        return preg_match('/^\d+\.\d+\.\d+$/', $version) === 1;  // Convert preg_match result to boolean
    }

    /**
     * Compares two version strings and determines if the update is a patch, minor, or major.
     *
     * @param string $oldVersion The old version string (e.g., "1.0.0").
     * @param string $newVersion The new version string (e.g., "2.1.0").
     * @return string The type of update: "patch", "minor", "major", or "no update" if comparison is not possible.
     */
    public static function compareVersions(string $oldVersion, string $newVersion): string
    {
        // Ensure both versions are valid
        if (! self::isValidVersion($oldVersion) || ! self::isValidVersion($newVersion)) {
            return "No update";
        }

        // Split version strings into arrays
        $oldParts = explode('.', $oldVersion);
        $newParts = explode('.', $newVersion);

        // Parse version numbers into integers
        [$oldMajor, $oldMinor, $oldPatch] = array_map('intval', $oldParts);
        [$newMajor, $newMinor, $newPatch] = array_map('intval', $newParts);

        // Compare versions
        if ($newMajor !== $oldMajor) {
            return "Major";
        } elseif ($newMinor !== $oldMinor) {
            return "Minor";
        } elseif ($newPatch !== $oldPatch) {
            return "Patch";
        }

        return "No update"; // No update if versions are the same
    }


    public static function getItemDescription($item)
    {
        $is_critical_text = $item->is_critical ? "[Critical]" : "";
        $update_type_text = "[" . $item->update_type . "]" . " ";
        $item_name_text = $item->name . " ";
        $item_version_text = ($item->update_available ? ($item->version . " => " . $item->update_version) : $item->version);
        $item_status_text = $item->is_abandoned ? " (Abandoned)" : ($item->is_expired ? " (Expired)" : "");      

        return $is_critical_text . $update_type_text . $item_name_text . $item_version_text . $item_status_text ;
    }

    /**
     * Fetches Craft's license key from either the environment or license file.
     *
     */
    public static function getCraftLicenseKey(): ?string
    {
        // Check for license key in environment variables or license file
        return App::env('CRAFT_LICENSE_KEY') ?: (file_exists(Craft::getAlias('@config/license.key')) ? file_get_contents(Craft::getAlias('@config/license.key')) : null);
    }


    public static function orderUpdateResults(array $array)
    {
        // Define custom sort order
        $order = [
            "[Critical]" => 0,
            "[Major]" => 1,
            "[Minor]" => 2,
            "[Patch]" => 3,
            "[No update]" => 4,
        ];

        // Custom comparison function
        usort($array, function ($a, $b) use ($order) {
            // Extract the type from each string (e.g., "[Critical]", "[Minor]", etc.)
            preg_match('/\[(.*?)\]/', $a, $matchesA);
            preg_match('/\[(.*?)\]/', $b, $matchesB);

            $typeA = $matchesA[0] ?? '';
            $typeB = $matchesB[0] ?? '';

            // Compare based on custom order
            return $order[$typeA] <=> $order[$typeB];
        });

        return $array;
    }

}
