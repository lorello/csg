<?php
require __DIR__ . '/../vendor/autoload.php';

# TODO: classes autoloading
require __DIR__ . '/../src/Softec/Cloud/ObjectStorageServiceProvider.php';
require __DIR__ . '/../src/Softec/Cloud/ObjectStorage.php';
require __DIR__ . '/../src/Softec/Cloud/PosixObjectStorage.php';
require __DIR__ . '/../src/Softec/Cloud/GoogleDriveObjectStorage.php';

// to use Request object
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// I'll define ObjectStorage as a Silex service provider
use Silex\ServiceProviderInterface;

// Create the app instance
$app = new Silex\Application();

// Config which protocols to activate in this instance
$app['object_storage.protocols'] = array('gdrive', 'posix');

// TODO: separate this in bootstrap
$app->register(
    new Softec\Cloud\ObjectStorageServiceProvider(),
    array(
        'active_protocols' => $app['object_storage.protocols'] // not used anymore, remove ?
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

$app->get(
    '/',
    function () use ($app) {
        return $app->redirect('/v1');
    }
);

// Group controllers by type
$v1 = $app['controllers_factory'];
$v1->get(
    '/',
    function () use ($app) {
        return $app->redirect('/v1/help');
    }
);

$v1->get(
    '/help',
    function () {
        return "List of commands:\nfiles/get";
    }
);

// TODO: separate these in src/controllers.php
$v1->get(
    '/files/get',
    function () use ($app) {
        //$name = $app['request']->headers->get('name');
        $name = "posix://lorello@softecspa.it/prova/my.txt";
        $f = $app['object_storage']($name);
        //xdebug_var_dump($f);

        // TODO: throw an exception specific in preceding load
        // then catch here and return a 404
        // if (!$f->exists()) {
        //    $app->error('404', "File $name is not present");
        // }

        $stream = function () use ($f) {
            echo "!ciao";
        };

        return $app->stream($stream, 200, array('Content-Type' => 'image/png'));
    }
);

$app->post(
    '/files/push',
    function (Request $request) use ($app) {
        $content = $request->getContent();
        $name = $request->headers->get('name');
        if (empty($name)) {
            $app->error('500', "Cannot push file, without specifying it's name");
        }
        $name = "posix://lorello@softecspa.it/prova/my.txt";
        $f = $app['object_storage']($name);
        $f->createItem($metadata, $content);

        return $app->json(array('response' => 'OK', 'name' => $name));
    }
);

// Each request on $v1 will be prefixed with '/v1'
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

return $app;