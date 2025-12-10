<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HardsellingController extends Controller
{
    public function index()
    {
        return view('hardsellings.index');
    }
}
