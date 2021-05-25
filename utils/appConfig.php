<?php 

function initializeMonolog($app): void // Добавление monolog в приложение
{

  $app->register(new Silex\Provider\MonologServiceProvider(), [
    'monolog.logfile' => 'php://stderr',
    'monolog.level' => \Monolog\Logger::WARNING,
  ]);

}