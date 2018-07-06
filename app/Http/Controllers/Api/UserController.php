<?php

namespace App\Http\Controllers\Api;

use Action;
use App\Http\Controllers\Controller;
use App\Models\UserInviteLink as Link;
// use Spatie\Permission\Models\Permission;
use App\User;
use Hash;
use Illuminate\Http\Request;
use JWTAuth;
use Log;
use Mail;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * @api {GET} /api/user/info UserDetails
     * @apiGroup User
     * @apiSuccess {JSON} Response json with info about user
     * @apiVersion 0.0.1
     * @apiSuccessExample Response:
     *     {
     *         "data": {
     *             "name": "username",
     *             "email": "example@example.com",
     *         }
     *     }
     * @param Request $request
     */
    public function getUserDetails(Request $request)
    {
        $userId = JWTAuth::toUser($request->header('Authorization'))->id;

        $user                   = User::find($userId);
        $user->permissionsArray = $user->getAllPermissions(); // get all permissions from roles and other granted

        // Log::info("Getting info about $user->name. Permissions count " . count($user->permissions));
        return response()->json(['data' => $user]);
    }

    /**
     * @api {POST} /api/user/login Login
     * @apiGroup User
     * @apiVersion 0.0.1
     * @apiParam Request {Illuminate\Http\Request} Request with user credentials
     * @apiParamExample {JSON} Request:
     *     {
     *         "email": "user@example.com",
     *         "password": "1234567890",
     *     }
     * @apiSuccess {JSON} Json json with jwt token
     * @apiSuccessExample {JSON} Response:
     *     {
     *         "token": "some token"
     *     }
     * @param Request $request
     */
    public function login(Request $request)
    {
        $credentials = [
            'email'    => $request->email,
            'password' => $request->password,
        ];
        if (!$token = JWTAuth::attempt($credentials)) {
            $password = $credentials['password'];
            $email    = $credentials['email'];

            return response()->json(['result' => "wrong email $email  or password $password."], 401);
        }
        $userID = JWTAuth::toUser($token)->id;
        Action::do(4, User::find($userID)->name . ' успешно авторизовался', $userID);
        return response()->json(['token' => $token]);
    }

    /**
     * @param Request $request
     */
    public function setUserDetailsFromInviteLink(Request $request)
    {
        $link = Link::where('link', $request->link)->firstOrFail();

        $password = Hash::make($request['password']);

        $departament_id = 0;

        $user           = new User();
        $user->name     = $request->name;
        $user->password = $password;
        $user->email    = $link->email;
        $user->save();

        Link::where('link', $request->link)->delete();
        return redirect('/');
    }

    /**
     * @api {GET} /api/user/roles GetRoles
     * @apiDescription Get user roles
     * @apiGroup User
     * @apiVersion 0.0.1
     */
    public function getRoles(Request $request)
    {
        $roles = Role::all();
        unset($roles[count($roles) - 1]);
        return response()->json($roles, 200);
    }

    public function changePassword(Request $request)
    {
        $newPassword = $request->password;

        $userID         = JWTAuth::toUser($request->header('Authorization'))->id;
        $user           = User::find($userID);
        $user->password = Hash::make($newPassword);
        $user->save();

        return response()->json(['message' => 'success'], 200);
    }

    public function changeUserName(Request $request)
    {
        $userId     = JWTAuth::toUser($request->header('Authorization'))->id;
        $user       = User::find($userId);
        $user->name = $request->name;
        $user->save();

        return response()->json($user, 200);
    }

    /**
     * Приглашает пользователей, высылая им почту с ссылками
     * @param Request $request
     */
    public function inviteUsers(Request $request)
    {
        $userToInvite = $request->email;
        Log::info('User to invite ' . '\'' . $userToInvite . '\'');
        $invitorID = JWTAuth::toUser($request->header('Authorization'))->id;

        $invitor = User::find($invitorID);

        $link        = new Link();
        $link->email = $userToInvite;
        $link->link  = $this->randomString(30);
        $link->save();
        // Mail::to($request->email)->queue(new UserInvite($link));
        $data = [
            'title'   => 'Доступ к Integro',
            'link'    => $link,
            'subject' => 'Приглашение с доступом',
            'to'      => $userToInvite,
        ];

        Mail::send('mail.user_invite', $data, function ($message) use ($userToInvite) {
            $message->from(env('MAIL_USERNAME'), 'Integro');
            $message->sender(env('MAIL_USERNAME'), 'Integro');
            $message->to($userToInvite);
            $message->subject('Доступ к Integro');
            $message->priority(3);
        });
    }

    /**
     * Generate random string
     * @return mixed
     */
    private function randomString($length = 10)
    {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
