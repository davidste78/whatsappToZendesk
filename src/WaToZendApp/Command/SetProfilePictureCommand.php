<?php

namespace WaToZendApp\Command;

use Lib\Config;
use Lib\ZendeskClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetProfilePictureCommand extends Command
{
    /** @var string */
    private $country;
    /** @var bool */
    private $debug = false;
    /** @var InputInterface */
    private $input;
    /** @var OutputInterface */
    private $output;

    protected function configure()
    {
        $this
            ->setName('whatsapp:profile:picture')
            ->setDescription('Set whatsapp profile picture')
            ->addArgument(
                'country',
                InputArgument::REQUIRED,
                'country code 2 letters'
            )
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'absolute path to an image'
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
        $this->path     = $input->getArgument('path');
        $username       = (string) Config::get("$this->country.whatsapp.phone");
        $password       = (string) Config::get("$this->country.whatsapp.password");
        $nickname       = (string) Config::get("$this->country.whatsapp.nickname");

        if ($input->getOption('debug')) {
            $this->debug = true;
        }

        $w = new \WhatsProt($username, $nickname, $this->debug);
        $w->connect();
        $w->loginWithPassword($password);

        try {
            $w->sendSetProfilePicture($this->path);
            $this->output->writeln('Profile image updated');
        } catch (Exception $e) {
            $this->output->writeln('Image was not updated. Try again');
        }

    }

}
