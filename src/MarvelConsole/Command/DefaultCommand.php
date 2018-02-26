<?php

namespace MarvelConsole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Helper\Table;
use MarvelConsole\Connector\ConnectorInterface;
use MarvelConsole\DataWriter\OutputToCSV;

class DefaultCommand extends Command
{
    private const DATA_TYPES = array('Comics', 'Events', 'Series', 'Stories');
    private const DATA_TYPES_LC = array('comics', 'events', 'series', 'stories');
    private $is_first_time = true;
    private $connector;

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

        // If they type "comic" or "event" instead of "comics" and "events" then append the "s"
        $data_type = strtolower($input->getArgument('type'));
        if($data_type && substr($data_type, -1) !== 's')
            $data_type .= 's';

        if(!$data_type || in_array($data_type, $this::DATA_TYPES_LC) === false)
            {
                // Had to go with the ChoiceQuestion option instead of $io->choice so could use the setNormalize method so when they type comics it accepts it even though option is Comics (cap C)
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
            }
        else
            $input->setArgument('type', $data_type);
    }

    /**
     * This is where the bulk of the form generation is handled.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $character_name = $input->getArgument('character');
        $data_type = $input->getArgument('type');

        $io = new SymfonyStyle($input, $output);

        $io->text(sprintf('Searching for %s that include %s, please wait...', $data_type, $character_name));
        $io->newLine();

        $character_id = $this->connector->searchForCharacter($character_name);
        if($character_id === false)
            {
                $io->newLine();
                $io->warning("Character name not located, please try again");
                $this->run(new ArrayInput(array()), $output);
                return;
            }
        else
            {
                $search_results = $this->connector->searchForData($character_id, $data_type);
                if($search_results === false)
                    {
                        $io->newLine();
                        $io->warning(sprintf("There are no %s that include %s, please try again", $data_type, $character_name));
                        $this->run(new ArrayInput(array()), $output);
                        return;
                    }
                else
                    {
                        $data_type = ucfirst($data_type);
                        $result_count = count($search_results);
                        $formatted_results = array();
                        $csv_results = array();
                        $current_result;
                        $date_formatted;

                        // I did each as a seperate loop as an optimisation to avoid having 40 conditionals for each set of results to handle the unique date field for each data type
                        switch($data_type)
                            {
                                case "Comics":
                                    for($i = 0; $i < $result_count; $i++)
                                        {
                                            $current_result = $search_results[$i];
                                            $date_formatted = strtok(array_column($current_result->dates, 'date', 'type')['onsaleDate'], 'T');
                                            $formatted_results[] = array($character_name, $data_type, $this->cleanOutputString($this->truncate($current_result->title, 50)), $date_formatted);
                                            $csv_results[] = array($character_name, $data_type, $this->cleanOutputString($current_result->title), $this->cleanOutputString($current_result->description), $date_formatted, '');
                                        }
                                    break;
                                case "Events":
                                    for($i = 0; $i < $result_count; $i++)
                                        {
                                            $current_result = $search_results[$i];
                                            $date_formatted = strtok($current_result->start, ' ');
                                            $formatted_results[] = array($character_name, $data_type, $this->cleanOutputString($this->truncate($current_result->title, 50)), $date_formatted);
                                            $csv_results[] = array($character_name, $data_type, $this->cleanOutputString($current_result->title), $this->cleanOutputString($current_result->description), $date_formatted, '');
                                        }
                                    break;
                                case "Series":
                                    for($i = 0; $i < $result_count; $i++)
                                        {
                                            $current_result = $search_results[$i];
                                            $formatted_results[] = array($character_name, $data_type, $this->cleanOutputString($this->truncate($current_result->title, 50)), $current_result->startYear);
                                            $csv_results[] = array($character_name, $data_type, $this->cleanOutputString($current_result->title), $this->cleanOutputString($current_result->description), $current_result->startYear, '');
                                        }
                                    break;
                                default:
                                    // Stories
                                    for($i = 0; $i < $result_count; $i++)
                                        {
                                            $current_result = $search_results[$i];
                                            $formatted_results[] = array($character_name, $data_type, $this->cleanOutputString($this->truncate($current_result->title, 50)), 'N/A');
                                            $csv_results[] = array($character_name, $data_type, $this->cleanOutputString($current_result->title), $this->cleanOutputString($current_result->description), 'N/A', '');
                                        }
                                    break;
                            }

                        $table = new Table($output);
                        $table->setHeaders(array('Character', 'Data Type', 'Name', 'Date First Published'))->setRows($formatted_results);
                        $table->setStyle('borderless');
                        $table->render();

                        $io->newLine();
                        $io->text("Data provided by Marvel. © 2018 MARVEL");
                        $io->newLine(2);

                        if($io->confirm(sprintf('Retrieved %d records, would you like to save them to a CSV document?', $result_count), true))
                            {
                                $file_name_invalid = true;
                                $file_name_suffix_int = 2;
                                $file_name_suffix = '';
                                $file_saved_successfully = false;
                                while($file_name_invalid)
                                    {
                                        $io->newLine();
                                        $file_name = $io->ask('Please enter a filename', $character_name.'_'.ucfirst($data_type).$file_name_suffix.'.csv');

                                        if(file_exists($file_name))
                                            {
                                                $option_1 = 'Choose a new filename';
                                                $option_2 = 'Overwrite it with the new data';
                                                $option_3 = 'Append the new data to the end';
                                                $file_option = $io->choice('That file already exists, what would you like to do?', array($option_1, $option_2, $option_3), $option_1);

                                                switch($file_option)
                                                    {
                                                        case $option_1:
                                                            $file_name_suffix = '_'.$file_name_suffix_int++;
                                                            break;
                                                        case $option_2:
                                                            $file_saved_successfully = OutputToCSV::Write($file_name, $csv_results);
                                                            $file_name_invalid = false;
                                                            break;
                                                        case $option_3:
                                                            $file_saved_successfully = OutputToCSV::Write($file_name, $csv_results, 'a');
                                                            $file_name_invalid = false;
                                                            break;
                                                    }
                                            }
                                        else
                                            {
                                                $file_saved_successfully = OutputToCSV::Write($file_name, $csv_results);
                                                $file_name_invalid = false;
                                            }
                                    }

                                if($file_saved_successfully)
                                    $io->success('File saved!');
                                else
                                    $io->error('Error saving file!');
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
    }

    /**
     * Strips HTML tags and newlines out of the provided text.
     * @string  The text to be cleaned.
     */
    private function cleanOutputString(string $text = null)
    {
        if(is_null($text))
            return '';

        return strip_tags(str_replace(array("\r", "\n"), ' ', $text));
    }

    /**
     * Truncates text to the specified length.
     * @string  The text to be truncated.
     * @int     The length to truncate the string to.
     */
    private function truncate(string $text, int $max_length = 25)
    {
        if(strlen($text) > $max_length)
            return substr($text, 0, $max_length - 3).'...';

        return $text;
    }
}