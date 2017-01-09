<?php

namespace DreamHack\SDK\Auth;

use DreamHack\SDK\Exceptions\UnauthorizedException;
use \Illuminate\Http\Request;

class User {
	private $logged_in = false;
	private $id = '';
	private $name = '';
	private $email = '';
	public function getId() {
		return $this->id;
	}
	public function getName() {
		return $this->name;
	}
	public function getEmail() {
		return $this->email;
	}
	public function __construct(Request $request) {
		$user = json_decode($request->headers->get('Solid-Authorization'), true);
		if($user && $user['id']) {
			$this->logged_in = true;
			$this->id = $user['id'];
			$this->name = $user['name'];
			$this->email = $user['email'];
		} else {
			// throw new UnauthorizedException;
		}
	}
}