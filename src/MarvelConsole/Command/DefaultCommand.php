<?php

namespace MarvelConsole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use MarvelConsole\Connector\ConnectorInterface;

class DefaultCommand extends Command
{
    private $connector;
    private const DATA_TYPES = array('Comics', 'Events', 'Series', 'Stories');
    private const DATA_TYPES_LC = array('comics', 'events', 'series', 'stories');
    private $is_first_time = true;

    public function setConnector(ConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

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
        if($this->is_first_time)
            {
                $io->newLine();
                $io->title(sprintf(' Welcome to the Continuum Comics %s CSV generator ', $this->connector->getName()));
                $this->is_first_time = false;
            }
    }

    /**
     * This is our last chance to get the user to enter a character name and data type
     * in case they didn't enter one or both of them as command line arguments.
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if(!$input->getArgument('character'))
            {
                $character_name = $io->ask('Please enter a character name to search for', null, function ($character_name) {
                    if ($character_name == "")
                        throw new \RuntimeException('You must type a character name');

                    return $character_name;
                });
                $input->setArgument('character', $character_name);
            }

        $data_type = strtolower($input->getArgument('type'));
        if($data_type && substr($data_type, -1) !== 's')
            $data_type .= 's';

        if(!$data_type || in_array($data_type, $this::DATA_TYPES_LC) === false)
            {
                $question = new ChoiceQuestion(' Please select a data type to retrieve', $this::DATA_TYPES, $this::DATA_TYPES[0]);
                $question->setErrorMessage("\n\n [ERROR] Selection %s is not a valid choice\n");
                $question->setNormalizer(function ($value) {
                    if($value && !is_numeric($value) && substr($value, -1) !== 's')
                        $value .= 's';
                    else if($value == "0")
                        $value = $this::DATA_TYPES[0];

                    return $value ? trim(ucfirst(strtolower($value))) : '';
                });
                $input->setArgument('type', strtolower($this->getHelper('question')->ask($input, $output, $question)));

                /*
                // Had to go with the ChoiceQuestion option above so could use the setNormalize method so when they type comics it accepts it even though option is Comics (cap C)
                $data_type = $io->choice('Please select a data type to retrieve', array('Comics', 'Events', 'Series', 'Stories'), 'Comics');
                $input->setArgument('type', $data_type);
                */
            }
        else
            $input->setArgument('type', $data_type);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->text(sprintf('Searching for %s that include %s, please wait...', strtolower($input->getArgument('type')), $input->getArgument('character')));

        $io->newLine();

        $character_id = $this->connector->searchForCharacter($input->getArgument('character'));
        if($character_id === false)
            {
                $io->newLine();
                $io->warning("Character name not located, please try again");
                $this->run(new ArrayInput(array()), $output);
                return;
            }

        $progress = new ProgressBar($output);
        $progress->setFormat(' %bar%');
        $progress->setProgressCharacter("\xF0\x9F\x95\xB7");
        $progress->start();

        for ($i = 0; $i < 10; $i++)
            {
                usleep(50000);
                $progress->advance();
            }

        $progress->finish();

        $io->newLine(2);

        if($io->confirm('Retrieved 40 records, would you like to save them to a CSV document?', true))
            {
                $io->newLine();
                $filename = $io->ask('Please enter a filename', $input->getArgument('character').'_'.ucfirst($input->getArgument('type')).'.csv');

                $io->success('File saved!');
            }

        $io->newLine();
        if($io->confirm('Would you like to perform a new search?', true))
            $this->run(new ArrayInput(array()), $output);
        else
            {
                $io->newLine();
                $io->section(' Thank you for using the Continuum Comics Marvel API searcher! ');
            }
    }
}