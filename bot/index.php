<?php

const DAYS_TO_SHOW = 3;
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
            $text = sprintf("Tides from %s for %d days", date("Y-m-d"), DAYS_TO_SHOW);
        try {
            $tides = new API($config["WORLDTIDES_APIKEY"]);
            $tides->setDate(date("Y-m-d"))
                ->setPoint($config["POINT_LAT"], $config["POINT_LON"]);
            $img = $tides->getImage(DAYS_TO_SHOW);
            $tmpfile = tempnam("/tmp", "tides_");
            file_put_contents($tmpfile, $img);
            $photo = new \CURLFile($tmpfile);
            $minmax = $tides->getMinMax(DAYS_TO_SHOW);
            if (is_array($minmax) && array_key_exists("min", $minmax) && array_key_exists("max", $minmax)) {
                if (is_array($minmax["min"]) && count($minmax["min"])) {
                    $text .= "\nMinimum level:\n";
                    foreach ($minmax["mix"] as $min) {
                        $text .= sprintf("%s - %f m\n", date("Y-m-d H:i", (int)$min["dt"]), (float)$min["height"]);
                    }
                }

                if (is_array($minmax["min"]) && count($minmax["min"])) {
                    $text .= "\nMaximum level:\n";
                    foreach ($minmax["max"] as $max) {
                        $text .= sprintf("%s - %f m\n", date("Y-m-d H:i", (int)$max["dt"]), (float)$max["height"]);
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Exception when call to worldtides: " . $e->getMessage());
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
