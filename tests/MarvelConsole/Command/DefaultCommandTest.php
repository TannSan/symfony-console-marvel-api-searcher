<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Exception\RuntimeException;
use MarvelConsole\Command\DefaultCommand;
use MarvelConsole\Connector\MarvelConnector;

class DefaultCommandTest extends TestCase
{
    private $command;

    public function setUp()
    {
        $this->command = new DefaultCommand();
        $this->command->setApplication(new Application());
        $this->command->setConnector(new MarvelConnector());
    }

    public function tearDown() {
        $this->command = null;
    }

    public function testNoArgumentsThrowsException()
    {
        $this->expectException(RuntimeException::class);
        $this->command->run(new ArrayInput(array()), new NullOutput());
        // Throws RuntimeException: 'Not enough arguments (missing: "character, type").')
    }

    public function testOneArgumentThrowsException()
    {
        $this->expectException(RuntimeException::class);
        $this->command->run(new ArrayInput(array('character'=>'Spider-man')), new NullOutput());
        // Throws RuntimeException: 'Not enough arguments (missing: "type").')
    }

    public function testBothArgumentsCorrect()
    {
        $exitCode = $this->command->run(new ArrayInput(array('character'=>'Spider-man', 'type'=>'events')), new NullOutput());
        $this->assertSame(0, $exitCode, '->run() returns an integer exit code');
    }
}