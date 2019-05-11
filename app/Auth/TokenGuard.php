<?php

namespace App\Auth;

use App\User;
use App\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Auth\TokenGuard as Guard;

// use Illuminate\Contracts\Auth\Guard;

class TokenGuard extends Guard
{
    use GuardHelpers;

    protected $user = null;

    protected $request;

    protected $provider;

    /**
     * The name of the query string item from the request containing the API token.
     *
     * @var string
     */
    protected $inputKey;

    /**
     * The user we last attempted to retrieve
     * @var
     */
    protected $lastAttempted;

    /**
     * UserGuard constructor.
     * @param UserProvider $provider
     * @param Request      $request
     * @return void
     */
    public function __construct(UserProvider $provider, Request $request = null)
    {
        $this->request  = $request;
        $this->provider = $provider;
        $this->inputKey = 'Authorization';
    }

    /**
     * Get the currently authenticated user.
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $token = $this->getTokenForRequest();

        if (!empty($token)) {
            $user = $this->provider->retrieveByCredentials(['api_token' => $token]);
        }

        return $this->user = $user;
    }

    /**
     * Rules a user's credentials.
     * @param  array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        $credentials = [$this->storageKey => $credentials[$this->inputKey]];

        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        return $this->hasValidCredentials($user, $credentials);
    }

    /**
     * Determine if the user matches the credentials.
     * @param  mixed $user
     * @param  array $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, $credentials)
    {
        return !is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * Set the current request instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  array  $credentials
     * @param  bool   $remember
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false): bool
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        if ($this->hasValidCredentials($user, $credentials)) {
            return $remember ? $this->setUser($user) : true;
        }

        return false;
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        $user = $this->user();

        // If we have an event dispatcher instance, we can fire off the logout event
        // so any further processing can be done. This allows the developer to be
        // listening for anytime a user signs out of this application manually.
        // $this->clearUserDataFromStorage();

        if (!is_null($this->user)) {
            // $this->cycleRememberToken($user);
            $user->destroyToken();
        }

        if (isset($this->events)) {
            $this->events->dispatch(new Events\Logout($this->name, $user));
        }

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->user = null;

        $this->loggedOut = true;
    }
}
