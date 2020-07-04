<?php
$app = Route::g();
$app->library('bot');
$app->library('formity2', 'formity');
$app->libraryOwn('curly');

$re = Formity::getInstance('persona');
$re->addField('documento_numero', 'input:number')->setMin(9999999)->setMax(99999999)->setStep(1);

$app->any('', function($app) {
  Bot::config($app->attr('minds') . 'fileMind.json');

  Bot::registerFormity('persona', function($queue, $re) {
    $queue->reply(json_encode($re->getData()));
    $queue->goQueue(1);
  }, function($queue) {
    $queue->reply("Se ha cancelado el formulario");
    $queue->goQueue(1);
  });

  $queue = Bot::createQueue(1);

  $queue->hears('/start', function ($queue) {
    $queue->reply("Iniciamos el formulario:");
    $queue->replyFormity('persona');
  });
  $queue->hears('opciones', function ($queue) {
    $queue->reply("mis opciones");
  });
  $queue->hears(function($n) {
    return strpos($n->text, 'hola') !== false;
  }, function($queue) {
    $queue->reply('Hola! ¿En qué podemos ayudarte?');
  });

  $message = json_decode(file_get_contents("php://input"), true);
  $msg = new class {
    public $chat_id;
    public $text;
    public $data;
    public function reply($txt) {
      $token = '1111522056:AAHrLAQ4j-mmMX6-jzpmuytcYVUDDqlLlJw';
      $url   = 'https://api.telegram.org/bot' . $token . '/sendMessage';
      echo "Respondiendo: " . $txt;
      Curly(CURLY_GET, $url, null, array(
        'chat_id' => $this->chat_id,
        'text'    => $this->data,
      ));
    }
  };
  $msg->data = json_encode($message);
  $msg->chat_id = $message['message']['chat']['id'];
  $msg->text  = $message['message']['text'];
  Bot::listen(1, $msg);

  Formity::delete('persona');

})->else(function() {
  Route::response(404);
});
