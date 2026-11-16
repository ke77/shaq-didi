<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonnelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()->role === UserRole::PermanentStaff,
            403,
            'Only Permanent Staff can view dispatch personnel.'
        );

        return response()->json(
            User::query()
                ->where('role', UserRole::NationalService)
                ->orderBy('name')
                ->get(['id', 'name', 'role'])
        );
    }
}
