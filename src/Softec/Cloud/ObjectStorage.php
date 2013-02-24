<?php

namespace Softec\Cloud;

// Any object storage must implements all methods of this interface
// all methods must be public
interface ObjectStorage
{
    public function getItem();

    public function createItem($metadata, $content);

    public function removeItem();

    public function updateItemMetadata($label, $value);
}
