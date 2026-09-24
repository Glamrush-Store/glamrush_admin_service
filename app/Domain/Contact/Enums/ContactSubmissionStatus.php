<?php

namespace App\Domain\Contact\Enums;

enum ContactSubmissionStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Spam = 'spam';
}
