<?php
namespace GridsBy\OAuth\Command;


use AiP\Runner;
use GridsBy\OAuth\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebAppCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('webapp')
            ->setDescription('Web application for testing all OAuth steps');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = [
            'app' => [
                'class' => 'GridsBy\OAuth\WebHandler',
                'file' => '',
                'middlewares' => ['Logger', 'HTTPParser']
            ],
            'transport' => 'Socket',
            'protocol' => 'http',
            'socket' => 'tcp://127.0.0.1:8081',
            'min-children' => 1,
            'max-children' => 1
        ];

        $output->writeln("Starting web-server on {$server['socket']}");

        $runner = new Runner(getcwd());
        $runner->addServer($server);
        $runner->go();
    }
}
