<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'error' => false,
            'login' => true,
            'users' => User::all()
        ],200);
    }

    /**
     * Login connection to api
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator      =       Validator::make( $request->all(),
        [
            'email'             => 'required|email',
            'password'          => 'required|alpha_num|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'      => true,
                'login'      => false,
                'errorList'  => $validator->errors()
            ],202);
        }
        $data = [
            'email'     => $request->email,
            'password'  => $request->password
        ];

        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
            return response()->json([
                'login'  => true,
                'token'  => $token,
            ], 200);
        } else {
            return response()->json([
                'error'     => true,
                'login'     => false,
                'errorList' => 'il y a une erreur dans vos identifiant'
            ],202);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator      =       Validator::make( $request->all(),
        [
            'lastName'          => 'required|string|max:100',
            'firstName'         => 'required|string|max:100',
            'adress'            => 'required|string|max:200',
            'postalCode'        => 'required|numeric',
            'city'              => 'required|string|max:100',
            'email'             => 'required|email|unique:users',
            'phone'             => 'required|numeric',
            'owner'             => 'required|boolean',
            'tenant'            => 'required|boolean',
            'password'          => 'required|alpha_num|min:6',
            'confirm_password'  => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'      => true,
                'login'      => false,
                'errorList'  => $validator->errors()
            ],202);
        }

        $firstName = strtolower($request->firstName);

        $user = new User();

        $user->lastName     = strtoupper($request->lastName);
        $user->firstName    = ucfirst($firstName);
        $user->adress       = $request->adress;
        $user->postalCode   = $request->postalCode;
        $user->city         = $request->city;
        $user->email        = $request->email;
        $user->phone        = $request->phone;
        $user->admin        = 0; //false
        $user->owner        = $request->owner;
        $user->tenant       = $request->tenant;
        $user->password     = bcrypt($request->password);

        $verify = $user->save();

        if (!$verify) {
            return response()->json([
                'error'      => true,
                'login'      => false,
                'errorList'  => "L'utilisateur n'a pas pu être enregistré"
            ],202);
        } else {
            //create token
            $token = $user->createToken('LaravelAuthApp')->accessToken;

            return response()->json([
                'error'         => false,
                'login'         => true,
                'confirmation'  => "L'utilisateur a été enregistré",
                'token'         => $token,
            ],200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if ($id == 'me') {
            $user = Auth::user();
        } else {
            $user = User::find($id);
        }

        return response()->json([
            'error' => false,
            'login' => true,
            'user'  => $user,
        ],200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator      =       Validator::make( $request->all(),
        [
            'id'                => 'required|exists:App\Models\User,id',
            'lastName'          => 'required|string|max:100',
            'firstName'         => 'required|string|max:100',
            'adress'            => 'required|string|max:200',
            'postalCode'        => 'required|numeric',
            'city'              => 'required|string|max:100',
            'email'             => 'required|email',
            'phone'             => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error'      => true,
                'login'      => false,
                'errorList'  => $validator->errors()
            ],202);
        }

        $firstName = strtolower($request->firstName);

        $user = User::find($request->id);

        $user->lastName     = strtoupper($request->lastName);
        $user->firstName    = ucfirst($firstName);
        $user->adress       = $request->adress;
        $user->postalCode   = $request->postalCode;
        $user->city         = $request->city;
        $user->email        = $request->email;
        $user->phone        = $request->phone;

        $verify = $user->save();

        if (!$verify) {
            return response()->json([
                'error'      => true,
                'login'      => false,
                'errorList'  => "L'utilisateur n'a pas pu être modifié"
            ],202);
        } else {
            return response()->json([
                'error'         => false,
                'login'         => true,
                'confirmation'  => "L'utilisateur a été modifié",
            ],200);
        }

    }

        /**
     * logout and revoke token user of api
     *
     * @return \Illuminate\Http\Response
     */
    public function logout() {
        $user = Auth::user();
        $verify = $user->token()->revoke();

        if ($verify) {
            return response()->json([
                'response'  => true,
                'message'   => 'Sucess logout'
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->id);

        $result = $user->delete();

        if ($result) {
            return response()->json([
                'response'  => true,
                'message'   => "L'utilisateur est supprimé"
            ], 200);
        } else {
            return response()->json([
                'response'  => false,
                'error'     => 'il y a un problème pour la suppression',
            ], 401);
        }
    }
}
