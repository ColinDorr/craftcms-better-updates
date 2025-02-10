<?php

namespace colindorr\craftcmsbetterupdates\console;

use Craft;
use yii\console\Controller;
use yii\console\ExitCode;
use colindorr\craftcmsbetterupdates\services\UpdateNotificationServices;
use colindorr\craftcmsbetterupdates\services\TrackUpdatesService;

class CheckForUpdatesController extends Controller
{
    /**
     * @var bool Whether to force the update check
     */
    public bool $force = false;

    /**
     * Define available options for the command
     *
     * @return array
     */
    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), ['force']);
    }

    /**
     * Run the update check and send notifications.
     *
     * Usage:
     * - `php craft better-updates/check-for-updates/run`
     * - `php craft better-updates/check-for-updates/run --force`
     *
     * @return int Exit code
     */
    public function actionRun(): int
    {
        try {
            $updateService = new UpdateNotificationServices();
            $update_status = $updateService->checkForUpdatesAndSendEmail($this->force);

            $this->stdout("\nMail status:\n-------------------------\n");
            foreach ($update_status as $status) {
                $this->stdout("[Status] " . $status . "\n");
            }
            $this->stdout("\n");

            $updateInfo = TrackUpdatesService::getUpdateInfo();
            $available_update = $updateInfo['available_update'] ?? [];
            $up_to_date = $updateInfo['up_to_date'] ?? [];

            if (!empty($available_update)) {
                $this->stdout("Available updates:\n-------------------------\n");
                foreach ($available_update as $item) {
                    $this->stdout($item . "\n");
                }
                $this->stdout("\n");
            }

            if (!empty($up_to_date)) {
                $this->stdout("Up to date:\n-------------------------\n");
                foreach ($up_to_date as $item) {
                    $this->stdout($item . "\n");
                }
            }

            return ExitCode::OK;
        } catch (\Throwable $e) {
            $this->stderr("\nERROR\n-------------------------\n");
            $this->stderr("Something went wrong:\n" . $e->getMessage() . "\n\n");

            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Run a test update check and send notifications.
     *
     * Usage:
     * - `php craft better-updates/check-for-updates/test`
     *
     * @return int Exit code
     */
    public function actionTest(): int
    {
        $this->force = true;
        return $this->actionRun();
    }
}
