<?php
namespace DreamHack\SDK\Tests;

use DreamHack\SDK\Auth\User;
use Manifest;
use Carbon\Carbon;

trait TestPermissions
{
    public function testPermissionDefinitions()
    {
        $resp = Manifest::getManifest(\DreamHack\SDK\Http\Controllers\ManifestController::class);

        $found = [];
        foreach ($resp['endpoints'] as $endpoint) {
            foreach ($endpoint['permissions'] ?? [] as $permission) {
                $found[$permission] = array_unique(array_merge(
                    $found[$permission] ?? [],
                    $endpoint['permission_parameters'] ?? []
                ));
            }
        }

        foreach ($found as $permission => $parameters) {
            $this->assertArrayHasKey($permission, $resp['permissions']['permissions'], 'Permission documentation is missing in /manifest');

            foreach ($parameters as $parameter) {
                $this->assertArrayHasKey($parameter, $resp['permissions']['parameters'], 'Permission parameter documentation is missing in /manifest');
            }
        }

        if (!$found) {
            $this->markTestIncomplete('No permissions was found');
        }
    }

    public function testUserCan()
    {
        $current = Carbon::create(2011, 2, 3, 4, 5, 6, 'Europe/Stockholm');
        $before = $current->copy()->subSecond('1');
        $after = $current->copy()->addSecond('1');

        $permissions = [
            'permission1' => [
                [
                    'event' => 'event a',
                ],
            ],
            'permission2' => [
                [
                    'event' => 'event b',
                ],
                [
                    'event' => 'event c',
                ],
            ],
            'permission3' => [
            ],
            'permission6' => [
                [
                    'to_time' => $current->toIso8601String(),
                    'event' => 'event b',
                ],
            ],
            'permission7' => [
                [
                    'from_time' => $current->toIso8601String(),
                    'event' => 'event b',
                ],
            ]
        ];

        $user = new User([
            'id' => 'userid',
            'name' => 'user name',
            'email' => 'user@email.com',
            'access' => [
                'permissions' => $permissions,
            ],
        ]);

        $this->assertFalse($user->can('permission1'));
        $this->assertTrue($user->can('permission1', ['event' => 'event a']));
        $this->assertFalse($user->can('permission1', ['event' => 'event b']));
        $this->assertFalse($user->can('permission1', ['event' => 'event c']));

        $this->assertFalse($user->can('permission2'));
        $this->assertFalse($user->can('permission2', ['event' => 'event a']));
        $this->assertTrue($user->can('permission2', ['event' => 'event b']));
        $this->assertTrue($user->can('permission2', ['event' => 'event c']));

        $this->assertTrue($user->can('permission3'));
        $this->assertTrue($user->can('permission3', ['event' => 'event a']));
        $this->assertTrue($user->can('permission3', ['event' => 'event b']));
        $this->assertTrue($user->can('permission3', ['event' => 'event c']));

        $this->assertFalse($user->can('permission4'));
        $this->assertFalse($user->can('permission4', ['event' => 'event a']));
        $this->assertFalse($user->can('permission4', ['event' => 'event b']));
        $this->assertFalse($user->can('permission4', ['event' => 'event c']));

        Carbon::setTestNow($before);
        $this->assertFalse($user->can('permission6'));
        $this->assertFalse($user->can('permission6', ['event' => 'event a']));
        $this->assertTrue($user->can('permission6', ['event' => 'event b']));
        $this->assertFalse($user->can('permission6', ['event' => 'event c']));

        $this->assertFalse($user->can('permission7'));
        $this->assertFalse($user->can('permission7', ['event' => 'event a']));
        $this->assertFalse($user->can('permission7', ['event' => 'event b']));
        $this->assertFalse($user->can('permission7', ['event' => 'event c']));

        Carbon::setTestNow($after);
        $this->assertFalse($user->can('permission6'));
        $this->assertFalse($user->can('permission6', ['event' => 'event a']));
        $this->assertFalse($user->can('permission6', ['event' => 'event b']));
        $this->assertFalse($user->can('permission6', ['event' => 'event c']));

        $this->assertFalse($user->can('permission7'));
        $this->assertFalse($user->can('permission7', ['event' => 'event a']));
        $this->assertTrue($user->can('permission7', ['event' => 'event b']));
        $this->assertFalse($user->can('permission7', ['event' => 'event c']));
    }

    public function testUserRelation()
    {
        $user = new User([
            'id' => 'userid',
            'name' => 'user name',
            'email' => 'user@email.com',
            'access' => [
                'relations' => [
                    'indirect' => [
                        'group 1',
                        'group 2',
                        'group 3',
                        'group 4',
                    ],
                    'direct' => [
                        'group 3',
                    ],
                    'TL' => [
                        'group 4',
                    ],
                ],
            ],
        ]);

        $this->assertFalse($user->hasRelation('direct', 'group 1'));
        $this->assertFalse($user->hasRelation('direct', 'group 2'));
        $this->assertTrue($user->hasRelation('direct', 'group 3'));
        $this->assertFalse($user->hasRelation('direct', 'group 4'));

        $this->assertTrue($user->hasRelation('indirect', 'group 1'));
        $this->assertTrue($user->hasRelation('indirect', 'group 2'));
        $this->assertTrue($user->hasRelation('indirect', 'group 3'));
        $this->assertTrue($user->hasRelation('indirect', 'group 4'));

        $this->assertFalse($user->hasRelation('TL', 'group 1'));
        $this->assertFalse($user->hasRelation('TL', 'group 2'));
        $this->assertFalse($user->hasRelation('TL', 'group 3'));
        $this->assertTrue($user->hasRelation('TL', 'group 4'));
    }

    public function testUserRole()
    {
        $user = new User([
            'id' => 'userid',
            'name' => 'user name',
            'email' => 'user@email.com',
            'access' => [
                'roles' => [
                    'asdf123' => 'crew manager',
                    'qwerty' => 'super-admin̈́',
                ],
            ],
        ]);

        $this->assertTrue($user->hasRole('asdf123'));
        $this->assertTrue($user->hasRole('qwerty'));

        $this->assertFalse($user->hasRole('fisk'));
        $this->assertFalse($user->hasRole('pinne'));
    }
}
