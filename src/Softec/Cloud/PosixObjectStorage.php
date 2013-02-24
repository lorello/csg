<?php

namespace Softec\Cloud;

// Using a posix filesystem with can simulate object storage
// saving metadata to posix extended attributes
class PosixFile implements ObjectStorage
{
    private $domain;
    private $username;
    private $pathname;
    private $root = '../data';

    public function __construct($domain, $username, $pathname, $root = '')
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
                $this->pathname
            )
        );
    }

    public function createItem($metadata, $content)
    {
        $item_dir = dirname($this->fullpathname);
        if (!is_dir($item_dir)) {
            mkdir($item_dir);
        }
        if (!file_put_contents($this->fullpathname, $content)) {
            throw new \Exception('Unable to create ' . $this->fullpathname);
        }

        return true;
    }

    public function removeItem()
    {
        if (!file_exists($this->fullpathname)) {
            throw new \Exception('Cannot remove non existent item ' . $this->fullpathname);
        }

        return true;
    }

    public function getItem()
    {
        if (!file_exists($this->fullpathname)) {
            throw new \Exception('Cannot retrieve non existent item ' . $this->fullpathname);
        }

        return file_get_contents($this->fullpathname);
    }

    public function updateItemMetadata($label, $value)
    {
        return 'Updating metadata on ' . $this->fullpathname . ": $label=$value";
    }
}

