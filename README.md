csg
=====

Cloud Storage Gateway

A gateway for object-storage (S3, GDrive) and traditional storage protocols (FTP, POSIX Filesystem) able to:

 * decouple storage management from your application: CSG run as a separate process, in an apache or nginx virtualhost, or with the embedded PHP 5.4 webserver, exposing a simple API
 * manage resource accounting for multiple application and multiple customer for applications

Api (v. 1)
------------

All API access is over HTTPS, and accessed from the csg.pagodabox.com domain.
All data is sent and received as JSON.
All timestamps are returned in ISO 8601 format:
<pre>
YYYY-MM-DDTHH:MM:SSZ
</pre>

Client Errors

Sending invalid JSON will result in a 400 Bad Request response.
Sending the wrong type of JSON values will result in a 400 Bad Request response.
Sending invalid fields will result in a 422 Unprocessable Entity response.


HTTP Verbs
Where possible, API strives to use appropriate HTTP verbs for each action.

HEAD
    Can be issued against any resource to get just the HTTP header info.

GET
    Used for retrieving resources.

POST
    Used for creating resources, or performing custom actions.

PATCH
    Used for updating resources with partial JSON data.

PUT
    Used for replacing resources or collections. For PUT requests with no body attribute, be sure to set the Content-Length header to zero.

DELETE
    Used for deleting resources.


<pre>
Verb        URL                     Parameters                              Description
------      -------------------     -----------------------------------     --------------------------------------------
GET         /v1/help

GET         /v1/files               Name                                    download an existing item
POST        /v1/files               Name, Content-Type, Content-Length      upload a new file ore create e new folder
PATCH       /v1/files               Name, Content-Type, Content-Length      update an existing file
GET         /v1/files/childrens     Name                                    list a folder contents
POST        /v1/files/copy          Name, Destination                       create a copy
POST        /v1/files/link          Name, Destination                       create another Name for an existing resource
POST        /v1/files/trash         Name                                    put an object in the trash
DELETE      /v1/files               Name                                    delete an object skipping the trash
POST        /v1/files/touch         Name                                    create an emtpy object

POST        /v1/auth/register       Email                                   register to the service
GET         /v1/auth/confirm        Token                                   confirm registration

GET         /v1/services/list                                                   list of subscribed services
POST        /v1/services/s3             Bucket, AuthToken, AccessKey            add s3 account for a bucket
DELETE      /v1/services/s3/bucket                                              remove access to a S3 bucket
POST        /v1/services/gdrive         Domain, AuthToken                       add Google Drive account for a domain
DELETE      /v1/services/gdrive/domain                                          remove access to a Google Drive domain

</pre>

All request MUST use at least those headers:

 * Auth-Key     the private authorization key
 * User-Id      the user id

Optional parameters

 * Async       (default: no)

CSG URI
----------

<pre>
[ protocol ]://[ user ]@[ domain ][ path ]/[ name ]

protocol = [ s3 | gdrive | ftp | posix ]

user = use nobody if it does not apply

domain = files are always divided in domains
</pre>


Registration
------------

TODO

Technology
----------

Written in PHP, uses Redis as data storage, cache and queue manager.

Current status
--------------
Implementation status is tracked here https://trello.com/board/cloud-storage-gateway/50903634f77ac4470d00249b

Sorry, many items are written in Italian, I'll translate them in english...

Tests
-----
https://travis-ci.org/lorello/csg
