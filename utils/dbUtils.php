<?php 

//Набор функций для взаимодействия с базой данных


function initializeDB(): PDO
{

  $dbopts = parse_url(getenv('DATABASE_URL'));

  $name = ltrim($dbopts["path"],'/');
  $host = $dbopts['host'];
  $port = $dbopts['port']; 

  $user = $dbopts['user'];
  $pass = $dbopts['pass'];

  $dsn = "pgsql:host=$host;port=$port;dbname=$name";

  return new PDO($dsn, $user, $pass);
  
}




function addUserToDb($chatId, $name): void //Добавляет пользователя в базу данных
{

  $conn = initializeDB();

  $st = $conn->prepare("SELECT * FROM users WHERE chat_id = $chatId");
  $st->execute();

  $rows = $st->rowCount();

  if ($rows === 0) 
  {
    $st = $conn->prepare("INSERT INTO users VALUES ($chatId, '$name')");
    $st->execute();
  }
  
}




function getFavorites($chatId): array //Возвращает список гифок из листа фаворитов
{

  $conn = initializeDB();

  $result = $conn->query("SELECT * FROM gifs WHERE chat_id = $chatId ORDER BY gif_id ASC");

  $arr = [];
  foreach ($result as $row)
  {
    $arr[] = $row['gif_url'];
  }

  return $arr;

}




function getUsers(): PDOStatement //Возвращает PDOStatement со всеми пользователями бота
{ 
  
  $conn = initializeDB();
  $sql = "SELECT * from users";
  return $conn->query($sql, PDO::FETCH_ASSOC);
  
}




function checkGifIntoFavorites($chatId, $url): bool
{

  $conn = initializeDB();
  $sql = "SELECT * from gifs WHERE chat_id = $chatId AND gif_url = '$url'";
  $result = $conn->query($sql, PDO::FETCH_ASSOC);
  return $result->rowCount() === 0;
  
}




function insertGifIntoFavorites($chatId, $url): void //Добавляет гифку в список фаворитов пользователя
{

  $lastGifId = getUserGifsAmount($chatId);

  if ($lastGifId === 0) 
  {

    insertGif($chatId, $url, 1);

  } 
  elseif ($lastGifId < MAX_GIFS_AMOUNT) 
  {

    $gif_id = $lastGifId + 1;
    insertGif($chatId, $url, $gif_id);

  }
  elseif($lastGifId === MAX_GIFS_AMOUNT) 
  {

    $gifsList = getUserGifs($chatId);

    deleteUserGifs($chatId);

    $gifsList->fetch(PDO::FETCH_ASSOC);
    while ($row = $gifsList->fetch(PDO::FETCH_ASSOC)) 
    {

      $gifIdAdd = $row['gif_id'] - 1;
      $chatIdAdd = $row['chat_id'];
      $urlAdd = $row['gif_url'];
      insertGif($chatIdAdd, $urlAdd, $gifIdAdd);

    }
    insertGif($chatId, $url, MAX_GIFS_AMOUNT);

  }

}




function getUserGifsAmount($chatId): int //Возвращает число сохраненных пользователем гифок
{

  $pdo = initializeDB();

  $st = $pdo->prepare("SELECT * FROM gifs WHERE chat_id = $chatId");
  $st->execute(); 

  return $st->rowCount();

}




function insertGif($chatId, $url, $gifId): void //Добавляет гифку в базу данных
{

  $pdo = initializeDB();
  $st = $pdo->prepare("INSERT INTO gifs(gif_id, chat_id, gif_url) VALUES ($gifId, $chatId, '$url')");
  $st->execute();

}




function deleteUserGifs($chatId): void //Удаляет все гифки пользователя из базы данных
{

  $pdo = initializeDB();
  $del_st = $pdo->prepare("DELETE FROM gifs WHERE chat_id = $chatId");
  $del_st->execute();

}




function getUserGifs($chatId): PDOStatement //Получает все гифки пользователя из базы данных
{

  $pdo = initializeDB();
  $st = $pdo->prepare("SELECT * FROM gifs WHERE chat_id = $chatId ORDER BY gif_id ASC");
  $st->execute();
  return $st;

}