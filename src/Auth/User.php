<?php

namespace DreamHack\SDK\Auth;

use DreamHack\SDK\Exceptions\UnauthorizedException;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable;

class User implements Authenticatable
{
    private $logged_in = false;
    private $id = '';
    private $name = '';
    private $email = '';

    public function getAuthIdentifierName() {
        return 'id';
    }
    public function getAuthIdentifier() {
        return $this->id;
    }
    public function getAuthPassword() {
        return '';
    }
    public function getRememberToken() {
        return '';
    }
    public function setRememberToken($value) {}
    public function getRememberTokenName() {
        return '';
    }







    public function getId()
    {
        return $this->id;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function isLoggedIn() {
        return $this->logged_in;
    }
    public function __construct(Request $request, $error = false)
    {
        $user = json_decode($request->headers->get('Solid-Authorization'), true);
        if ($user && $user['id']) {
            $this->logged_in = true;
            $this->id = $user['id'];
            $this->name = $user['name'];
            $this->email = $user['email'];
        }
    }
}
