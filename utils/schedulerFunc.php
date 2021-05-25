<?php 

function runScheduler($telegram): void //Тело Scheduler
{

  $users = getUsers();
  foreach($users as $user) 
  {

    $id = $user['chat_id'];
    $name = $user['name'];

    $gifArr = getGif();
    $err = $gifArr[1];
    $gif = $gifArr[0];

    if (!$err) 
    {

      $telegram->sendMessage(['chat_id' => "$id", 'text' => "Привет, $name! Не скучай, держи гифку :)"]);
      $url = $gif->data->url;
      sendGifIntoTelegram($telegram, $id, $url);

    }
    
  }
  
}