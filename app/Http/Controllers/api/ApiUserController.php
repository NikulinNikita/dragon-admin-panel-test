<?php

namespace App\Http\Controllers\api;

use App\Models\Staff;
use App\Models\StaffSession;
use App\Models\Table;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /* @var Staff $user */
        $user = auth()->user();

        return response()->json(['user' => $user, 'avatar' => $user->getAvatarUrlOrBlankAttribute()], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    //TODO: this method only for the test purposes. Delete it after proper implementation
    public function activateSession(Table $table) {
        return response()->json(['success' => true]);
    }

    //TODO: this method only for the test purposes. Delete it after proper implementation
    public function deactivateSession(Table $table) {
        return response()->json(['success' => true]);
    }
}
