<?php

namespace App\Enums;

enum LawyerRequestStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Rejected = 'rejected';
}
