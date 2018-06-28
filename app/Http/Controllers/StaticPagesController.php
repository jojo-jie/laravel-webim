<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Auth;

class StaticPagesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'only' => ['home']
        ]);
    }

    public function home()
    {
        $id = Auth::id();
        $user = Auth::user();
        Redis::hmset('user_id:'.$id,['id'=>$id,'name'=>$user->name,'email'=>$user->email,'avatar'=>$user->avatar]);
        $data = [];
        $data['id'] = $id;
        $data['type'] = 'open';
        $str = json_encode($data,true);
        return view('static_pages/home',['message'=>$str]);
    }

    public function help()
    {
        return view('static_pages/help');
    }

    public function about()
    {
        return view('static_pages/about');
    }
}
