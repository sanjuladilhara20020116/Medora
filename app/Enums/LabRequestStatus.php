<?php

namespace App\Enums;

enum LabRequestStatus: string
{
    case REQUESTED = 'REQUESTED';

    case SAMPLE_COLLECTED = 'SAMPLE_COLLECTED';

    case PROCESSING = 'PROCESSING';

    case COMPLETED = 'COMPLETED';

    case CANCELLED = 'CANCELLED';
}