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




    public function user()
    {
        return $this;
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
    public function __construct($user, $error = false)
    {
        if (!is_array($user)) {
            $user = json_decode($user, true);
        }

        if ($user && isset($user['id'])) {
            $this->logged_in = true;
            $this->id = $user['id'];
            $this->name = $user['name'];
            $this->email = $user['email'];
            $this->permissions = $user['permissions'] ?? [];
            $this->access = $user['access'] ?? [];
        }
    }

    public function can($id, array $parameters = [])
    {
        if (!isset($this->access['permissions'])) {
            return false;
        }

        if (!isset($this->access['permissions'][$id])) {
            return false;
        }
            
        $limitations = $this->access['permissions'][$id];

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

    public function hasRelation($relation, $group_id)
    {
        if (!isset($this->access['relations'])) {
            return false;
        }
   
        if (!isset($this->access['relations'][$relation])) {
            return false;
        }

        return in_array($group_id, $this->access['relations'][$relation]);
    }

    public function hasRole($role, array $parameters = [])
    {
        if (!isset($this->access['roles'])) {
            return false;
        }

        if (!isset($this->access['roles'][$id])) {
            return false;
        }
            
        $limitations = $this->access['roles'][$id];

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
}
