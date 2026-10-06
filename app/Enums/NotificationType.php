<?php

namespace App\Enums;

enum NotificationType: string
{
    case BranchRequest = 'branch_request';
    case BranchApproved = 'branch_approved';
    case BranchRejected = 'branch_rejected';
    case JobApplication = 'job_application';
    case ApplicationStatus = 'application_status';
    case Invitation = 'invitation';
    case Admin = 'admin';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
