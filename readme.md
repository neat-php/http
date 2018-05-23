Neat HTTP components
=======================
[![Stable Version](https://poser.pugx.org/neat/http/version)](https://packagist.org/packages/neat/http)
[![Build Status](https://travis-ci.org/neat-php/http.svg?branch=master)](https://travis-ci.org/neat-php/http)

Neat HTTP components provide a clean and expressive API for your application
to accept and return HTTP messages.

Getting started
---------------
To install this package, simply issue [composer](https://getcomposer.org) on the
command line:
```
composer require neat/http
```

Then capture the request, do your thing and send a response:
```php
<?php

$request = Neat\Http\Request::capture();

// ...

$response = new Neat\Http\Response("Here's my response");
$response->send();
```

Reading the request
------------------
The request can be read using simple methods like show below.
```php
// Get ?page= query parameter
$page = $request->query('page');

// Get posted name field
$name = $request->post('name');

// Or just get all posted fields
$post = $request->post();

// Get the request method
$method = $request->method();

// And of course the requested URL
$url = $request->url();
```

URL inspection
--------------
To save you the trouble of disecting and concatenating URL's by hand, the
URL class will lend you a hand:
```php
// Besides asking the request, you can capture the URL from globals directly
$url = Neat\Http\Url::capture();

// Or create a URL manually
$url = new Neat\Http\Url('https://example.com/articles?page=2');

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
$mutation = $url->withPath('/just')->withQuery('tweak=any&part=youlike);
```

File uploads
------------
Uploaded files can be accessed through the request using the ```file``` method.
```php
// Get uploaded file with the name avatar
$file = $request->file('avatar');

// Check the error status
if (!$file->ok()) {
    echo 'Upload error id = ' . $file->error();
}

// Get the file name, size and mime type
$file->name(); // 'selfie.jpg' <-- provided by the client, so consider it unsafe user input
$file->mime(); // 'image/jpeg' <-- provided by the client, so consider it unsafe user input
$file->size(); // 21359

// Move the file from its temporary location
$file->moveTo('destination/path/including/filename.jpg');
```

Cookies
-------
Cookies can be red using the ```cookie``` method.
```php
// Get a cookie value
$value = $request->cookie('preference');
```

Responding
----------
Building responses with Neat HTTP components is real easy:
 
```php
// Create a simple text response
$response = new Neat\Http\Response('Hello world!');

// Add a header
$response = $response->withHeader('Content-Type', 'text/plain');

// Use a non-200 status code
$response = $response->withStatus(403, 'None shall pass!');

// And send it away
$response->send();
```

Certain types of responses (redirects for example) can become quite cumbersome
to create. Therefor you can use static factory methods like ```redirect```: 
```php
// Create a redirection response
$response = Neat\Http\Response::redirect('/');

// Or create a 204 No Content response using the normal constructor
$response = new Neat\Http\Response(null);
```

Using JSON
----------
Neat HTTP messages understand JSON decoding and encoding natively. When
receiving a JSON request, you just use the ```post``` data like you do
with posted form data. To create a JSON response, just pass an object
or array into the Response constructor.
```php
$request = Neat\Http\Request::capture();
$request->post() // the JSON data

$response = new Neat\Http\Response(['json' => 'data']);
$response->send();
```
