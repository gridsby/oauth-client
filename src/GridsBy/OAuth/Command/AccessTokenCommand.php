<?php
namespace GridsBy\OAuth\Command;


use GridsBy\OAuth\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AccessTokenCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('access-token')
            ->addArgument('verifier', InputArgument::OPTIONAL, 'What is the PIN?')
            ->setDescription('Fetch access-token');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $verifier = $input->getArgument('verifier');

        if (!$verifier) {
            /** @var DialogHelper $dialog */
            $dialog = $this->getApplication()->getHelperSet()->get('dialog');
            $verifier = $dialog->ask($output, 'What is the PIN?');
        }

        $client = new Client();
        $client->fetchAccessToken($verifier);

        $data = $client->configData();
        $output->writeln('got access token: '.$data['tokens']['access_token']);
    }
}
