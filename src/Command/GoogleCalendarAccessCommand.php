<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Utils\GoogleCalendar;

class GoogleCalendarAccessCommand extends Command
{
    protected static $defaultName = "gmi:google-calendar:create-access";

    private $helper;

    public function __construct()
    {
        $this->helper = new GoogleCalendar(false);

        parent::__construct();
    }

    protected function configure(): void
    {
        // The short description shown while running "php bin/console list"
        $this->setDescription("Create the google access token");

        // The full command description shown when running the command with --help
        $this->setHelp(
            "This command allows you to create the google access token to connect to the API\n\n".
            "The <info>%command.name%</info> command is used to create the google access token (google_token.json file in the root directory) to connect to the Google Calendar API.\n\n".
            "Once the command is executed, a link will be displayed in the console to ask you to authenticate with the google account you wish to use to connect to the API.\n\n".
            "Once authenticated, you will be redirected to ISP to finalize the creation of the token file."
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $output->writeln("\nOpen the following link in your browser:\n");
        $output->writeln($this->helper->getClient()->createAuthUrl());

        $output->writeln('Enter verification code: ');

        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $this->helper->getClient()->fetchAccessTokenWithAuthCode($authCode);
        $this->helper->getClient()->setAccessToken($accessToken);

        // Check to see if there was an error.
        if (array_key_exists('error', $accessToken)) {
            $io->error(join(', ', $accessToken));
        }

        // Save the token to a file.
        if (!file_exists(dirname($this->helper::TOKEN_PATH))) {
            mkdir(dirname($this->helper::TOKEN_PATH), 0700, true);
        }

        file_put_contents($this->helper::TOKEN_PATH, json_encode($this->helper->getClient()->getAccessToken()));

        return 0;
    }
}

?>