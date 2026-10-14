<?php

namespace App\Http\Controllers;

use App\Home\HomePage;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('index', ['home' => HomePage::for($request->user(), session('series'))]);
    }
}
