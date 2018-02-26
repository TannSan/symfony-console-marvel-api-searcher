<?php

namespace MarvelConsole\DataWriter;

use Symfony\Component\Dotenv\Dotenv;

class OutputToCSV
{
    public static function write(string $file_name, array $row_data, string $mode = 'w')
        {
            if(is_dir($file_name))
                return false;

            $file = fopen($file_name, $mode.'b');

            if($file === false)
                return false;

            if($mode === 'w')
                fputcsv($file, array('Character', 'Data Type', 'Name', 'Description', 'Date First Published', 'Data provided by Marvel. © 2018 MARVEL'), getenv('CSV_SEPERATOR'));

            foreach ($row_data as $row)
                {
                    fputcsv($file, $row, getenv('CSV_SEPERATOR'));
                }

            fclose($file);

            return true;
        }
}