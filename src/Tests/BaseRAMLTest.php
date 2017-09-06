<?php
namespace DreamHack\SDK\Tests;

use Manifest;

class BaseRAMLTest extends \TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testManifestErrors()
    {
        $resp = Manifest::getRAMLManifest(\DreamHack\SDK\Http\Controllers\ManifestController::class);

        foreach ($resp->errors() as $error => $desc) {
            // The only accepted error is "db exception"
            $this->assertContains('db exception', $error, $desc);
        }
        if ($resp->hasWarnings()) {
            $this->markTestIncomplete(
                'This test has not been implemented yet.'
            );
        }
    }
}
