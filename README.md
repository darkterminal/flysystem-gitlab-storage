# Flysystem Gitlab storage

A Gitlab Storage filesystem for [Flysystem](https://flysystem.thephpleague.com/docs/).

[![Latest Version](https://img.shields.io/packagist/v/royvoetman/flysystem-gitlab-storage.svg?style=flat-square)](https://packagist.org/packages/royvoetman/flysystem-gitlab-storage)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/royvoetman/flysystem-gitlab-storage.svg?style=flat-square)](https://packagist.org/packages/royvoetman/flysystem-gitlab-storage)

This package contains a Flysystem adapter for Gitlab. Under the hood, Gitlab's [Repository (files) API](https://docs.gitlab.com/ee/api/repository_files.html) v4 is used.

## Installation

```bash
composer require royvoetman/flysystem-gitlab-storage
```

## Integrations

* Laravel - https://github.com/royvoetman/laravel-gitlab-storage

## Usage
```php
// Create a Gitlab Client to talk with the API
$client = new Client('personal-access-token', 'project-id', 'branch', 'base-url');
   
// Create the Adapter that implentents Flysystems AdapterInterface
$adapter = new GitlabAdapter($client));

// Create FileSystem
$filesystem = new Filesystem($adapter);

// write a file
$filesystem->write('path/to/file.txt', 'contents');

// update a file
$filesystem->update('path/to/file.txt', 'new contents');

// read a file
$contents = $filesystem->read('path/to/file.txt');

// check if a file exists
$exists = $filesystem->has('path/to/file.txt');

// delete a file
$filesystem->delete('path/to/file.txt');

// rename a file
$filesystem->rename('filename.txt', 'newname.txt');

// copy a file
$filesystem->copy('filename.txt', 'duplicate.txt');

// delete a directory
$filesystem->deleteDir('path/to/directory');

// see http://flysystem.thephpleague.com/api/ for full list of available functionality
```

### Access token (required for private projects)
Gitlab supports server side API authentication with Personal Access tokens

For more information on how to create your own Personal Access token: [Gitlab Docs](https://docs.gitlab.com/ee/user/profile/personal_access_tokens.html)

### Project ID
Every project in Gitlab has its own Project ID. It can be found at to top of the frontpage of your repository. [See](https://stackoverflow.com/questions/39559689/where-do-i-find-the-project-id-for-the-gitlab-api#answer-53126068)

### Base URL
This will be the URL where you host your gitlab server (e.g. https://gitlab.com)

## Debugging
In some cases, the need arises to debug the Http Messeages returned by the Gitlab API. For this need, the adapter has a built-in `debug mode`. When this mode is enabled, it will tell the adapter to prevent catching the `GuzzleHttp\Exception\GuzzleException`.

To check for the current state of de adaptor the `isDebugEnabled` method can be called on the adapter. 

Enabling debug mode can be done with either of the following two options:

### At instantiation
```php
$adapter = new GitlabAdapter($client, '', true); // Third parameter defines debug mode
```  

### At runtime
```php
$adapter->setDebug(true);
```  

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Contributions are **welcome** and will be fully **credited**. We accept contributions via Pull Requests on [Github](https://github.com/RoyVoetman/flysystem-gitlab-storage).

### Pull Requests

- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - The easiest way to apply the conventions is to install [PHP Code Sniffer](http://pear.php.net/package/PHP_CodeSniffer).
- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.
- **Create feature branches** - Don't ask us to pull from your master branch.
- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
