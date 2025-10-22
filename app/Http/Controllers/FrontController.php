<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;


class FrontController extends BaseController
{

    public function __construct()
    {

    }

    public function index()
    {
        return view('application');
    }

}
