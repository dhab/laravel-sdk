<?php
namespace DreamHack\SDK\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use DreamHack\SDK\Auth\User;
use DreamHack\SDK\Facades\Fake;

abstract class TestCase extends BaseTestCase
{
    protected function mockUser($attributes = [])
    {
        return new User(json_encode($attributes + [
            'id' => Fake::uuid(),
            'number' => Fake::randomNumber(),
            'name' => "PastaMannen".Fake::randomNumber(4),
            'first_name' => Fake::firstNameMale(),
            'last_name' => Fake::lastName(),
            'email' => Fake::email(),
        ]));
    }
}
