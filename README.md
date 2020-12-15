ExportBA
========

Dieses Modul exportiert Stellenanzeigen aus einer Yawik Installation an die
"Bundesagentur für Arbeit". Um das Modul nutzen zu können benötigt man einen
Kooperationsvertrag mit der BA.

https://www.arbeitsagentur.de/datei/dok_ba015033.pdf


Requirements
------------

running [YAWIK](https://github.com/cross-solution/YAWIK)


Installation
------------

Require a dependency via composer.

```bash
composer require yawik/export-ba
```

Enable the module for the Zend module manager via creating the `simpleimport.module.php` file in the `/config/autoload` directory with the following content.

```php
<?php
return [
    'ExportBA'
];
```

Configuration
-------------

TBD


Development
-------
1.  Clone project
```sh
$ git clone git@github.com:yawik/export-ba.git /path/to/export-ba 
```

2. Install dependencies:
```sh
$ composer install
```

3. Run PHPUnit Tests
```sh
$ ./vendor/bin/phpunit
```

4. Run Behat Tests
```sh

```

Licence
-------

MIT
