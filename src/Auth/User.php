<?php

namespace DreamHack\SDK\Auth;

use DreamHack\SDK\Exceptions\UnauthorizedException;
use \Illuminate\Http\Request;

class User {
	private $logged_in = false;
	public $id;
	public $name;
	public $email;
	public function __construct(Request $request) {
		$user = json_decode($request->headers->get('Solid-Authorization'), true);
		if($user && $user['id']) {
			$this->id = $user['id'];
			$this->name = $user['name'];
			$this->email = $user['email'];
		} else {
			throw new UnauthorizedException;
		}
	}
}