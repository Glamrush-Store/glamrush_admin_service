<?php

namespace App\Domain\Discount\Enums;

enum DiscountTargetMode: string
{
    case Include = 'include';
    case Exclude = 'exclude';
}
