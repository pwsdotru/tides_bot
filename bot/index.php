<?php

require_once(dirname(__DIR__) . "/vendor/autoload.php");

use TelegramBot\Api\Client;
use TelegramBot\Api\Exception;

try {
    $config = parse_ini_file(__DIR__ . "/config.ini");
    $bot = new Client($config["TOKEN"]);

    $bot->command('start', function ($message) use ($bot) {
            $bot->sendMessage(
                $message->getChat()->getId(),
                "Welcome to tides bot"
            );
    });

    $bot->run();
} catch (Exception $e) {
    printf("Exception: %s\n\n", $e->getMessage());
    exit(1);
}
