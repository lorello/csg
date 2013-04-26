<?php

namespace Softec\Cloud\Tests;

require __DIR__ . '/../../../vendor/autoload.php';

use Silex\WebTestCase;

/**
 * @preserveGlobalState disabled
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 * @runTestsInSeparateProcesses
 * http://www.phpunit.de/manual/3.4/en/appendixes.annotations.html#appendixes.annotations.runTestsInSeparateProcesses
 * @runInSeparateProcess
 */
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
    }

    public function testGetV1Root()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1');
        $this->assertTrue(
            $client->getResponse()->isRedirect('/v1/help')
        );
    }

    public function testGetV1Help()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/v1/help');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Cloud Storage Gateway")'));
    }
     */
    public function testGetV1GetSome()
    {
        $client = $this->createClient();
        $crawler = $client->request(
            'GET', // method
            '/v1/files', // uri
            array(), // parameters
            array(), // files
            array(
                'HTTP_User' => 'non-existent',
                'HTTP_Auth' => 'random-string',
            ),
            null // content
        );
        $this->assertEquals(500, $client->getResponse()->getStatusCode());
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }
}
