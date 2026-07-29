<?php

namespace App\Enums;

enum ProductApprovalAction: string
{
    case SUBMITTED = 'submitted';

    case RESUBMITTED = 'resubmitted';

    case APPROVED = 'approved';

    case REJECTED = 'rejected';

    case PUBLISHED = 'published';

    case UNPUBLISHED = 'unpublished';

    case ARCHIVED = 'archived';
}
