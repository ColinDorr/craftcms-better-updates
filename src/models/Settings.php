<?php

declare(strict_types=1);

namespace colindorr\craftcmsbetterupdates\models;

use craft\base\Model;

class Settings extends Model
{
    public string $email = "";
    public string $version_type = "Minor";
    public string $day_of_week = "Monday";
    public string $frequency = "Weekly";
    public int $next_planned_email_timestamp = 0;  // Corrected to int type

    public function rules(): array
    {
        return [
            [["email", "version_type", "day_of_week", "frequency"], 'string'],
            [["next_planned_email_timestamp"], 'integer'], // Added validation rule for integer
        ];
    }
}
