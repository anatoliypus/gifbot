<?php

//Подключения модулей
require('../utils/commonInc.php');

use Telegram\Bot\Api; 
use Symfony\Component\HttpFoundation\Response;


$telegram = new Api(getenv('BOT_TOKEN')); //Устанавливаем токен, полученный у BotFather
$result = $telegram->getWebhookUpdates(); //Передаем в переменную $result полную информацию о сообщении пользователя


//Создание и конфигурация приложения
$app = new Silex\Application();
$app['debug'] = true;


// Инициализация Monolog
initializeMonolog($app);


// Веб - обработчики
$app->post('/', function() use($telegram, $result) {

  if (checkCallbackQuery($telegram, $result)) // Проверка на callback_query с inline кнопкой
  {
    return new Response('');
  }

  $chatId = $result["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
  $text = $result["message"]["text"]; //Текст сообщения
  $name = isset($result["message"]["from"]["username"]) ? $result["message"]["from"]["username"] : "Сэр"; //Имя пользователя

  switch ($text) 
  {

    case START_TEXT: //Иницализация и приветствие
      initBot($telegram, $chatId, $name);
      return new Response('');
      break;

    case SEARCH_RANDOM_BTN: //Поиск случайной гифки
      sendRandomGif($telegram, $chatId);
      return new Response('');
      break;

    case SHOW_FAVORITES_BTN: //Показ гифок из списка любимых
      sendFavoritesList($telegram, $chatId);
      return new Response('');
      break;
    
    case SEARCH_BY_TEXT_BTN: //Поиск гифки по тексту - просьба ввести ключевые слова
      askTextToSearch($telegram, $chatId);
      return new Response('');
      break;

    default: //Поиск гифки по тексту
      sendRequiredGif($telegram, $chatId, $text);
      return new Response('');

  }
});

$app->run();