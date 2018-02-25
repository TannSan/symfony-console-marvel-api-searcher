<?php 

namespace MarvelConsole\Connector;

use Symfony\Component\Dotenv\Dotenv;
use GuzzleHttp\Client;

class MarvelConnector implements ConnectorInterface
{
    private $client;
    private $last_response;

    /**
     * Returns the API name.
     */
    public function getName()
    {
        return 'Marvel';
    }

    /**
     * Create a single Guzzle client that will be re-used for all requests.
     */
    public function initialise()
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => 'https://gateway.marvel.com:443/v1/public/']);
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
     * @string  The character name to search for
     * Returns  The JSON response item (which could contain an error code) or false if the character name was not valid.
     */
    public function searchForCharacter(string $character_name)
    {
        if($character_name != "")
            {
                $this->last_response = $this->client->request('GET', 'characters?name='.$character_name.$this->generateAPIAuth(), ['http_errors' => false]);
                return $this->last_response;
            }

        return false;
    }    
}