<?php

namespace Softec\Cloud;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ObjectStorageServiceProvider implements ServiceProviderInterface
{


    // create the factory specifying supported storage types
    public function register(Application $app)
    {
        define('USERNAME_PATTERN', '([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*)');
        define('DOMAIN_PATTERN', '([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}');

        // service object_storage($name)
        $app['object_storage'] = $app->protect(
            function ($uri) use ($app) {
                if (empty($uri)) {
                    throw new \Exception("object name cannot be empty");
                }

                if (!preg_match('/([^:]+):\/\/(.+)/', $uri, $matches)) {
                    throw new \Exception("syntax error in object name");
                }

                $proto = $matches[1];
                $url = $matches[2];
                unset($matches);

                $parts = explode('@', $url, 2);
                if (count($parts) == 2) {
                    $username = $parts[0];
                    $address = $parts[1];
                } else {
                    $username = '';
                    $address = $parts[0];
                }

                if (!preg_match('/' . USERNAME_PATTERN . '/', $username)) {
                    throw new \Exception("Username '$username' is not valid");
                }

                if (!preg_match('/(' . DOMAIN_PATTERN . ')(\/.*)/', $address, $matches)) {
                    throw new \Exception("Domain/path is not valid");
                }
                $domain = $matches[1];
                $pathname = $matches[4];
                unset($matches);

                if ($app['debug']) {
                    // TODO: add logging functionality
                    echo $proto . '://' . $username . '@' . $domain . $pathname;
                }

                switch ($proto) {
                    case 'gdrive':
                        return new GoogleDrive($domain, $username, $pathname);
                        break;

                    case 'posix':
                        return new PosixFile($domain, $username, $pathname);
                        break;

                    default:
                        throw new \Exception("protocol specified '$proto' is not supported");
                }
            }
        );
    }

    function boot(Application $app)
    {

    }
}
