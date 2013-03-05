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
/*
    public function testGetRoot()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Cloud Storage Gateway")'));
    }
    public function testGetV1Root()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Cloud Storage Gateway")'));
    }
 */
    public function testGetV1Help()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/help');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Cloud Storage Gateway")'));
    }
}
