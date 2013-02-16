<?php

// Any object storage must implements all methods of interface
// all methods must be public
interface ObjectStorage {
    public function getItem();
    public function createItem($metadata, $content);
    public function updateItemMetadata($name, $label, $value);
}

// Any API call to Google APIs share this core functions
abstract class GoogleApi {

    private $token;

    public function __construct($token) {
        $this->token = $token;
    }

    public function call($method, $params) {
        echo "Calling google method '$method''";
    }

}

class GoogleDrive extends GoogleApi implements ObjectStorage {
    private $name;

    public function __construct($name, $token) {
        $this->name = $name;
        parent::__construct($token);
    }

    public function getItem() {
        parent::call();
    }

    public function createItem($content) {
        $params['content']=$content;
        parent::call('create', $params);
    }

    public function updateItemMetadata($name) {
        $params['content']=$content;
        parent::call('create', $params);
    }

}
