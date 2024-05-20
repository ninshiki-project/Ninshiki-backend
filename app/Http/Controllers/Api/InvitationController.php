<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserInvitationPatchRequest;
use App\Models\Invitation;
use App\Models\User;
use App\Notifications\User\Invitation\DeclinedNotification;
use Carbon\Carbon;

class InvitationController extends Controller
{
    /**
     * Get All Invitations
     */
    public function index()
    {
        return response()->json(Invitation::all());
    }

    /**
     * Accept/Decline Invitation
     */
    public function invitation(UserInvitationPatchRequest $request)
    {
        // get Invitation data
        $invitation = Invitation::where('token', $request->token)->firstOrFail();

        if ($request->status === 'declined') {
            $user = User::findOrFail($invitation->invited_by_user);
            // Send email notification to the user who invited,
            //that the invited person has declined accepting the invitation to join
            $user?->notify(new DeclinedNotification($user, $invitation));
            $invitation->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Invitation declined',
            ], 202);
        }

        // create into user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'department' => $invitation?->department,
            'role' => $invitation?->role,
        ])->save();

        $user->points()->create();

        // update the invitation
        $invitation->status = 'accepted';
        $invitation->accepted_at = Carbon::now();
        $invitation->token = null;
        $invitation->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Invitation accepted',
        ], 202);

    }
}