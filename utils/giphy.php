<?php

//Функции для общения с Giphy API

function getGif($text = null): array // Получение гифки по тексту или рандомно с помощью API сервиса GIPHY
{

  $urlParameters = 'random?';
  $apiKey = getenv('API_KEY');

  if ($text !== null) 
  {
    
    $text = urlencode(trim($text));
    $urlParameters = "search?limit=1&q=$text" . "&";
  }
  
  $curl = curl_init();

  curl_setopt_array($curl, [
    CURLOPT_URL => "https://giphy.p.rapidapi.com/v1/gifs/{$urlParameters}api_key={$apiKey}",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
      "x-rapidapi-host: giphy.p.rapidapi.com",
      "x-rapidapi-key: aa98ea583amsh3311e6609c84969p183b35jsna73aee9d04d2"
    ],
  ]);

  $json = curl_exec($curl);
  $err = curl_error($curl);

  $result = json_decode($json);

  curl_close($curl);

  return [$result, $err];

}