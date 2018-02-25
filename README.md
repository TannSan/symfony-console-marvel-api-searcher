# symfony-console-marvel-api-searcher
This PHP appliction uses the Symfony console to search the Marvel API for comics, events, series and stories related to a character.  It gives you the option to export the to 40 results as a CSV file.

Access to the Marvel API is free and requires registration with your email address: https://developer.marvel.com/

##### Usage
At the command line in the project root direction:

php console.php [Character Name] [Data Type]

Example: php console.php Spider-man events

Data Type can be:
* comics
* events
* series
* stories

##### Unit Tests
Can be run with: vendor\bin\simple-phpunit.bat

[Data provided by Marvel. © 2014 Marvel](http://marvel.com)