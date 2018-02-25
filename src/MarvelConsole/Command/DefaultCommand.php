<?php 

namespace MarvelConsole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

class DefaultCommand extends Command
{
    protected function configure()
    {
        $this->setName('marvel:search')
        ->setDescription('Retrieves records about characters from the Marvel universe.')
        ->setHelp('Retrieves records about characters from the Marvel universe.')
        ->addArgument('character', InputArgument::REQUIRED, 'The Marvel character you wish to search for i.e. Spider-man')   
        ->addArgument('type', InputArgument::REQUIRED, 'The data type you wish to return.  Available choices are: comics / events / series / stories');   
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->newLine();
        $io->title(' Welcome to the Continuum Comics Marvel API CSV generator ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->text(sprintf('Searching for %s that include %s', strtolower($input->getArgument('type')), $input->getArgument('character')));
    }
}