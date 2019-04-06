# gcm-client

[![Author](http://img.shields.io/badge/author-@iansltx-blue.svg?style=flat-square)](https://twitter.com/iansltx)
[![Latest Version](https://img.shields.io/github/release/iansltx/gcm-client.svg?style=flat-square)](https://github.com/iansltx/gcm-client/releases)
[![Software License](https://img.shields.io/badge/license-BSD-brightgreen.svg?style=flat-square)](LICENSE.md)

gcm-client is a PHP library for interacting with Google Cloud Messaging for Android's HTTP API. It can be used to
send messages to both Android Registration IDs and Notification Keys (for user-based multi-device messaging). It can
also create and manipulate Notification Keys.

This library should conform to PSRs 1, 2, and 4, and requires PHP 7.2 or newer.

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
$singleRegIdResult = $client->sendToRegIds($message, 'regId3');

// create a Notification Key for user-based messaging and send to that
$nkClient = $client->withProjectId('myProjectId'); // a project ID is required for notification key manipulation
$key = $nkClient->createNotificationKey('myUniqueKeyName', ['regId1', 'regId2']);
$nkClient->addToNotificationKey($key, ['regId3'], 'myUniqueKeyName'); // returns the notification key
$nkClient->removeFromNotificationKey($key, ['regId1'], 'myUniqueKeyName'); // returns the notification key
$nKeyResult = $client->sendToNotificationKey($key, $message); // could use $nkClient to send as well
```

Take a look at the docblocks of Client and Message for more information.

Google changed the preferred location of the notification key field for their message-sending endpoint awhile back. This
library has been updated to the new, non-deprecated, location. Additionally, it looks like they're requiring names
when updating notification keys now; this used to be optional, hence its placement as the last parameter in the calls
above.

As of v1.0, this library uses Google's non-deprecated FCM legacy endpoints (their words, not mind), rather than the
old GCM endpoints that'll go offline on or after May 29th, 2019. So, despite the name, this library can be used with
Firebase Cloud Messaging after May 2019.

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
