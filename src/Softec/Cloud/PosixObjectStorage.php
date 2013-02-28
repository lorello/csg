<?php

namespace Softec\Cloud;

// Using a posix filesystem which can simulate object storage
// saving metadata to posix extended attributes
class PosixFile implements ObjectStorage
{
    private $domain;

    private $username;

    private $pathname;

    // TODO: passing this as parameter
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
            mkdir($item_dir, 0775, TRUE);
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

    public function isDir() {
        return is_dir($this->fullpathname);
    }

    public function getChildrens() {
        if (!this->isDir())
            throw new \Exception("Can get items only in directories and $this->fullpathname does not seems a directory");
        return array();
    }
}

