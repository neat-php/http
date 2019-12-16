Neat HTTP components
====================
[![Stable Version](https://poser.pugx.org/neat/http/version)](https://packagist.org/packages/neat/http)
[![Build Status](https://travis-ci.org/neat-php/http.svg?branch=master)](https://travis-ci.org/neat-php/http)

Neat HTTP components provide a clean and expressive API for your application
to access HTTP messages.

Requirements
------------
To use Neat HTTP components you will need
- PHP 7.0 or newer
- a [PSR-7 HTTP message implementation](https://packagist.org/providers/psr/http-message-implementation)

To send and receive messages, we suggest using
the [neat/http-client](https://github.com/neat-php/http-client)
and [neat/http-server](https://github.com/neat-php/http-server) packages.

Getting started
---------------
To install this package, simply issue [composer](https://getcomposer.org) on the
command line:
```
composer require neat/http
```

Reading the request
-------------------
The request can be read using simple methods like shown below.
```php
<?php /** @var Neat\Http\ServerRequest $request */

// Get ?page= query parameter
$page = $request->query('page');

// Get posted name field
$name = $request->post('name');

// Or just get all posted fields
$post = $request->post();

// Who doesn't want a cookie
$preference = $request->cookie('preference');

// Get the request method
$method = $request->method();

// And of course the requested URL
$url = $request->url();
```

URL inspection
--------------
To save you the trouble of dissecting and concatenating URL's by hand, the
URL class will lend you a hand:
```php
<?php /** @var Neat\Http\Url $url */

// You can easily print the URL because it converts to a string when needed
echo $url;

// Or get parts of the url separately
$url->scheme();   // 'https'
$url->username(); // ''
$url->password(); // ''
$url->host();     // 'example.com'
$url->port();     // null
$url->path();     // '/articles'
$url->query();    // 'page=2'
$url->fragment(); // ''

// Do you want to create a slightly different URL based on this one?
$mutation = $url->withPath('/just')->withQuery('tweak=any&part=youlike');
```

File uploads
------------
Uploaded files can be accessed through the request using the ```file``` method.
```php
<?php /** @var Neat\Http\ServerRequest $request */

// Get uploaded file with the name avatar
$file = $request->files('avatar');

// Check the error status
if (!$file->ok()) {
    echo 'Upload error id = ' . $file->error();
}

// Get the file name, size and mime type
$file->clientName(); // 'selfie.jpg' <-- provided by the client, so consider it unsafe user input
$file->clientType(); // 'image/jpeg' <-- provided by the client, so consider it unsafe user input
$file->size(); // 21359

// Move the file from its temporary location
$file->moveTo('destination/path/including/filename.jpg');
```
