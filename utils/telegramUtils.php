<?php 

//Набор функций для взаимодействия с Telegram



function sendRandomGif($telegram, $chatId): void //Отправка пользователю рандомной гифки
{

  $telegram->sendMessage(['chat_id' => $chatId, 'text' => "Держи \xF0\x9F\x98\x8A"]);

  $random = getGif();
  $err = $random[1];
  $response = $random[0];

  if ($err) // Ошибка обращения к GIPHY API 
  {

    $telegram->sendMessage(['chat_id' => $chatId, 'text' => "К сожалению, мне не удалось найти :("]);

  }
  else //Отправка гифки пользователю
  {

    $url = $response->data->url;
    sendGifIntoTelegram($telegram, $chatId, $url);

  }

}




function sendFavoritesList($telegram, $chatId): void // Отправка пользователю его списка фаворитов
{
      
  $list = getFavorites($chatId);

  foreach ($list as $gif_url)
  {
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => $gif_url]);
  }

  $gifAmount = count($list);

  if ($gifAmount > 0)
  {
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => "Вот последние $gifAmount из твоих сохраненных гифок!"]);
  }
  else 
  {
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => "Ты еще не добавил гифки в свой список фаворитов!("]);
  }

}




function askTextToSearch($telegram, $chatId): void // Запрос текста для поиска гифки
{

  $reply = "Какую гифку будем искать? \xF0\x9F\x98\x80";
  $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply]);

}




function sendRequiredGif($telegram, $chatId, $text): void //Отправка гифки по тексту
{

  $reply = "Сейчас поищу \xF0\x9F\x91\x8A";
  $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply]);

  $answer = getGif($text);
  $result = $answer[0];
  $err = $answer[1];

  if ($err) // Ошибка обращения к GIPHY API 
  {

    $telegram->sendMessage(['chat_id' => $chatId, 'text' => "К сожалению, мне не удалось найти :("]);

  } 
  else //Отправка гифки пользователю
  {

    if (isset($result->data[0]->url)) 
    {
      $url = $result->data[0]->url;
      sendGifIntoTelegram($telegram, $chatId, $url);
    }
    else 
    {
      $telegram->sendMessage(['chat_id' => $chatId, 'text' => "Попробуй ввести другой запрос!"]);
    }

  }

}




function sendGifIntoTelegram($telegram, $chatId, $url): void // Функция для отравки кнопка с inline кнопкой
{

  $btn = ['text' => 'Добавить в фавориты!', 'callback_data' => "/wishlist"];
  $inline_keyboard = [[$btn]];
  $keyboard = ["inline_keyboard" => $inline_keyboard];
  $reply_markup = json_encode($keyboard);
  $telegram->sendMessage(['chat_id' => $chatId, 'text' => $url, 'reply_markup' => $reply_markup]);

}




function initBot($telegram, $chatId, $name): void // Набор команд при первом старте диалога с ботом
{

  addUserToDb($chatId, $name);
  $keyboard = [[SEARCH_RANDOM_BTN],[SEARCH_BY_TEXT_BTN], [SHOW_FAVORITES_BTN]]; //Клавиатура
  $reply_markup = $telegram->replyKeyboardMarkup([ 'keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => false ]);
  $reply = "Добро пожаловать в сервис по поиску гифок, $name! \xF0\x9F\x98\x89 Чтобы приступить, выбери одну из функций на панели ниже \xE2\x9C\x8C";
  $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply, 'reply_markup' => $reply_markup]);

}




function checkCallbackQuery($telegram, $result): bool // Проверка и обработка callback_query из inline button
{

  if (isset($result['callback_query']))
  {

    $callback = $result['callback_query'];

    if ($callback['data'] === '/wishlist') 
    {

      $url = $callback['message']['text'];
      $chatId = $callback['message']['chat']['id'];

      if (checkGifIntoFavorites($chatId, $url)) 
      {
        insertGifIntoFavorites($chatId, $url);
      } 
      else 
      {
        $telegram->sendMessage(['chat_id' => $chatId, 'text' => 'Такая гифка в списке фаворитов у тебя уже есть!)']);
      }

    }
    
  }

  return isset($result['callback_query']);
}