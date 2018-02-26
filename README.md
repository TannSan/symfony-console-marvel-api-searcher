# symfony-console-marvel-api-searcher
This PHP appliction uses the Symfony Console & Guzzle to search the Marvel API for comics, events, series and stories related to a character.  It gives you the option to export the to 40 results as a CSV file.

Access to the Marvel API is free and requires registration with your email address: https://developer.marvel.com/

[Data provided by Marvel. © 2018 MARVEL](http://marvel.com)

## Usage
The syntax is:
```
php console.php [Character Name] [Data Type]
```

At the command line in the project root directory:

```
php console.php Spider-man Events
```

To have pretty colors on Windows:

```
php console.php Spider-man Events --ansi
```

### Data Type can be:
* Comics
* Events
* Series
* Stories

## Unit Tests
Unit tests can be run with the following command from the project root directory (Windows):
```
vendor\bin\simple-phpunit.bat
```

## Dependancies
[symfony/console](https://github.com/symfony/console)
[symfony/dotenv](https://github.com/symfony/dotenv)
[symfony/phpunit-bridge](https://github.com/symfony/phpunit-bridge)
[guzzlehttp/guzzle v6](http://docs.guzzlephp.org)