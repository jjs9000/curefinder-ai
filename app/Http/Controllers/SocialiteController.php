<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGithub()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGithubCallback()
    {
        try {
            $user = Socialite::driver('github')->user();
            
            $findUser = User::where('github_id', $user->id)->first();
            
            if ($findUser) {
                Auth::login($findUser);
                return redirect()->intended('/dashboard');
            } else {
                $newUser = User::updateOrCreate(['email' => $user->email], [
                    'name' => $user->name ?? $user->nickname,
                    'github_id' => $user->id,
                    'password' => bcrypt(rand(1, 10000)),
                ]);
                
                Auth::login($newUser);
                return redirect()->intended('/dashboard');
            }
        } catch (Exception $e) {
            return redirect('auth/github');
        }
    }
}
