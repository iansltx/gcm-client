# gcm-client

[![Author](http://img.shields.io/badge/author-@iansltx-blue.svg?style=flat-square)](https://twitter.com/iansltx)
[![Latest Version](https://img.shields.io/github/release/iansltx/gcm-client.svg?style=flat-square)](https://github.com/iansltx/gcm-client/releases)
[![Software License](https://img.shields.io/badge/license-BSD-brightgreen.svg?style=flat-square)](LICENSE.md)

gcm-client is a PHP library for interacting with Google Cloud Messaging for Android's HTTP API. It can be used to
send messages to both Android Registration IDs and Notification Keys (for user-based multi-device messaging). It can
also create and manipulate Notification Keys.

This library should conform to PSRs 1, 2, and 4, and requires PHP 5.4 or newer.

## Install

Via Composer

``` bash
$ composer require iansltx/gcm-client
```

If you don't want Composer, you may download the source zipball directly from GitHub and load it using a PSR-4 compliant
autoloader. If you don't have such an autoloader, require `autoload.php` to get one that works for this library.

## Usage

```php
<?php

require "vendor/autoload.php";

$client = new iansltx\GCMClient\Client(YOUR_GCM_API_KEY);
$message = new iansltx\GCMClient\Message(['title' => 'Notification', 'message' => 'Hello World!']);

// send directly to one or more Registration IDs
$regIdResult = $client->sendToRegIds($message, ['regId1', 'regId2']);

// create a Notification Key for user-based messaging and send to that
$nkClient = $client->withProjectId('myProjectId'); // a project ID is required for notification key manipulation
$key = $nkClient->createNotificationKey('myUniqueKeyName', ['regId1', 'regId2']);
$nkClient->addToNotificationKey($key, ['regId3']); // returns the notification key
$nkClient->removeFromNotificationKey($key, ['regId1']); // returns the notification key
$nKeyResult = $client->sendToNotificationKey($key, $message); // could use $nkClient to send as well
```

More examples coming soon; in the mean time, take a look at the docblocks of Client and Message for more information.

## Testing

``` bash
$ composer test
```

PHPUnit is currently used for testing.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email iansltx@gmail.com instead of using the issue tracker.

## Credits

- [Ian Littman](https://github.com/iansltx)
- [All Contributors](../../contributors)

## License

This library is BSD 2-clause licensed. Please see [License File](LICENSE.md) for more information.
