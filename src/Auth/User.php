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

    public function getAuthIdentifierName()
    {
        return 'id';
    }
    public function getAuthIdentifier()
    {
        return $this->id;
    }
    public function getAuthPassword()
    {
        return '';
    }
    public function getRememberToken()
    {
        return '';
    }
    public function setRememberToken($value)
    {
    }
    public function getRememberTokenName()
    {
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
    public function isLoggedIn()
    {
        return $this->logged_in;
    }
    public function __construct($solidAuth, $error = false)
    {
        $user = json_decode($solidAuth, true);
        if ($user && $user['id']) {
            $this->logged_in = true;
            $this->id = $user['id'];
            $this->name = $user['name'];
            $this->email = $user['email'];
            $this->permissions = $user['permissions'] ?? [];
        }
    }

    public function hasPermission($id, array $parameters = [])
    {
        foreach ($this->permissions as $permission => $limitations) {
            // Only check permissions that are in the list
            if ($permission !== $id) {
                continue;
            }

            foreach ($limitations as $limitation => $values) {
                // Only check parameters that are provided
                if (!isset($parameters[$limitation])) {
                    continue;
                }

                // If the parameter is not in the value list, permission denied
                if (!in_array($parameters[$limitation], $values)) {
                    return false;
                }
            }

            // A matching permission was found, permission granted
            return true;
        }

        // No matching permission found, permission denied
        return false;
    }
}
