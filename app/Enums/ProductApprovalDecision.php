<?php

namespace App\Enums;

enum ProductApprovalDecision: string
{
    case PENDING = 'pending';

    case APPROVED = 'approved';

    case REJECTED = 'rejected';
}
