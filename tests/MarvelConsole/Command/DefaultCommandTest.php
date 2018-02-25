<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\Output;
use MarvelConsole\Command\DefaultCommand;
use MarvelConsole\Connector\MarvelConnector;

class DefaultCommandTest extends TestCase
{
    private $command;
    protected $tester;

    public function setUp()
    {
        $this->command = new DefaultCommand();
        $this->command->setApplication(new Application());
        $this->command->setConnector(new MarvelConnector());
        $this->command->setCode(function ($input, $output) {});
        $this->tester = new CommandTester($this->command);
    }

    public function tearDown() {
        $this->command = null;
        $this->tester = null;
    }

    public function testNoArgumentsThrowsException()
    {
        $this->expectException(RuntimeException::class);
        $this->tester->setInputs(array(''));
        $this->tester->execute(array());
        // Throws RuntimeException: 'Not enough arguments (missing: "character, type").')
    }

    public function testSingleArgumentThrowsException()
    {
        $this->expectException(RuntimeException::class);
        $this->tester->setInputs(array('Spider-Man'));
        $this->tester->execute(array());
        // Throws RuntimeException: 'Not enough arguments (missing: "type").')
    }

    public function testInvalidTypeArgumentThrowsException()
    {
        $this->expectException(RuntimeException::class);
        $this->tester->setInputs(array('Spider-Man', 'holiday'));
        $this->tester->execute(array());
    }

    public function testBothValidArgumentsProvided()
    {
        $this->tester->execute(array('character' => 'Spider-Man', 'type' => 'Events'));
        $this->assertSame(0, $this->tester->getStatusCode(), '->getStatusCode() returns the status code');
    }
}