<?php

namespace DreamHack\SDK\Traits;

use Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait DhidPermissions
{
    public static function getDhid($key = null)
    {
        $data = json_decode(request()->headers->get('Solid-Authorization'), true);

        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? null;
    }


    public static function requirePermission($permission, $circumstances)
    {
        if (!self::checkPermission($permission, $circumstances)) {
            $this->deny();
        }

        Log::info("requirePermissions: $permission - ACCEPTED", $circumstances);
    }

    public static function deny()
    {
        Log::warning("Access was denied to ".request()->method()." ".request()->url(), ['user_id' => self::getDhid('id')]);
        throw new HttpException(403, 'Unfortunately you are not allowed to do this');
    }

    public static function checkPermission($permission, $circumstances = [])
    {
        $access = self::getDhid('access');
        if (!isset($access['permissions'])) {
            Log::debug("checkPermissions: $permission - FAILED (no permissions key)", $circumstances);
            return false;
        }

        if (!isset($access['permissions'][$permission])) {
            Log::debug("checkPermissions: $permission - FAILED (permission is missing)", $circumstances);
            return false;
        }

        $notFullfilledCircumstances = array_keys(array_diff_assoc($access['permissions'][$permission], $circumstances));
        if (count($notFullfilledCircumstances) !== 0) {
            Log::debug("checkPermissions: $permission - FAILED (not all circumstances fullfilled, ".implode(',', $notFullfilledCircumstances).")", $circumstances);
            return false;
        }
        Log::debug("checkPermissions: $permission - ACCEPTED", $circumstances);
        return true;
    }
}
