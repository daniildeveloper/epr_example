<?php

namespace App\Http\Controllers;

use Action;
use ActionType;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use JWTAuth;
use Log;
use Spatie\Permission\Models\Permission;

class OwnerController extends Controller
{
    /**
     * @apiGroup Owner
     * @apiName GetUserData
     * @apiDescription Get Info about user: roles, permissions
     * @apiVersion 0.0.1
     */
    public function getUsersData()
    {
        $users = User::all();
        // add info about user roots
        foreach ($users as $user) {
            $user->permissionsArray = $user->getAllPermissions();
            $user->role             = $user->role;
        }
        return response()->json($users, 200);
    }

    /**
     * @api {POST} /api/owner/user/grant-permission GrantPermission
     * @apiGroup Owner
     * @apiVersion 0.0.1
     * @apiDescription Grant permission to user
     */
    public function grantPermission(Request $request)
    {
        $user = User::find($request->user_id);

        if (count(Permission::where('name', $request->permission)->get()) === 0) {
            return response()->json(['message' => 'Not find'], 422);
        }

        // if ($user->hasPermissionTo($request->permission)) {
        if (!$this->can($request->user_id, $request->permission)) {
            $user->givePermissionTo($request->permission);
            Log::info('Give permission to ' . $request->permission);
        } else {
            $user->revokePermissionTo($request->permission);
            Log::info('Revoke permission to ' . $request->permission);
        }

        return response()->json(['user' => $user], 200);
    }

    /**
     * @api {GET} /api/owner/user/permissions GetPermissions
     * @apiVerson 0.0.1
     * @apiGroup Owner
     * @apiDescription Get all system registred permissions
     */
    public function getPermissions(Request $request)
    {
        $permissions = Permission::all();

        return response()->json($permissions, 200);
    }

    /**      [description]
     */
    private function can(
        $userId,
        $permission
    ) {
        $user = User::find($userId);

        $user->permissions = $user->getAllPermissions();

        for ($i = 0; $i < count($user->permissions); $i++) {
            $permissionUser = $user->permissions[$i]->name;
            if ($user->permissions[$i]->name === $permission) {
                Log::info("Compare $permissionUser and $permission");
                return true;
            }
        }

        return false;
    }

    /**
     * Revoke user access
     * @param  Request $request [description]
     * @return [type]           [description]
     * @api {POST} /api/owner/user/revoke-access revokeAccess
     * @apiGroup Owner
     * @apiVersion 0.0.1
     * @apiParamExample JSON:
     *   {
     *     "user_id": 1
     *   }
     */
    public function revokeAccess(Request $request)
    {
        $user             = JWTAuth::toUser($request->header('Authorization'));
        $userToDelete     = User::findOrFail($request->user_id);
        $userToDeleteName = $userToDelete->name;
        $userToDelete->delete();
        Action::do(ActionType::where('slug', 'revoke_user_access')->first()->id, 'Уволен пользователь ' . $userToDeleteName, $user->id);
        return response()->json(['message' => 'User deleted'], 200);
    }

    /**
     * @param Request $request
     * @api {POST} /api/owner/user/change-password
     */
    public function setUserPassword(Request $request)
    {
        $userID      = $request->user_id;
        $newPassword = $request->password;

        $user           = User::find($userID);
        $user->password = bcrypt($newPassword);
        $user->save();

        Action::do(ActionType::where('slug', 'change_user_password')->first()->id, "Изменен пароль пользователю $user->name", JWTAuth::toUser($request->header('Authorization'))->id);

        return response()->json(['message' => 'Pasword updated'], 200);
    }
}
