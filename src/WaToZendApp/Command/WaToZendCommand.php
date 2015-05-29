<?php

namespace WaToZendApp\Command;

use Lib\Config;
use Lib\ZendeskClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WaToZendCommand extends Command
{
    /** @var string */
    private $country;
    /** @var bool */
    private $debug = false;
    /** @var InputInterface */
    private $input;
    /** @var OutputInterface */
    private $output;
    /** @var \WhatsProt */
    private $w;
    /** @var ZendeskClient */
    private $z;

    protected function configure()
    {
        $this
            ->setName('whatsapp:client:start')
            ->setDescription('Start the whatsapp client')
            ->addArgument(
                'country',
                InputArgument::REQUIRED,
                'country code 2 letters'
            )
            ->addOption(
                'debug',
                null,
                InputOption::VALUE_NONE,
                'enable debug mode'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input    = $input;
        $this->output   = $output;
        $this->country  = $input->getArgument('country');
        $username       = (string) Config::get("$this->country.whatsapp.phone");
        $password       = (string) Config::get("$this->country.whatsapp.password");
        $nickname       = (string) Config::get("$this->country.whatsapp.nickname");

        if ($input->getOption('debug')) {
            $this->debug = true;
        }

        $this->z = new ZendeskClient($this->country);
        $this->w = new \WhatsProt($username, $nickname, $this->debug);
        $this->w->connect();
        $this->w->loginWithPassword($password);

        while (true) {
            $this->getMessages();
            $this->getComments();
            sleep(5);
        }
    }

    protected function getMessages()
    {
        $this->output->writeln('<info>Listening Messages...</info>');
        $messages = 0;
        while ($this->w->PollMessage()) {
            $data = $this->w->GetMessages();
            if (!empty($data)) {
                foreach ($data as $object) {
                    $this->sendComment($object);
                    $messages++;
                }
            }
            sleep(1);
        }
        $this->output->writeln("<info>$messages</info> messages where sent to zendesk");
    }

    protected function sendComment(\ProtocolNode $node)
    {
        $ticket  = $this->z->manageSend($node);
        $this->output->writeln("ticket generated/edited " . $ticket->ticket->id);
    }

    protected function getComments()
    {
        $this->output->writeln('<info>Looking for comments...</info>');
        $count = 0;
        $messages = $this->z->manageGet();

        foreach ($messages as $message) {
            $count++;
            $this->sendMessage($message['phone'], $message['message']);
        }
        $this->output->writeln("<info>$count</info> comments where sent to WhatsApp");
    }

    protected function sendMessage($target, $message)
    {
        $this->w->sendMessage($target , $message);
        sleep(1);
    }
}
