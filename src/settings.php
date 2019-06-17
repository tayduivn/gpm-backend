<?php
$config = json_decode(file_get_contents(__DIR__ . '/../config/config.json'), true);

return [
  'settings' => [
    "determineRouteBeforeAppMiddleware" => true,
    'displayErrorDetails'               => true, // set to false in production
    'addContentLengthHeader'            => false, // Allow the web server to send the content-length header

    // Renderer settings
    'renderer'                          => [
      'template_path' => __DIR__ . '/../templates/',
    ],

    // Monolog settings
    'logger'                            => [
      'name'  => 'slim-app',
      'path'  => __DIR__ . '/../logs/app.log',
      'level' => \Monolog\Logger::DEBUG,
    ],

    'db'  => [
      'host'   => $config['prod_heroku']['host'],
      'user'   => $config['prod_heroku']['user'],
      'pass'   => $config['prod_heroku']['pass'],
      'dbname' => $config['prod_heroku']['dbname']
    ],

    // jwt settings
    "jwt" => [
      'secret' => 'supersecretkeyyoushouldnotcommittogithub'
    ]
  ],
];
