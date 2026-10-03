<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AddonController extends Controller
{
    public function index()
    {
        abort(410, 'This integration is no longer available.');
    }

    public function install(Request $request)
    {
        abort(410, 'This integration is no longer available.');
    }
}
