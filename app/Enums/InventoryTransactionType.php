<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case INITIAL = 'initial';

    case PURCHASE = 'purchase';

    case SALE = 'sale';

    case RETURN = 'return';

    case ADJUSTMENT = 'adjustment';

    case DAMAGE = 'damage';

    case RESERVED = 'reserved';

    case RELEASED = 'released';

    case CANCELLED_ORDER = 'cancelled_order';

    case TRANSFER = 'transfer';
}
