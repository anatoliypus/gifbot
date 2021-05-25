<?php

//Подключения модуля и библиотек
require(__DIR__ . '/../utils/commonInc.php');
use Telegram\Bot\Api;


$telegram = new Api(getenv('BOT_TOKEN')); //Устанавливаем токен, полученный у BotFather


runScheduler($telegram);
