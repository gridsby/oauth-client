<?php
namespace GridsBy\OAuth\Command;


use GridsBy\OAuth\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RequestTokenCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('request-token')
            ->setDescription('Fetch request-token');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new Client();
        $client->fetchRequestToken();

        $data = $client->configData();
        $output->writeln('got request token: '.$data['tokens']['request_token']);
    }
}
