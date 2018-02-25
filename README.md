# symfony-console-marvel-api-searcher
This PHP appliction uses the Symfony console to search the Marvel API for comics, events, series and stories related to a character.  It gives you the option to export the to 40 results as a CSV file.

Access to the Marvel API is free and requires registration with your email address: https://developer.marvel.com/

## Usage
The syntax is:
```
php console.php [Character Name] [Data Type]
```

At the command line in the project root direction:

```
php console.php Spider-man Events
```

Data Type can be:
* Comics
* Events
* Series
* Stories

## Unit Tests
Unit tests can be run with the following command from the project root directory (Windows):
```
vendor\bin\simple-phpunit.bat
```
[Data provided by Marvel. © 2018 MARVEL](http://marvel.com)