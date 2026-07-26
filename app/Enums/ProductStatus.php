<?php

namespace App\Enums;

enum ProductStatus: string
{
    case DRAFT = 'draft';

    case PENDING = 'pending';

    case APPROVED = 'approved';

    case REJECTED = 'rejected';

    case PUBLISHED = 'published';

    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {

            self::DRAFT => 'Draft',

            self::PENDING => 'Pending',

            self::APPROVED => 'Approved',

            self::REJECTED => 'Rejected',

            self::PUBLISHED => 'Published',

            self::ARCHIVED => 'Archived',

        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
