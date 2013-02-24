<?php

namespace Softec\Cloud;

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
