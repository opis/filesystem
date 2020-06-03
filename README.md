# Opis FileSystem
[![Tests](https://github.com/opis/filesystem/workflows/Tests/badge.svg)](https://github.com/opis/filesystem/actions)
[![Latest Stable Version](https://poser.pugx.org/opis/filesystem/version.png)](https://packagist.org/packages/opis/filesystem)
[![Latest Unstable Version](https://poser.pugx.org/opis/filesystem/v/unstable.png)](//packagist.org/packages/opis/filesystem)
[![License](https://poser.pugx.org/opis/filesystem/license.png)](https://packagist.org/packages/opis/filesystem)

**Opis FileSystem** is a filesystem abstraction library.


## License

**Opis FileSystem** is licensed under the [Apache License, Version 2.0][license].

## Requirements

* PHP ^7.4
* ext-json
* ext-fileinfo
* [Opis Stream]

## Installation

**Opis FileSystem** is available on [Packagist] and it can be installed from a
command line interface by using [Composer]. 

```bash
composer require opis/filesystem
```

Or you could directly reference it into your `composer.json` file as a dependency

```json
{
    "require": {
        "opis/filesystem": "^2020"
    }
}
```


[license]: https://www.apache.org/licenses/LICENSE-2.0 "Apache License"
[Packagist]: https://packagist.org/packages/opis/filesystem "Packagist"
[Composer]: https://getcomposer.org "Composer"
[Opis Stream]: https://github.com/opis/stream "Opis Stream"
