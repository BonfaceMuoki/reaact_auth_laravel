<?php
namespace App\Http\Controllers;

use App\Mail\SendTenantEmail;
use App\Models\ReportConsumer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Models\Role;
use Mockery\Exception;
use App\Models\Property;
use App\Models\Organization;
use Validator;
use DB;
use Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Response;

class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['registertenant', 'login', 'register', 'allUsers','registerAccesor']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $image = $request->file('image');


        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->createNewToken($token);
    }
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'register_as' => 'required|in:Super Admin,Report Uploader,Uploaders Accesser,Report Uploader Admin,Valuation Firm Director',
            'full_name' => 'required|string|between:2,100',
            'email' => 'required|string|between:2,100',
            'password' => ['required', Password::min(6)->letters()->mixedCase()->numbers()->symbols()->uncompromised()],
            'password_confirmation' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = [];
        try {
            DB::beginTransaction();

            $organization = null;
            $user=null;
            if (strtolower($request->post('register_as')) == 'report uploader admin') {
                $user = User::create(
                    [
                        'full_name' => $request->full_name,
                        'email' => $request->email,
                        'password' => bcrypt($request->password)
                    ]
                );
                $uploaderadmin_role = Role::where('slug', 'report uploader admin')->first();
                $user->roles()->attach($uploaderadmin_role);
                //check if organization exist
                $validator = Validator::make($request->all(), [
                    'company_name' => 'required|string|between:2,100',
                    'organization_phone' => 'regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:organizations',
                    'directors_vrb' => 'required|string|unique:organizations',
                    'isk_number' => 'required|string|max:100|unique:organizations'
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->errors()->toJson(), 400);
                }
                $company['organization_name'] = $request->post('company_name');
                $company['organization_phone'] = $request->post('organization_phone');
                $company['organization_email'] = $request->post('company_email');
                $company['directors_vrb'] = $request->post('directors_vrb');
                $company['isk_number'] = $request->post('isk_number');
                $organization=Organization::where($company)->first();
                if(!$organization){
                    $company['created_by'] = $user->id;
                    $organization = Organization::create($company);
                    $organization->users()->attach($user);
                }else{
                    $organization->users()->attach($user);
                }
                //check if organization exist
            }else if(strtolower($request->post('register_as')) == 'report uploader'){
              //get admin account user
              if(auth()->user()==null){
                 return response()->json([
                    'code'=>0,
                    'message'=>'Unauthorized access'
                 ],401);
              }else{
                $user = User::create(
                    [
                        'full_name' => $request->full_name,
                        'email' => $request->email,
                        'password' => bcrypt($request->password)
                    ]
                );
                $uploader_role = Role::where('slug', 'report uploader')->first();
                $user->roles()->attach($uploader_role);
                //
                $loggeduser=auth()->user();
                $organizations=$loggeduser->UploaderOrganization()->get();
                $organization=$organizations[0];
                $organization->users()->attach($user);
                //

              }
            }
            else if(strtolower($request->post('register_as')) == 'valuation firm director'){

            }else if(strtolower($request->post('register_as')) == 'super admin'){
                $user = User::create(
                    [
                        'full_name' => $request->full_name,
                        'email' => $request->email,
                        'password' => bcrypt($request->password)
                    ]
                );
                $superadmin_role = Role::where('slug', 'super admin')->first();
                $user->roles()->attach($superadmin_role);
            }
            DB::commit();
            return response()->json([
                'message' => 'Account has been created successfully',
                'user' => $user,
                'roles' => $user->roles()->get()
            ], 201);


        } catch (\Exception $exp) {
            DB::rollBack(); // Tell Laravel, "It's not you, it's me. Please don't persist to DB"
            return response()->json([
                'message' => 'Account has mot been created successfully',
                'error' => $exp
            ], 400);

        }

    }
public function registerAccesor(Request $request){

    $validator = Validator::make($request->all(), [
        'register_as' => 'required|in:Report Accessor,Report Accessor Admin',
        'full_name' => 'required|string|between:2,100',
        'email' => 'required|string|between:2,100',
        'password' => ['required', Password::min(6)->letters()->mixedCase()->numbers()->symbols()->uncompromised()],
        'password_confirmation' => 'required|same:password'
    ]);
    if ($validator->fails()) {
        return response()->json($validator->errors()->toJson(), 400);
    }
    $user = [];
    try {
        DB::beginTransaction();

        $organization = null;
        $organizations = null;
        $user=null;
        if (strtolower($request->post('register_as')) == 'report accessor admin') {
            $user = User::create(
                [
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ]
            );
            $uploaderadmin_role = Role::where('slug', 'report accessor admin')->first();
            $user->roles()->attach($uploaderadmin_role);
            //check if organization exist
            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|between:2,100',
                'organization_phone' => 'regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:report_consumers',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }
            $company['organization_name'] = $request->post('company_name');
            $company['organization_phone'] = $request->post('organization_phone');
            $company['organization_email'] = $request->post('company_email');
            $company['organization_type'] = $request->post('accessor_type');
            $organization=ReportConsumer::where($company)->first();
            if(!$organization){
                $company['created_by'] = $user->id;
                $organization = ReportConsumer::create($company);

                $organization->users()->attach($user);


            }else{
                $organization->users()->attach($user);
            }
            //check if organization exist
        }else if(strtolower($request->post('register_as')) == 'report accessor'){
          //get admin account user
          if(auth()->user()==null){
             return response()->json([
                'code'=>0,
                'message'=>'Unauthorized access'
             ],401);
          }else{
            $user = User::create(
                [
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ]
            );
            //
            $loggeduser=auth()->user();
            $organizations=$loggeduser->AccessorOrganization()->get();
            $organization=$organizations[0];
            $organization->users()->attach($user);
            //

          }
        }
        else if(strtolower($request->post('register_as')) == 'valuation firm director'){

        }
        DB::commit();
        return response()->json([
            'message' => 'Account has been created successfully',
            'organization_d_u' => $user->UploaderOrganization()->get(),
            'organization_d_a' => $user->AccessorOrganization()->get(),
            'user' => $user
        ], 201);


    } catch (\Exception $exp) {
        DB::rollBack(); // Tell Laravel, "It's not you, it's me. Please don't persist to DB"
        return response()->json([
            'message' => 'Account has mot been created successfully',
            'error' => $exp
        ], 400);

    }

}

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(auth()->user());
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userDetails(Request $request)
    {
        $loggeduser = auth()->user();
        try {
            $thisuser = User::where("id", $request->user)->first();

            if ($loggeduser->hasRole("admin")) {

            } else if ($loggeduser->hasRole("owner")) {
                $thisuser->makeHidden(['created_at', 'updated_at', 'nin_number', 'phone_number', 'email_verified_at']);
            }

            $getuser = $thisuser;
            return response()->json(['user' => $getuser, 'user_properties' => $getuser->properties()->get()], 200);
        } catch (Exception $e) {
            return response()->json(
                [
                    'user' => null,
                    'error' => $e->getMessage()
                ]
            );
        }

    }
    protected function createNewToken($token)
    {
        $role=auth()->user()->roles()->first(["id", "name","name as role_name"]);
        $user=auth()->user();
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => array_merge($user->toArray(),$role->toArray()),
            'role' => $role,
            'roles' => $user->roles()->get(["id", "name"]),
            'permissions' => array_merge($user->permissions()->get(["id", "slug as name"])->toArray(),$role->permissions()->get(['id','slug as name'])->toArray())
        ]);
    }

    public function inviteTenant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'invite_completion_registration_url' => 'required|url',
            'invite_completion_login_url' => 'required|url',
            'owner' => 'required',
            'property' => 'required',
            'unit' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(["message" => "Unprocessable data", "errors" => $validator->errors()], 422);
        }
        // If email does not exist

        // If email exists
        $this->sendTenantInviteMail($request->all());
        return response()->json([
            'message' => 'Check your inbox, we have sent a link to reset email.'
        ], Response::HTTP_OK);


    }
    public function sendTenantInviteMail($request)
    {
        $token = $this->generateInviteToken($request);
        $property = Property::where("id", $request['property'])->first();
        Mail::to($request['email'])->send(new SendTenantEmail($token, $request['invite_completion_registration_url'], $request['invite_completion_login_url'], "invite", $request['message'], $property));
    }
    public function generateInviteToken($request)
    {
        $isOtherToken = DB::table('invite_tenants')->where('email', $request['email'])->where('owner', $request['owner'])->where("unit", $request['unit'])->where('completed', false)->first();

        if ($isOtherToken) {
            return $isOtherToken->token;
        }

        $token = Str::random(80);

        $this->storeToken($token, $request);
        return $token;
    }

    public function storeToken($token, $request)
    {
        DB::table('invite_tenants')->insert([
            'email' => $request['email'],
            'owner' => $request['owner'],
            'property' => $request['property'],
            'unit' => $request['unit'],
            'message' => $request['unit'],
            'invite_completion_url' => $request['invite_completion_registration_url'],
            'invite_completion_login_url' => $request['invite_completion_login_url'],
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
    }
    public function allUsers()
    {
        return response()->json(["users" => User::all()]);
    }
}