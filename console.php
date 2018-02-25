#!/usr/bin/env php
<?php 

require_once __DIR__ . '/vendor/autoload.php'; 
 
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Dotenv\Dotenv;
use MarvelConsole\Command\DefaultCommand;
use MarvelConsole\Connector\MarvelConnector;

// TODO: Complete Marvel API guzzle connector
// TODO: Add more PHP Unit tests for new connector functions
// TODO: Error handling for character and data type command line arguments
// TODO: The Marvel Characters call returns a list of characters so could present it as a user selectable option
//       - Update - I tried a few shared character names and the API seems to just return them as one character i.e. Iron Man, Thor, Marvel Boy
//       - Needs more research/testing - https://screenrant.com/marvel-comics-heroes-same-names/
// TODO: Check that they have entered a valid data type - comics, events, series and stories
// TODO: Special handling for non-plurals e.g. user types "event" so it is renamed to "events" in Marvel API call
// TODO: Display results in tabular format
// TODO: Error handling for if character does not exist in API
// TODO: Include Marvel Copyright message with search results - "Data provided by Marvel. © 2014 Marvel"
// TODO: Confirmation message for saving results to CSV
// TODO: Output to CSV
// TODO: Progress bar for fetching data
// TODO: Make sure the quiet mode console option works --quiet
// TODO: If file exists then prompt if they want to replace contents or append to them - fopen("filename.txt", w or a)
// FUTURE TODO: Display a quote from the chosen character
// FUTURE TODO: Imlplement Guzzle caching for faster results and less hits on the server: https://ourcodeworld.com/articles/read/538/how-to-create-a-psr-6-file-system-cache-for-guzzle-in-symfony-3

// Load in our Marvel API keys from the .env file in the project root
// $public_API_key = getenv('PUBLIC_KEY');
// $private_API_key = getenv('PRIVATE_KEY');
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

if(!getenv('PUBLIC_KEY') || !getenv('PRIVATE_KEY'))        
{
    $io = new SymfonyStyle(new StringInput(''), new ConsoleOutput());
    $io->newLine();
    $io->error('You must set your public and private keys in the .env file.');    
    return;
}

$marvel_connector = new MarvelConnector();
$marvel_connector->initialise();

// This is nice to have but it uses up one tick of the daily access rate limit
/*
if(!$marvel_connector->testConnectionAuth())
{
    $io = new SymfonyStyle(new StringInput(''), new ConsoleOutput());
    $io->newLine();
    $io->error(sprintf('The %s API authorisation failed.', $marvel_connector->getName()));
    $message = $marvel_connector->getResponseMessage();
    if($message)
        $io->error($message);
    return;
}
*/

$command = new DefaultCommand();
$command->setConnector($marvel_connector);

$app = new Application("Continuum Comics Marvel API Searcher", "1.0");
$app->add($command);
$app->setDefaultCommand($command->getName(), true);
$app->run();