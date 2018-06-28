<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        //除此之外均需要登录才可以访问
        $this->middleware('auth', [
            'except' => ['show','create', 'store']
        ]);

        //只允许未登录用户访问
        $this->middleware('guest', [
            'only' => ['create']
        ]);

    }

    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
        //$this->authorize('show',$user);
        try {
            $this->authorize ('show', $user);
            return view('users.show', ['user'=>$user,'message'=>null]);
        } catch (AuthorizationException $authorizationException) {
            abort(403, '对不起，你无权访问！');
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
            'avatar'    => config('image.avatar')[array_rand(config('image.avatar'))]
        ]);
        Auth::login($user);
        return redirect()->route('home');
    }

    public function edit()
    {

    }

    public function update()
    {

    }
}
