<?php

const days_to_show = 3;
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
            $text = sprintf("Tides from %s for %d days", date("Y-m-d"), days_to_show);
            try {
                $tides = new API($config["WORLDTIDES_APIKEY"]);
                $tides->setDate(date("Y-m-d"))
                    ->setPoint($config["POINT_LAT"], $config["POINT_LON"]);
                $img = $tides->getImage(days_to_show);
                $tmpfile = tempnam("/tmp", "tides_");
                file_put_contents($tmpfile, $img);
                $photo = new \CURLFile($tmpfile);
            } catch (Exception $e) {
                error_log("Expection on show: " . $e->getMessage());
            }

            $bot->sendPhoto(
                $message->getChat()->getId(),
                $photo,
                $text
            );

            if (file_exists($tmpfile)) {
                unlink($tmpfile);
            }
    });

    $bot->run();
} catch (Exception $e) {
    printf("Exception: %s\n\n", $e->getMessage());
    exit(1);
}
