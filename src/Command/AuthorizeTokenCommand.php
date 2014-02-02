<?php
namespace GridsBy\OAuth\Command;


use GridsBy\OAuth\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AuthorizeTokenCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('authorize-token')
            ->setDescription('Authorize request-token');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new Client();
        $url = $client->authorizationUrl();

        $output->writeln("Go to the following link in your browser:");
        $output->writeln($url);
    }
}
