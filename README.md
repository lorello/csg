csg
===

Cloud Storage Gateway

A gateway for object-storage (S3, GDrive) and traditional storage protocols (FTP, POSIX Filesystem) able to:
- decouple storage management from your application: CSG run as a separate process, in an apache or nginx virtualhost, or with the embedded PHP 5.4 webserver, exposing a simple API
- manage resource accounting for multiple application and multiple customer for applications

Api (v. 1)
----------

Http verb   URL                     Parameters                

GET         /v1/help
GET         /v1/files               Name
POST        /v1/files               Name, Content-Type, Content-Length
GET         /v1/files/childrens     Name
POST        /v1/files/copy          Name, Destination
POST        /v1/files/trash         Name
DELETE      /v1/files               Name
POST        /v1/files/touch         Name

All request MUST use at least those header:
Auth-Key    the private authorization key

Optional parameters
Async       (default: no)

CSG URI
-------

[protocol]://[user]@[domain][path]/[name]

protocol = [ s3 | gdrive | ftp | posix ]

Registration
------------
TODO

Technology
----------
Written in PHP, uses Redis as data storage, cache and queue manager.

