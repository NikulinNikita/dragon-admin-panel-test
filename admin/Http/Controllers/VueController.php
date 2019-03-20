<?php

namespace Admin\Http\Controllers;

use Illuminate\Http\Request;

class VueController extends Controller
{
    public function fireStaticMethod(Request $request, $section, $method, $id = null)
    {
        $section = ucwords(camel_case($section));
        $section = "Admin\\Http\\Sections\\{$section}";
        $data    = $section::$method($id);

        return $data;
    }
}