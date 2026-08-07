<?php

namespace App\Domain\Newsletter\Enums;

enum NewsletterSubscriberStatus: string
{
    case Pending = 'pending';
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';
}
