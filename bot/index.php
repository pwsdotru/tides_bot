<?php

require_once(dirname(__DIR__) . "/vendor/autoload.php");

use TelegramBot\Api\Client;
use TelegramBot\Api\Exception;
use Worldtides\API;

try {
    $config = parse_ini_file(__DIR__ . "/config.ini");
    $bot = new Client($config["TOKEN"]);

    $bot->command('start', function ($message) use ($bot) {
            $bot->sendMessage(
                $message->getChat()->getId(),
                "Welcome to tides bot. Use /show for get Tides level for next 7 days."
            );
    });

    $bot->command('show', function ($message) use ($bot) {
            global $config;
            $tides = new API($config["WORLDTIDES_APIKEY"]);
            $tides->setDate(date("Y-m-d"))
                ->setPoint($config["POINT_LAT"], $config["POINT_LON"]);
            $bot->sendMessage(
                $message->getChat()->getId(),
                $tides->geImage(7)
            );
    });

    $bot->run();
} catch (Exception $e) {
    printf("Exception: %s\n\n", $e->getMessage());
    exit(1);
}
