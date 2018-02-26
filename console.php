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

// TODO: Add more PHP Unit tests for new connector functions
// TODO: Make sure the quiet mode console option works --quiet
// TODO: Display a quote from the chosen character
// TODO: Implement auto-complete for character name (there is a console helper to help with that)
// TODO: Imlplement Guzzle caching for faster results and less hits on the server: https://ourcodeworld.com/articles/read/538/how-to-create-a-psr-6-file-system-cache-for-guzzle-in-symfony-3
// TODO: Implement the progress bar for while data is loading, can tie it into the Guzzle progress
// TODO: Add results sort command line option
// TODO: Add results limit command line option - maximum number of results to pull - 100 is max
// TODO: Add offset option so can download multiple pages of data

// Load in our Marvel API keys from the .env file in the project root
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