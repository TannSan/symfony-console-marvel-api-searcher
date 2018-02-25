<?php

namespace MarvelConsole\Connector;

/**
 * Paving the way to add more connectors in the future e.g. DC, DarkHorse, Titan
 */
interface ConnectorInterface
{
    public function getName();
    public function initialise();
    public function testConnectionAuth();
    public function searchForCharacter(string $character_name); 
}