<?php

namespace DreamHack\SDK\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use App\User;
use Auth;

class SocialiteController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('web');
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('dhid')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
		if (isset($_GET['error'])) {
			return response()->view('auth.error', $_GET, 400);
		}

        try {
    		$_SESSION['dhid'] = Socialite::driver('dhid')->user();
	    	$authUser = $this->findOrCreateUser($_SESSION['dhid']);
    		Auth::login($authUser, true);

            return redirect($this->redirectTo);
        } catch (InvalidStateException $ex) {
            // User returned with an old state.. do a new auth
            return Socialite::driver('dhid')->redirect();
        }
    }

    /**
     * Return user if exists; create and return if doesn't
     *
     * @param $dhidUser
     * @return User
     */
    private function findOrCreateUser($dhidUser)
    {
		if (!$user = User::where('dhid', $dhidUser->id)->first()) // Find based on UUID
            if (!$user = User::where('email', $dhidUser->email)->first()) // Find based on email
                $user = New User([ // Create a new user
                    'password' => '',
                ]);

        // Update attributes
        $user->name = 
            $dhidUser->name . 
            ($dhidUser->user['name']?' ('.$dhidUser->user['name'].')':'');
        $user->email = $dhidUser->email;
        $user->dhid = $dhidUser->id;

        // Save only stores if something has changed
        $user->save();

        return $user;
    }
}
