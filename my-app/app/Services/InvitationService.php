<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use App\Models\Invitation;
use Illuminate\Support\Str;

class InvitationService
{
    public function invite(User $inviter, string $email, string $role): Invitation
    {
        if ($inviter->hasRole('SuperAdmin')) {

            if ($role !== 'Admin') {
                abort(403, 'SuperAdmin can only invite Admin');
            }

            $company = Company::create([
                'name' => $email . "'s Company"
            ]);
        } elseif ($inviter->hasRole('Admin')) {

            if (!in_array($role, ['Admin', 'Member'])) {
                abort(403, 'Admin can invite only Admin or Member');
            }

            $company = $inviter->company;
        } else {
            abort(403, 'You cannot send invitations');
        }

        return Invitation::create([
            'company_id' => $company->id,
            'email' => $email,
            'role' => $role,
            'invited_by' => $inviter->id,
            'token' => Str::uuid(),
        ]);
    }

    public function accept(string $token, string $name, string $password): User
    {
        $invite = Invitation::where('token', $token)->firstOrFail();

        $user = User::create([
            'name' => $name,
            'email' => $invite->email,
            'password' => bcrypt($password),
            'company_id' => $invite->company_id,
            'invited_by' => $invite->invited_by,
        ]);

        $user->assignRole($invite->role);

        $invite->update([
            'accepted_at' => now()
        ]);

        return $user;
    }
}
