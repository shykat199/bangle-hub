<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function checkUpdate()
    {
        abort(410, 'This integration is no longer available.');
    }

    public function processUpdate(Request $request)
    {
        abort(410, 'This integration is no longer available.');
    }
}
