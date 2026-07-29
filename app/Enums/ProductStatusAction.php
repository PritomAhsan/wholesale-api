<?php

namespace App\Enums;

enum ProductStatusAction:string
{
    case PUBLISHED='published';

    case UNPUBLISHED='unpublished';

    case ARCHIVED='archived';

    case RESTORED='restored';

    case DELETED='deleted';

    case FORCE_DELETED='force_deleted';
}
