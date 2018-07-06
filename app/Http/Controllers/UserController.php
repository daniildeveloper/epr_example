<?php

namespace App\Http\Controllers;

use App\Models\UserInviteLink as Link;
use App\User;
use Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
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
     * Return user register form
     * @param  Request $request [description]
     * @param  [type]  $link    [description]
     * @return [type]           [description]
     */
    public function renderUserRegisterForm(
        Request $request,
        $link
    ) {
        $linkData = Link::where('link', $link)->firstOrFail();

        return view('auth.register', [
            'email'          => $linkData->email,
            'role_id'        => $linkData->role_id,
            'link'           => $linkData->link,
        ]);
    }
}
