<?php

namespace Tests;

trait Admin
{
    private $authInfo = null;
    /**
     * Login and return token
     *
     * @return \Illuminate\Foundation\Application
     */
    public function authenticate()
    {
        if (is_null($this->authInfo)) {
            $username = config('test.admin_username');
            $password = config('test.admin_password');
            $response = $this->json('POST', '/api/auth/login', [
                'username' => $username,
                'password' => $password,
            ]);

            $response->assertStatus(200);

            $this->authInfo = $response->decodeResponseJson();
        }
        return $this->authInfo;
    }
}
