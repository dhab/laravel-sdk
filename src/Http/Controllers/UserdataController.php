<?php

namespace DreamHack\SDK\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use DreamHack\SDK\Traits\BaseUserdata;
use DreamHack\SDK\Facades\Auth;

/**
 * @DHController(prefix="/userdata")
 */
class UserdataController extends BaseController
{
    use BaseUserdata;

    /**
     * Since email is very user controlled and goes into a LIKE most of the
     * time, we check it a bit extra. Email validator in ID should catch
     * these, but better be safe than sorry.
     *
     * Errors will end up in failed_jobs in ID, notification sent to slack.
     *
     * @param string
     * @return string
     */
    private function verifyEmail($email)
    {
        if (strpos($email, "%") !== false) {
            return "";
        }
        if (strpos($email, "'") !== false) {
            return "";
        }
        return $email;
    }

    /**
     * Display a listing of the resource.
     * @Post("export", as="userdata.export")
     * @Version("1")
     */
    public function export(Request $request)
    {
        // 1. Check permissions
        if (!Auth::user() || !Auth::user()->can('api_internal.userdata')) {
            return response()->json([], 403);
        }
        
        // 2. Get input (need $user->id, $user->email) or change base
        $user = new \stdClass;
        $user->email = $this->verifyEmail($request->input('email'));
        $user->id = $request->input('user_id');

        if (empty($user->id) || empty($user->email)) {
            return response()->json([], 401);
        }

        // 3. Get data and return it.
        return $this->getData($user);
    }

    /**
     * Display a listing of the resource.
     * @Post("delete", as="userdata.delete")
     * @Version("1")
     */
    public function delete(Request $request)
    {
        // 1. Check permissions
        if (!Auth::user() || !Auth::user()->can('api_internal.userdata')) {
            return response()->json([], 403);
        }
        
        // 2. Get input (need $user->id, $user->email) or change base
        $user = new \stdClass;
        $user->email = $this->verifyEmail($request->input('email'));
        $user->id = $request->input('user_id');

        $dryRun = $request->input('dry_run');

        if (empty($user->id) || empty($user->email) || ($dryRun !== true && $dryRun !== false)) {
            return response()->json([], 401);
        }

        // 3. Get data and return it.
        return $this->deleteData($user, $dryRun);
    }
}
