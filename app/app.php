<?php
$loader = require __DIR__ . '/../vendor/autoload.php';

// to use Request object
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// I'll define ObjectStorage as a Silex service provider
use Silex\ServiceProviderInterface;

// Create the app instance
$app = new Silex\Application();

// Config which protocols to activate in this instance
$app['object_storage.protocols'] = array('gdrive', 'posix');

$app['auth.enable'] = true;
$app['auth.keys'] = array('fqw7vTgs99PcpMdm', '9prpb4mRddwJwvgf');

$app['files.max_size']=1000000;

// TODO: separate this in bootstrap
$app->register(
    new Softec\Cloud\ObjectStorageServiceProvider(),
    array(
        'active_protocols' => $app['object_storage.protocols'] // not used anymore, remove ?
    )
);

// transform json input in array
$app->before(
    function (Request $request) {
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }
    }
);

$app->before(
    function (Request $request) use ($app) {
        if ($app['auth.enable']) {
            $authkey = $request->headers->get('Auth-Key');
            if (!in_array($authkey, $app['auth.keys'])) {
                return $app->error('501', "Call not authorized, your key '$authkey' does not seems valid.");
            }
        }
    }
);

// main route
$app->get(
    '/',
    function () use ($app) {
        return $app->redirect('/v1/');
    }
);

// ok: 200 OK, 201 created, 202 accepted, 203 not autoritative request, 204 no content, 205 reset content
// ?: 300 multiple choices, 301 moved permanently, 302 found, 303 see other, 304 not modified
// errors: 500, internal server error, 501 not implemented ,502 bad gateway, 503 Service Unavailable, 504 gateway timeout
$app->get(
    '/error/{code}',
    function ($code) use ($app) {
        return $app->json(array('response'=>'ko'), $code);
    }
)->assert('code', '\d{3}');


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
        return "<h1>Cloud Storage Gateway</h1>\nList of commands:\n<code>files/get</code>\n";
    }
);

// TODO: separate these in src/controllers.php
// TODO: download an item
$v1->get(
    '/files',
    function (Request $request) use ($app) {
        $name = $request->headers->get('Name');

        // TODO: fix exception catching
        try {
            $f = $app['object_storage']($name);
        } catch (\Exception $e) {
            return $app->json(
                array(
                    'response' => 'ko',
                    'name' => $name,
                    'message' => $e->getMessage() . ' [' . $e->getCode() . ']'
                ),
                500
            );
        }

        // TODO: throw an exception specific in preceding load
        // then catch here and return a 404
        // if (!$f->exists()) {
        //    $app->error('404', "File $name is not present");
        // }


        //$stream = function () use ($f) {
        //    echo "!ciao";
        //};

        return $app->stream($f->getItem(), 200);
    }
);

// TODO: Insert a new file or update an existent one
$v1->post(
    '/files',
    function (Request $request) use ($app) {
        $name = $request->headers->get('Name');
        $size = $request->headers->get('Content-Length');
        $size = $request->headers->get('Content-Type');
        $checksum = $request->headers->get('Checksum');

        if (empty($size)) {
            $app->error('500', "Cannot create a file without specifying its size");
        }

        if (empty($name)) {
            $app->error('500', "Cannot create a file, without specifying its name");
        }

        if ($size > $app['files.max_size']) {
            $app->error('500', "Cannot create a file bigger than ".$app['files.max_size']);
        }

        $content = $request->getContent();
        $metadata['size'] = $checksum;
        $metadata['checksum'] = $checksum;

        $f = $app['object_storage']($name);
        $f->createItem($metadata, $content);

        return $app->json(array('response' => 'OK', 'name' => $name), 201);
    }
);

// TODO: create a copy of a file to a new location
$v1->post(
    '/files/copy',
    function (Request $request) use ($app) {
        $name = $request->headers->get('Name');
        if (empty($name)) {
            $app->error('500', "Cannot copy file, without specifying it's name");
        }
        $destination = $request->headers->get('Destination');
        if (empty($destination)) {
            $app->error('500', "Cannot copy '$name', without specifying a valid destination");
        }

        // if destination and source protocol are the same I could implement a copy inside ObjectStorage
        // otherwise I could get the item and the post the item to $destination

        $f = $app['object_storage']($name);
        $d = $app['object_storage']($destination);



        return $app->json(array('response' => 'ok', 'name' => $name, 'destination' => $destination));
    }
);

// TODO: get a list of files in a folder
$v1->get(
    '/files/childrens',
    function (Request $request) use ($app) {
        $name = $request->headers->get('name');
        $f = $app['object_storage']($name);
        return $app->json(array('response' => 'ok', 'name' => $name, 'childrens' => $f->getChildrens()));
    }
);

// TODO: move a file to trash
$v1->post(
    '/files/trash',
    function (Request $request) use ($app) {
        $name = $request->headers->get('name');
        $f = $app['object_storage']($name);

        return $app->json(array('response' => 'ok', 'name' => $name));
    }
);

// TODO: restore a file to trash
$v1->post(
    '/files/restore',
    function (Request $request) use ($app) {
        $name = $request->headers->get('name');
        $f = $app['object_storage']($name);

        return $app->json(array('response' => 'ok', 'name' => $name));
    }
);

// TODO: delete a file skipping the trash
$v1->delete(
    '/files',
    function (Request $request) use ($app) {
        $name = $request->headers->get('name');
        $f = $app['object_storage']($name);

        return $app->json(array('response' => 'ok', 'name' => $name));
    }
);

// TODO: update metadata lastupdated to server time
$v1->post(
    '/files/touch',
    function (Request $request) use ($app) {
        $name = $request->headers->get('name');
        $f = $app['object_storage']($name);

        return $app->json(array('response' => 'ok', 'name' => $name));
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
                $message = "We are sorry, but something went terribly wrong. [code $code]";
        }

        return new Response($message);
    }
);

return $app;
