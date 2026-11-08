<?php

namespace App\Enums;

enum UserRole: string
{
     case PermanentStaff = 'permanent_staff';
     case NationalService = 'national_service';
}