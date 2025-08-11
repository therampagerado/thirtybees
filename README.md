# thirty bees
[![Crowdin](https://d322cqt584bo4o.cloudfront.net/thirty-bees/localized.svg)](https://crowdin.com/project/thirty-bees)
[![Forum](https://img.shields.io/badge/forum-thirty%20bees-brightgreen.svg)](https://forum.thirtybees.com/discover/)

thirty bees is a matured e-commerce solution which once started as a fork of PrestaShop 1.6.1.11 and is still compatible with (almost) all PS 1.6 modules. Its focus is on stability, correctness and reliability of the rich feature set, to allow merchants to focus on growing their business.

## Supporters

thirty bees is committed to being free and open source. We are also committed to making all software that thirty bees develops free and open source. For that reason we have setup a Backers page so our community can help support us. Please feel free to [sign-up](https://forum.thirtybees.com/support-thirty-bees/) as a backer to help support ThirtyBees so it can grow and thrive!

![thirty bees screenshot](docs/thirty-bees-screenshot.jpeg)

## Roadmap

- Remove really old code. Like retrocompatibility code for PS 1.4 and older.
- Remove pointless configuration switches in back office. Quite a number of them are outdated or useless, just distracting merchants and slowing down operations.
  - Support for multiple encryption algorithms. One reliable encryption is entirely sufficient.
  - Support for mixed HTTP/HTTPS sites. This was a good idea in 2005, but triggers browser warnings today.
  - ...
- Package management for JavaScript and CSS vendor packages as well.
- Bring all modules provided by thirty bees to the standards level of default modules.
- Bootstrap 4 for back office.

## Requirements
Support for these general requirements (except recommendations) gets tested during installation, so one can simply try to proceed. A proceeding installation means all requirements are met.

- PHP 7.4 - PHP 8.3
- Apache or nginx
- Linux or MacOS
- MySQL 5.5.3+ or MariaDB 10+
- PHP extensions:
  - Required:
    - bcmath
    - gd
    - json
    - mbstring
    - openssl
    - mysql (PDO only)
    - xml (SimpleXML, DOMDocument)
    - zip
  - Recommended:
    - imap (for allowing to use an IMAP server rather than PHP's built-in mail function)
    - curl (for better handling of background HTTPS requests)
    - opcache (not mandatory because some hosters turn this off in favor of other caching mechanisms)
    - apcu/redis/memcache(d)

## Installation for Shop Owners

- Download the [latest release package](https://github.com/thirtybees/thirtybees/releases) (_thirtybees-vXXX.zip_, ~43 MiB).
- Unpack this ZIP file into your web hosting directory. If you have no shell access, unpack it locally and upload all files, e.g. with [FileZilla](https://filezilla-project.org/). Using a subdirectory works fine.
- Direct your browser to your webhosting, it should show the installer.
- Follow instructions.

## Installation for Developers

You can install the master or follow a [release package](https://github.com/thirtybees/thirtybees/releases)
- Recursively clone the repository and choose tag release version number from the -b parameter:
```shell
$ git clone https://github.com/thirtybees/thirtybees.git --recurse-submodules
```
- Then cd into the `thirtybees` folder
- Run composer to install the dependencies - you have to choose composer file according to php version
```shell
$ COMPOSER=composer/<php>/composer.json composer install
```
- Then install the software as usual, using either a web browser (https://example.com/install-dev)
- Or install via command line
```shell
$  php install-dev/index_cli.php --newsletter=1 --language=en --country=us --domain=thirty.bees:8888 --db_name=thirtybees --db_create=1 --name=thirtybees --email=test@thirty.bees --firstname=thirty --lastname=bees --password=thirtybees
```
- Arguments available:
```
--step          all / database,fixtures,theme,modules                   (Default: all)
--language      Language iso code                                       (Default: en)
--all_languages Install all available languages                         (Default: 0)
--timezone                                                              (Default: Europe/Paris)
--base_uri                                                              (Default: /)
--domain                                                                (Default: localhost)
--db_server                                                             (Default: localhost)
--db_user                                                               (Default: root)
--db_password                                                           (Default: )
--db_name                                                               (Default: thirtybees)
--db_clear      Drop existing tables                                    (Default: 1)
--db_create     Create the database if not exist                        (Default: 0)
--prefix                                                                (Default: tb_)
--engine        InnoDB                                                  (Default: InnoDB)
--name                                                                  (Default: thirty bees)
--activity                                                              (Default: 0)
--country                                                               (Default: fr)
--firstname                                                             (Default: John)
--lastname                                                              (Default: Doe)
--password                                                              (Default: 0123456789)
--email                                                                 (Default: pub@thirtybees.com)
--license       Show thirty bees license                                (Default: 0)
--newsletter    Get news from thirty bees                               (Default: 1)
--send_email    Send an email to the administrator after installation   (Default: 1)
```

## Contributing
See [CONTRIBUTING.md](CONTRIBUTING.md)

## Testing
See [TESTING.md](TESTING.md)