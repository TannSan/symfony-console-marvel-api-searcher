<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class MarvelAPITest extends TestCase
{
    private $client;

    public function setUp()
    {
        $this->client = new GuzzleHttp\Client(['base_uri' => 'https://gateway.marvel.com:443/v1/public/']);
    }

    public function tearDown()
    {
        $this->client = null;
    }

    public function testGuzzleConnectionToMarvelFailsWithNoAuthDetails()
    {
        $response = $this->client->request('GET', 'characters?apikey=', ['http_errors' => false]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testGuzzleConnectionReturnTypeIsJSON()
    {
        $response = $this->client->request('GET', 'characters?apikey=', ['http_errors' => false]);
        $contentType = $response->getHeaders()["Content-Type"][0];
        $this->assertContains("application/json", $contentType);
    }

    public function testNotReachedMarvelAPIRateLimit()
    {
        // The limit for most Marvel services is 1000 per day.
        // Can view your current usage on the developer account page
        // https://developer.marvel.com/account
        $response = $this->client->request('GET', 'characters?apikey=', ['http_errors' => false]);
        $this->assertNotEquals(429, $response->getStatusCode());
    }
}