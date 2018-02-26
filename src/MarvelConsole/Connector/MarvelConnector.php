<?php

namespace MarvelConsole\Connector;

use Symfony\Component\Dotenv\Dotenv;
use GuzzleHttp\Client;

class MarvelConnector implements ConnectorInterface
{
    private $client;
    private $last_response;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => 'https://gateway.marvel.com:443/v1/public/']);
    }

    /**
     * Returns the API name.
     */
    public function getName()
    {
        return 'Marvel';
    }

    /**
     * Generates the auth part of the request string as specified by Marvel.
     * https://developer.marvel.com/documentation/authorization
     */
    private function generateAPIAuth()
    {
        $ts = time();
        return '&ts='.$ts.'&apikey='.getenv('PUBLIC_KEY').'&hash='.md5($ts.getenv('PRIVATE_KEY').getenv('PUBLIC_KEY'));
    }

    /**
     * Tests the supplied API authorisation details.
     * Returns true if the connection was successfull and false otherwise.
     */
    public function testConnectionAuth()
    {
        $this->last_response = $this->client->request('GET', 'characters?name=Spider-Man'.$this->generateAPIAuth(), ['http_errors' => false]);
        return $this->last_response->getStatusCode() == 200;
    }

    /**
     * Returns the message body from the last response.
     */
    public function getResponseMessage()
    {
        if($this->last_response && strpos($this->last_response->getHeaders()["Content-Type"][0], 'application/json') !== false)
            return json_decode($this->last_response->getBody())->message;

        return false;
    }

    /**
     * Search for a character.
     * @string  The character name to search for.
     * Returns  The ID of the character or false if the character name was not valid.
     */
    public function searchForCharacter(string $character_name)
    {
        if($character_name != "")
            {
                $this->last_response = $this->client->request('GET', 'characters?name='.$character_name.$this->generateAPIAuth(), ['http_errors' => false]);
                if($this->last_response->getStatusCode() == 200)
                    {
                        $results = json_decode($this->last_response->getBody())->data->results;
                        if(count($results) > 0)
                            return $results[0]->id;
                    }
            }

        return false;
    }

    /**
     * Search for comics/events/series/stories based on the supplied character id and data type.
     * @int     The character id.
     * @string  The data type to search for, valid options are comics/events/series/stories.
     * Returns  An array of results or false if none found.
     */
    public function searchForData(int $character_id, string $data_type)
    {
        $this->last_response = $this->client->request('GET', 'characters/'.$character_id.'/'.$data_type.'?limit=40'.$this->generateAPIAuth(), ['http_errors' => false]);

        if($this->last_response->getStatusCode() == 200)
            {
                $results = json_decode($this->last_response->getBody())->data->results;
                if(count($results) > 0)
                    return $results;
            }

        return false;
    }
}