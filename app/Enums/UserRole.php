<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'ADMIN';

    case DOCTOR = 'DOCTOR';

    case NURSE = 'NURSE';

    case RECEPTIONIST = 'RECEPTIONIST';

    case LAB_STAFF = 'LAB_STAFF';

    case PHARMACIST = 'PHARMACIST';

    case ACCOUNTANT = 'ACCOUNTANT';
}