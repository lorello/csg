<?php

require_once __DIR__ . '/../vendor/autoload.php';

require '../src/Softec/Cloud/ObjectStorage.php';

# to use Request object
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Silex\ServiceProviderInterface;

// Create the app instance
$app = new Silex\Application();

// Turn on debugging
// TODO: create index_dev.php for dev environment
$app['debug'] = true;

// Config with protocols to activate in this instance
$app['object_storage.protocols'] = array('gdrive', 'posix');

// TODO: separate this in bootstrap
$app->register(
    new Softec\Cloud\ObjectStorageServiceProvider(),
    array(
        'active_protocols' => $app['object_storage.protocols']
    )
);

$app->before(
    function (Request $request) {
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }
    }
);

// TODO: separate these in src/controllers.php
$app->get(
    '/test',
    function () use ($app) {
        //$name = $app['request']->headers->get('name');
        $name = "posix://lorello@softecspa.it/prova/my.txt";
        $f = $app['object_storage']($name);

        // TODO: throw an exception specific in preceding load
        // then catch here and return a 404
        // if (!$f->exists()) {
        //    $app->error('404', "File $name is not present");
        // }

        return $f->getItem();
    }
);


// Group controllers by type
$v1 = $app['controllers_factory'];
$v1->get(
    '/',
    function () {
        return 'V1 homepage';
    }
);
// Each requesto on $v1 will be prefixed with '/v1'
$app->mount('/v1', $v1);

$app->error(
    function (\Exception $e, $code) use ($app) {
        if ($app['debug']) {
            // use the default error handler of Silex
            return;
        }

        switch ($code) {
            case 404:
                $message = 'The requested page could not be found.';
                break;
            default:
                $message = 'We are sorry, but something went terribly wrong.';
        }

        return new Response($message);
    }
);

$app->run();
