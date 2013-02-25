<?php

namespace Softec\Cloud\Tests;

require __DIR__ . '/../../../vendor/autoload.php';

use Silex\WebTestCase;

class PosixObjectStorageTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../../../app/app.php';
        $app['debug'] = true;
        $app['exception_handler']->disable();

        return $app;
    }

    public function testFooBar()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Contact us")'));
        $this->assertCount(1, $crawler->filter('form'));
    }
}
