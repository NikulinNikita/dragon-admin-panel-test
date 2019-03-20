<?php
namespace Admin\Http\Controllers\User;


use Admin\Http\Controllers\Controller;
use App\Models\User;

class AuthController extends Controller
{
    public function manualLogin($id)
    {
        $user = User::findOrFail($id);

        if ($user->options) {
            $user->options->adminAsUser = true;
        } else {
            $user->options = (object)['adminAsUser' => true];
        }

        $user->save();
        
        $url = url(env('FRONT_HOST') . '/user/auth/' . $id);
        
        return redirect($url);    
    }
}