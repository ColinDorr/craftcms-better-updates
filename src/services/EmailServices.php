<?php

declare(strict_types=1);

namespace colindorr\craftcmsbetterupdates\services;

use Craft;
use craft\base\Component;
use craft\mail\Message;

use colindorr\craftcmsbetterupdates\helpers\SettingsHelpers;
use colindorr\craftcmsbetterupdates\helpers\EmailHelpers;
use colindorr\craftcmsbetterupdates\services\TrackUpdatesService;

class EmailServices extends Component
{
    private static function getEmailMessage()
    {
        $updateInfo = TrackUpdatesService::getUpdateInfo();
        $available_update = $updateInfo['available_update'] ?? [];
        $up_to_date = $updateInfo['up_to_date'] ?? [];

        $message = "<div>";
        if (count($available_update) > 0) {
            $message .= "<p style='font-weight:600'>Available update" . (count($available_update) !== 1 ? "s" : "") . "</p><ul>";
            foreach ($available_update as $update) {
                $message .= "<li>" . $update . "</li>";
            }
            $message .= "</ul><br>";
        }

        if (count($up_to_date) > 0) {
            $message .= "<p style='font-weight:600'>Up to date</p><ul>";
            foreach ($up_to_date as $update) {
                $message .= "<li>" . $update . "</li>";
            }
            $message .= "</ul>";
        }
        $message .= "</div>";

        return $message;
    }

    public static function sendEmail()
    {
        $settings = SettingsHelpers::getPluginSettings();
        $site = Craft::$app->getSites()->getPrimarySite()->name;
        $email = $settings->email ?? null;
        $subject = "[" . $site . "] has available updates";
        $html = EmailHelpers::getEmailTemplate(self::getEmailMessage());

        if (!$email) {
            // Log the error for debugging in Craft logs
            Craft::error("No valid email provided for update notifications.", __METHOD__);

            // Display error message in CLI
            if (Craft::$app->request->getIsConsoleRequest()) {
                fwrite(STDERR, "❌ Error: No valid email provided for update notifications.\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }

            // Display error in the control panel (if relevant)
            throw new \yii\base\Exception("No valid email provided for update notifications.");
        }

        $emailArray = array_map('trim', explode(',', $email));

        Craft::$app
            ->getMailer()
            ->compose()
            ->setTo($emailArray)
            ->setSubject($subject)
            ->setHtmlBody($html)
            ->send();
    }

}
