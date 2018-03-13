<?php
namespace DreamHack\SDK\Tests;

use Manifest;

trait TestPermissions
{
    public function testPermissionDefinitions() {
        $resp = Manifest::getManifest(\DreamHack\SDK\Http\Controllers\ManifestController::class);

        $found = [];
        foreach($resp['endpoints'] as $endpoint) {
            foreach($endpoint['permissions'] ?? [] as $permission) {
                $found[$permission] = array_unique(array_merge(
                  $found[$permission] ?? [],
                  $endpoint['permission_parameters'] ?? []
                ));
            }
        }

        foreach($found as $permission => $parameters) {
          $this->assertArrayHasKey($permission, $resp['permissions']['permissions'], 'Permission documentation is missing in /manifest');

          foreach($parameters as $parameter) {
              $this->assertArrayHasKey($parameter, $resp['permissions']['parameters'], 'Permission parameter documentation is missing in /manifest');
          }
        }

        if (!$found) {
            $this->markTestIncomplete('No permissions was found');
        }
    }
}
