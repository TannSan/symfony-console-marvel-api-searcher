# symfony-console-marvel-api-searcher
This PHP appliction uses the Symfony Console & Guzzle to search the Marvel API for comics, events, series and stories related to a character.  It gives you the option to export the to 40 results as a CSV file.  **Requires PHP 7.1.3 or greater.**

Access to the Marvel API is free and requires registration with your email address: https://developer.marvel.com/

[Data provided by Marvel. © 2018 MARVEL](http://marvel.com)

## Installation
1. Clone this repository: `git clone https://github.com/TannSan/symfony-console-marvel-api-searcher.git`
2. Create your .env file in the project root.
...There is a template file included called `.env.example` which you can rename
3. Set your Marvel API private and public keys in the .env file
4. Download dependancies: `composer update`

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

#### Data Types:
* Comics
* Events
* Series
* Stories

## Unit Tests
Unit tests can be run with the following command from the project root directory:
```
Windows: vendor\bin\simple-phpunit.bat
Linux: vendor/bin/simple-phpunit
```

## Dependencies
* [symfony/console](https://github.com/symfony/console)
* [symfony/dotenv](https://github.com/symfony/dotenv)
* [symfony/phpunit-bridge](https://github.com/symfony/phpunit-bridge)
* [guzzlehttp/guzzle v6](http://docs.guzzlephp.org)
* PHP Extension ext-mbstring (Only for PHPUnit testing)