# Stripe PHP bindings

[![Build Status](https://github.com/stripe/stripe-php/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/stripe/stripe-php/actions?query=branch%3Amaster)
[![Latest Stable Version](https://poser.pugx.org/stripe/stripe-php/v/stable.svg)](https://packagist.org/packages/stripe/stripe-php)
[![Total Downloads](https://poser.pugx.org/stripe/stripe-php/downloads.svg)](https://packagist.org/packages/stripe/stripe-php)
[![License](https://poser.pugx.org/stripe/stripe-php/license.svg)](https://packagist.org/packages/stripe/stripe-php)

The Stripe PHP library provides convenient access to the Stripe API from
applications written in the PHP language. It includes a pre-defined set of
classes for API resources that initialize themselves dynamically from API
responses which makes it compatible with a wide range of versions of the Stripe
API.

## Requirements

PHP 5.6.0 and later.

## Composer

You can install the bindings via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require stripe/stripe-php
```

To use the bindings, use Composer's [autoload](https://getcomposer.org/doc/01-basic-usage.md#autoloading):

```php
require_once 'vendor/autoload.php';
```

## Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/stripe/stripe-php/releases). Then, to use the bindings, include the `init.php` file.

```php
require_once '/path/to/stripe-php/init.php';
```

## Dependencies

The bindings require the following extensions in order to work properly:

-   [`curl`](https://secure.php.net/manual/en/book.curl.php), although you can use your own non-cURL client if you prefer
-   [`json`](https://secure.php.net/manual/en/book.json.php)
-   [`mbstring`](https://secure.php.net/manual/en/book.mbstring.php) (Multibyte String)

If you use Composer, these dependencies should be handled automatically. If you install manually, you'll want to make sure that these extensions are available.

## Getting Started

Simple usage looks like:

```php
$stripe = new \Stripe\StripeClient('YOUR_STRIPE_SECRET_KEY');
$customer = $stripe->customers->create([
    'description' => 'example customer',
    'email' => 'email@example.com',
    'payment_method' => 'pm_card_visa',
]);
echo $customer;
```

### Client/service patterns vs legacy patterns

You can continue to use the legacy integration patterns used prior to version [7.33.0](https://github.com/stripe/stripe-php/blob/master/CHANGELOG.md#7330---2020-05-14). Review the [migration guide](https://github.com/stripe/stripe-php/wiki/Migration-to-StripeClient-and-services-in-7.33.0) for the backwards-compatible client/services pattern changes.

## Documentation

See the [PHP API docs](https://stripe.com/docs/api/?lang=php#intro).

## Legacy Version Support

### PHP 5.4 & 5.5

If you are using PHP 5.4 or 5.5, you should consider upgrading your environment as those versions have been past end of life since September 2015 and July 2016 respectively.
Otherwise, you can still use Stripe by downloading stripe-php v6.43.1 ([zip](https://github.com/stripe/stripe-php/archive/v6.43.1.zip), [tar.gz](https://github.com/stripe/stripe-php/archive/6.43.1.tar.gz)) from our [releases page](https://github.com/stripe/stripe-php/releases). This version will work but might not support recent features we added since the version was released and upgrading PHP is the best course of action.

### PHP 5.3

If you are using PHP 5.3, you should upgrade your environment as this version has been past end of life since August 2014.
Otherwise, you can download v5.9.2 ([zip](https://github.com/stripe/stripe-php/archive/v5.9.2.zip), [tar.gz](https://github.com/stripe/stripe-php/archive/v5.9.2.tar.gz)) from our [releases page](https://github.com/stripe/stripe-php/releases). This version will continue to work with new versions of the Stripe API for all common uses.

## Custom Request Timeouts

> **Note**
> We do not recommend decreasing the timeout for non-read-only calls (e.g. charge creation), since even if you locally timeout, the request on Stripe's side can still complete. If you are decreasing timeouts on these calls, make sure to use [idempotency tokens](https://stripe.com/docs/api/?lang=php#idempotent_requests) to avoid executing the same transaction twice as a result of timeout retry logic.

To modify request timeouts (connect or total, in seconds) you'll need to tell the API client to use a CurlClient other than its default. You'll set the timeouts in that CurlClient.

```php
// set up your tweaked Curl client
$curl = new \Stripe\HttpClient\CurlClient();
$curl->setTimeout(10); // default is \Stripe\HttpClient\CurlClient::DEFAULT_TIMEOUT
$curl->setConnectTimeout(5); // default is \Stripe\HttpClient\CurlClient::DEFAULT_CONNECT_TIMEOUT

echo $curl->getTimeout(); // 10
echo $curl->getConnectTimeout(); // 5

// tell Stripe to use the tweaked client
\Stripe\ApiRequestor::setHttpClient($curl);

// use the Stripe API client as you normally would
```

## Custom cURL Options (e.g. proxies)

Need to set a proxy for your requests? Pass in the requisite `CURLOPT_*` array to the CurlClient constructor, using the same syntax as `curl_stopt_array()`. This will set the default cURL options for each HTTP request made by the SDK, though many more common options (e.g. timeouts; see above on how to set those) will be overridden by the client even if set here.

```php
// set up your tweaked Curl client
$curl = new \Stripe\HttpClient\CurlClient([CURLOPT_PROXY => 'proxy.local:80']);
// tell Stripe to use the tweaked client
\Stripe\ApiRequestor::setHttpClient($curl);
```

Alternately, a callable can be passed to the CurlClient constructor that returns the above array based on request inputs. See `testDefaultOptions()` in `tests/CurlClientTest.php` for an example of this behavior. Note that the callable is called at the beginning of every API request, before the request is sent.

### Configuring a Logger

The library does minimal logging, but it can be configured
with a [`PSR-3` compatible logger][psr3] so that messages
end up there instead of `error_log`:

```php
\Stripe\Stripe::setLogger($logger);
```

### Accessing response data

You can access the data from the last API response on any object via `getLastResponse()`.

```php
$customer = $stripe->customers->create([
    'description' => 'example customer',
]);
echo $customer->getLastResponse()->headers['Request-Id'];
```

### SSL / TLS compatibility issues

Stripe's API now requires that [all connections use TLS 1.2](https://stripe.com/blog/upgrading-tls). Some systems (most notably some older CentOS and RHEL versions) are capable of using TLS 1.2 but will use TLS 1.0 or 1.1 by default. In this case, you'd get an `invalid_request_error` with the following error message: "Stripe no longer supports API requests made with TLS 1.0. Please initiate HTTPS connections with TLS 1.2 or later. You can learn more about this at [https://stripe.com/blog/upgrading-tls](https://stripe.com/blog/upgrading-tls).".

The recommended course of action is to [upgrade your cURL and OpenSSL packages](https://support.stripe.com/questions/how-do-i-upgrade-my-stripe-integration-from-tls-1-0-to-tls-1-2#php) so that TLS 1.2 is used by default, but if that is not possible, you might be able to solve the issue by setting the `CURLOPT_SSLVERSION` option to either `CURL_SSLVERSION_TLSv1` or `CURL_SSLVERSION_TLSv1_2`:

```php
$curl = new \Stripe\HttpClient\CurlClient([CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1]);
\Stripe\ApiRequestor::setHttpClient($curl);
```

### Per-request Configuration

For apps that need to use multiple keys during the lifetime of a process, like
one that uses [Stripe Connect][connect], it's also possible to set a
per-request key and/or account:

```php
$customers = $stripe->customers->all([],[
    'api_key' => 'sk_test_...',
    'stripe_account' => 'acct_...'
]);

$stripe->customers->retrieve('cus_123456789', [], [
    'api_key' => 'sk_test_...',
    'stripe_account' => 'acct_...'
]);
```

### Configuring CA Bundles

By default, the library will use its own internal bundle of known CA
certificates, but it's possible to configure your own:

```php
\Stripe\Stripe::setCABundlePath("path/to/ca/bundle");
```

### Configuring Automatic Retries

The library can be configured to automatically retry requests that fail due to
an intermittent network problem:

```php
\Stripe\Stripe::setMaxNetworkRetries(2);
```

[Idempotency keys][idempotency-keys] are added to requests to guarantee that
retries are safe.

### Telemetry

By default, the library sends telemetry to Stripe regarding request latency and feature usage. These
numbers help Stripe improve the overall latency of its API for all users, and
improve popular features.

You can disable this behavior if you prefer:

```php
\Stripe\Stripe::setEnableTelemetry(false);
```

### Public Preview SDKs

Stripe has features in the [public preview phase](https://docs.stripe.com/release-phases) that can be accessed via versions of this package that have the `-beta.X` suffix like `12.2.0-beta.2`.
We would love for you to try these as we incrementally release new features and improve them based on your feedback.

The public preview SDKs are a different version of the same package as the stable SDKs. These versions are appended with `-beta.X` such as `15.0.0-beta.1`. To install, choose the version that includes support for the preview feature you are interested in by reviewing the [releases page](https://github.com/stripe/stripe-dotnet/releases/) and then use it in the `composer require` command:

```bash
composer require stripe/stripe-php:v<replace-with-the-version-of-your-choice>
```

> **Note**
> There can be breaking changes between two versions of the public preview SDKs without a bump in the major version. Therefore we recommend pinning the package version to a specific version in your composer.json file. This way you can install the same version each time without breaking changes unless you are intentionally looking for the latest version of the public preview SDK.

Some preview features require a name and version to be set in the `Stripe-Version` header like `feature_beta=v3`. If the preview feature you are interested in has this requirement, use the function `addBetaVersion` (available only in the public preview SDKs):

```php
Stripe::addBetaVersion("feature_beta", "v3");
```

### Custom requests

If you would like to send a request to an undocumented API (for example you are in a private beta), or if you prefer to bypass the method definitions in the library and specify your request details directly, you can use the `rawRequest` method on the StripeClient.

```php
$stripe = new \Stripe\StripeClient('sk_test_xyz');
$response = $stripe->rawRequest('post', '/v1/beta_endpoint', [
  "caveat": "emptor"
], [
  "stripe_version" => "2022-11_15",
]);
// $response->body is a string, you can call $stripe->deserialize to get a \Stripe\StripeObject.
$obj = $stripe->deserialize($response->body);

// For GET requests, the params argument must be null, and you should write the query string explicitly.
$get_response = $stripe->rawRequest('get', '/v1/beta_endpoint?caveat=emptor', null, [
  "stripe_version" => "2022-11_15",
]);
```

## Support

New features and bug fixes are released on the latest major version of the Stripe PHP library. If you are on an older major version, we recommend that you upgrade to the latest in order to use the new features and bug fixes including those for security vulnerabilities. Older major versions of the package will continue to be available for use, but will not be receiving any updates.

## Development

[Contribution guidelines for this project](CONTRIBUTING.md)

We use [just](https://github.com/casey/just) for conveniently running development tasks. You can use them directly, or copy the commands out of the `justfile`. To our help docs, run `just`.

To get started, install [Composer][composer]. For example, on Mac OS:

```bash
brew install composer
```

Install dependencies:

```bash
just install
# or: composer install
```

The test suite depends on [stripe-mock], so make sure to fetch and run it from a
background terminal ([stripe-mock's README][stripe-mock] also contains
instructions for installing via Homebrew and other methods):

```bash
go install github.com/stripe/stripe-mock@latest
stripe-mock
```

Install dependencies as mentioned above (which will resolve [PHPUnit](http://packagist.org/packages/phpunit/phpunit)), then you can run the test suite:

```bash
just test
# or: ./vendor/bin/phpunit
```

Or to run an individual test file:

```bash
just test tests/Stripe/UtilTest.php
# or: ./vendor/bin/phpunit tests/Stripe/UtilTest.php
```

Update bundled CA certificates from the [Mozilla cURL release][curl]:

```bash
./update_certs.php
```

The library uses [PHP CS Fixer][php-cs-fixer] for code formatting. Code must be formatted before PRs are submitted, otherwise CI will fail. Run the formatter with:

```bash
just format
# or: ./vendor/bin/php-cs-fixer fix -v .
```

## Attention plugin developers

Are you writing a plugin that integrates Stripe and embeds our library? Then please use the `setAppInfo` function to identify your plugin. For example:

```php
\Stripe\Stripe::setAppInfo("MyAwesomePlugin", "1.2.34", "https://myawesomeplugin.info");
```

The method should be called once, before any request is sent to the API. The second and third parameters are optional.

### SSL / TLS configuration option

See the "SSL / TLS compatibility issues" paragraph above for full context. If you want to ensure that your plugin can be used on all systems, you should add a configuration option to let your users choose between different values for `CURLOPT_SSLVERSION`: none (default), `CURL_SSLVERSION_TLSv1` and `CURL_SSLVERSION_TLSv1_2`.

[composer]: https://getcomposer.org/
[connect]: https://stripe.com/connect
[curl]: http://curl.haxx.se/docs/caextract.html
[idempotency-keys]: https://stripe.com/docs/api/?lang=php#idempotent_requests
[php-cs-fixer]: https://github.com/FriendsOfPHP/PHP-CS-Fixer
[psr3]: http://www.php-fig.org/psr/psr-3/
[stripe-mock]: https://github.com/stripe/stripe-mock
