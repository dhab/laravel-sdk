<?php

namespace DreamHack\SDK\Auth;

use DreamHack\SDK\Exceptions\UnauthorizedException;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable;
use Carbon\Carbon;

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

        if ($user && isset($user['service'])) {
            $this->logged_in = true;
            $this->id = $user['service']['id'];
            $this->access = $user['access'] ?? [];
            $this->permissions = $user['access']['permissions'] ?? [];
        }
    }

    public function can($id, array $context = [])
    {
        if (!isset($this->access['permissions'])) {
            return false;
        }

        if (!isset($this->access['permissions'][$id])) {
            return false;
        }
    
        $blocks = $this->access['permissions'][$id];

        // No limitations on the permission, permission granted
        if (count($blocks) === 0) {
            return true;
        }

        foreach ($blocks as $block) {
            $result = true;
            foreach ($block as $parameter => $value) {
                switch ($parameter) {
                    case 'from_time':
                        $ts = Carbon::parse($value);
                        $result = $result && $ts->isPast();
                        break;
                    case 'to_time':
                        $ts = Carbon::parse($value);
                        $result = $result && $ts->isFuture();
                        break;
                    default:
                        // If parameter is missing from the context, permission denied
                        if (!isset($context[$parameter])) {
                            $result = false;
                        } else {
                            $result = $result && ($value == $context[$parameter]);
                        }
                }
            }

            // A matching permission was found, permission granted
            if ($result) {
                return true;
            }
        }

        // No maching permission was found, permission denied
        return false;
    }

    public function hasRelation($relation, $group_id = null)
    {
        if (!isset($this->access['relations'])) {
            return false;
        }
   
        if (!isset($this->access['relations'][$relation])) {
            return false;
        }

        if ( $group_id == null ) { // No group was specified, then any group will suffice
            return true;
        }

        return in_array($group_id, $this->access['relations'][$relation]);
    }

    public function hasRole($role)
    {
        if (!isset($this->access['roles'])) {
            return false;
        }

        if (!isset($this->access['roles'][$role])) {
            return false;
        }

        // A matching role was found
        return true;
    }
}
