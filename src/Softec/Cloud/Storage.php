<?php

// Any object storage must implements all methods of this interface
// all methods must be public
interface ObjectStorage
{
    public function getItem();

    public function createItem($metadata, $content);

    public function removeItem();

    public function updateItemMetadata($label, $value);
}

// Any API call to Google APIs share this core functions
abstract class GoogleApi
{

    // authorization token
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function call($method, $params = array())
    {
        $output = "Calling google method '$method''";
        foreach ($params as $k => $v) {
            $output .= "\n$k = $v\n";
        }

        return $output;
    }
}

// Example of ObjectStorage implementation
class GoogleDrive extends GoogleApi implements ObjectStorage
{
    // object name
    private $name;

    public function __construct($name, $token)
    {
        $this->name = $name;
        parent::__construct($token);
    }

    public function getItem()
    {
        $params['name'] = $this->name;
        parent::call('files.get');
    }

    public function createItem($metadata, $content)
    {
        $params['content'] = $content;
        parent::call('create', $params);
        foreach ($metadata as $k => $v) {
            $this->updateItemMetadata($k, $v);
        }
    }

    public function removeItem()
    {
        $params['name'] = $this->name;
        parent::call('files.remove', $params);
    }

    public function updateItemMetadata($label, $value)
    {
        $params[$label] = $value;
        parent::call('file.patch', $params);
    }
}

// Using a posix filesystem with can simulate object storage
// saving metadata to posix extended attributes
class PosixFile implements ObjectStorage
{
    private $domain;
    private $username;
    private $pathname;
    private $root = '../data';

    public function __construct($domain, $username, $pathname, $root='')
    {
        if (!empty($root)) {
            $this->root = $root;
        }
        $this->domain = $domain;
        $this->username = $username;
        $this->pathname = $pathname;
        $this->fullpathname = implode(
            '/', 
            array(
                $this->root,
                $this->domain,
                $this->username,
                $this->pathname)
            );
    }

    public function createItem($metadata, $content)
    {
        $item_dir = dirname($this->fullpathname);
        if (!is_dir($item_dir)) {
            mkdir($item_dir);
        }
        if(!file_put_contents($this->fullpathname, $content)) {
            throw new Exception('Unable to create '.$this->fullpathname);
        }
        return TRUE;
    }

    public function removeItem()
    {
        if (!file_exists($this->fullpathname)) {
            throw new Exception('Cannot remove non existent item '.$this->fullpathname);
        }
        return TRUE;
    }

    public function getItem()
    {
        if (!file_exists($this->fullpathname)) {
            throw new Exception('Cannot retrieve non existent item '.$this->fullpathname);
        }
        return file_get_contents($this->fullpathname);
    }

    public function updateItemMetadata($label, $value)
    {
        return 'Updating metadata on '.$this->fullpathname.": $label=$value";
    }
}

// At the end I need a Factory to create an object and to get the right object type
class ObjectStorageFactory
{
    const DOMAIN_PATTERN = '([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}';

    const USERNAME_PATTERN = '([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*';

    private static $supported_storage_types = array('gdrive', 'posix');

    private $storage_types = array();

    // create the factory specifying supported storage types
    public function __construct($storage_types = Array())
    {
        foreach ($storage_types as $storage_type) {
            if (in_array($storage_type, $this->supported_storage_types)) {
                $this->storage_types[] = $storage_type;
            }
        }
    }

    // create an object of the factory
    // name must match PROTO://USER@DOMAIN/PATH
    public function load($uri)
    {
        if (empty($uri)) {
            throw new Exception("object name cannot be empty");
        }

        if (!preg_match('/([^:]+):\/\/(.+)/', $uri, $matches)) {
            throw new Exception("syntax error in object name");
        }

        $proto = $matches[1];
        $url = $matches[2];

        $parts = explode('@', $url, 2);
        if ( count($parts)==2 ){
            $username = $parts[0];
            $address = $parts[1];
        } else {
            $username = '';
            $address = $parts[0];
        }

        if (!preg_match('/'.self::USERNAME_PATTERN.'/',$username)) {
            throw new Exception("Username '$username' is not valid");
        }

        if (!preg_match('/('.self::DOMAIN_PATTERN.')(\/.*)/',$address, $matches)) {
            throw new Exception("Username '$username' is not valid");
        }
        $domain = $matches[1];
        $pathname = $matches[2];

        echo "proto: $proto\nusername: $username\ndomain: $domain\npathname: $pathname\n";

        switch($proto) {
            case 'gdrive':
                return new GoogleDrive($domain, $username, $pathname);
                break;

            case 'posix':
                return new PosixFile($domain, $username, $pathname);
                break;

            default:
                throw new Exception("protocol specified '$proto'' is not supported");
        }
    }
}
