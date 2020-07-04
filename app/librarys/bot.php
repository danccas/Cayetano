<?php
class Bot {
  private $queues       = array();
  private $formitys     = array();
  private $currentQueue = null;
  private $configure    = array();
  public $memory        = array();
  public $memory_mod    = false;

  private static $instance = null;

  public static function importRoute($route) {
    return static::getInstance();
  }
  public static function getInstance() {
    if(is_null(static::$instance)) {
      return static::$instance = new static();
    }
    return static::$instance;
  }
  public static function config($memory_file) {
    $ce = Bot::getInstance();
    if(!empty($ce->configure)) {
      $ce->queues = array();
      static::$instance = null;
      BotQueue::clearQueues();
      $ce = Bot::getInstance();
    }
    $ce->configure['id']   = time();
    $ce->configure['file'] = $memory_file;
    if(file_exists($memory_file)) {
      $rp = json_decode(file_get_contents($memory_file), true);
    }
    if(empty($rp)) {
      $rp = array(
        'formity' => array(
          'id'   => null,
          'part' => 0,
          'data' => null,
        ),
        'queue'   => null,
        'nivel'   => 0,
        'table'   => array(),
      );
    }
    $ce->memory = $rp;
  }
  public static function data() {
    $ce = Bot::getInstance();
    $args = func_get_args();
    if(count($args) == 1) {
      return $ce->memory['table'][$args[0]];

    } else if(count($args) == 2) {
      $ce->memory['table'][$args[0]] = $args[1];
      $ce->memory_mod = true;
      return true;

    } else {
      return false;
    }
  }
  public static function createQueue($key) {
    $ce = Bot::getInstance();
    return $ce->queues[$key] = BotQueue::getInstance($key);
  }
  public static function registerFormity($key, $complete, $cancel) {
    $ce = Bot::getInstance();
    $ce->formitys[$key] = array(
      'complete' => $complete,
      'cancel'   => $cancel,
    );
    return true;
  }
  public static function evalFormity($key, $say = null) {
    $ce = Bot::getInstance();
    if(!isset($ce->formitys[$key])) {
      $queue = BotQueue::getInstance($ce->memory['queue']);
      $queue->reply('No se ha encontrado el formulario');
      return false;
    }
    $re = Formity::getInstance($key);
    $queue = BotQueue::getInstance($ce->memory['queue']);
    $say_null = array(
      'id'    => null,
      'part'  => 0,
      'data'  => null,
    );
    if(is_null($say)) {
      $ce->memory['formity'] = $say_null;
      $ce->memory['formity']['id'] = $key;
      $ce->memory_mod = true;
    } else {
      $queue->message = $say;
      if($say->text == 'salir') {
        $ce->memory['formity'] = $say_null;
        $ce->memory_mod = true;
        if(is_callable($ce->formitys[$key]['cancel'])) {
          $ce->formitys[$key]['cancel'](BotQueue::getInstance($ce->memory['queue']), $re);
        }
        return;
      }
      $re->setPreDataParams($ce->memory['formity']['data']);
    }
//    $queue = BotQueue::getInstance($ce->memory['queue']);
    foreach($re->getFields() as $field) {
      if($field->confirm || $field->disabled) {
        continue;
      }
      denuevo:
      if($ce->memory['formity']['part'] == 0) {
        $queue->reply('Ingrese ' . $field->name);
        $ce->memory['formity']['part']++;
        $ce->memory_mod = true;

      } elseif($ce->memory['formity']['part'] == 1) {
        $e = $field->setValue($say->text);
        $ce->memory_mod = true;
        if($e) {
          $queue->reply('Recibido, confirmalo con un "si"');
          $ce->memory['formity']['part']++;
        } else {
          $ce->memory['formity']['part'] = 0;
          $queue->reply('Error:');
          if(!empty($field->error)) {
            foreach($field->error as $e) {
              $queue->reply($e);
            }
          }
          goto denuevo;
        }

      } elseif($ce->memory['formity']['part'] == 2) {
        if($say->text == 'si') {
          $ce->memory['formity']['part'] = 0;
          $ce->memory_mod = true;
          $field->confirm = true;
          $queue->reply('Confirmado!');
          continue;

        } elseif($say->text == 'no') {
          $ce->memory['formity']['part'] = 0;
          $ce->memory_mod = true;
          $field->clear();
          $queue->reply('Otra vez!');
          goto denuevo;
        } else {
          $queue->reply('No entendí');
        }
      }
      $ce->memory['formity']['data'] = $re->getDataParams($onlySet = true);
      return;
    }
    if(!empty($ce->formitys[$key]['complete'])) {
      $ce->memory['formity'] = $say_null;
      if(is_callable($ce->formitys[$key]['complete'])) {
        $ce->formitys[$key]['complete'](BotQueue::getInstance($ce->memory['queue']), $re);
      }
    }
  }
  public static function listen($queue, $say) {
    $ce = Bot::getInstance();
    if(!is_null($ce->memory['formity']['id'])) {
      return Bot::evalFormity($ce->memory['formity']['id'], $say);

    } elseif(!empty($ce->memory['queue'])) {
      $q = BotQueue::getInstance($ce->memory['queue']);
    } else {
      $q = BotQueue::getInstance($queue);
    }
    return $q->listen($say);
  }
  private function _saveMemory() {
    if($this->memory_mod) {
      file_put_contents($this->configure['file'], json_encode($this->memory));
      return true;
    }
    return false;
  }
  public static function saveMemory() {
    return Bot::getInstance()->_saveMemory();
  }
  function __destruct() {
    return $this->_saveMemory();
  }
}
class BotQueue {
  public $message     = null;
  private $list_hears = array();
  private $hears_none = null;
  private static $instances = array();
  
  public static function getInstance($cdr) {
    if(!is_null($cdr) && !array_key_exists($cdr, static::$instances)) {
      return static::$instances[$cdr] = new static($cdr);
    }
    if(isset(static::$instances[$cdr])) {
      return static::$instances[$cdr];
    }
    return false;
  }
  function __construct($cdr) {
    $this->id = $cdr;
  }
  public static function clearQueues() {
    static::$instances = array();
  }
  private function sequence() {
    $this->nivel++;
    return $this;
  }
  public function hears($that, $call) {
    $this->list_hears[] = array(
      'format' => $that,
      'call'   => $call,
    );
    return $this;
  }
  public function reply($msg) {
    if(empty($this->message)) {
      _log(null, "Sin message");
      return false;
    }
    return $this->message->reply($msg);
  }
  public function replyImage($msg) {
    if(empty($this->message)) {
      _log(null, "Sin message");
      return false;
    }
    return $this->message->replyImage($msg);
  }
  public function else($call) {
    $this->hears_none = $call;
  }
  public function goQueue($queue) {
    $ce = Bot::getInstance();
    $ce->memory['queue'] = $queue;
    $ce->memory_mod = true;
    Bot::saveMemory();
  }
  public function replyFormity($key) {
    return Bot::evalFormity($key);
  }
  public function listen($say) {
    $this->message = $say;
    $ce = Bot::getInstance();
    $ce->memory['queue'] = $this->id;
    if(!(is_null($ce->memory['queue']) && $this->id == 1)) {
      $ce->memory_mod = true;
    }
    if(!empty($this->list_hears)) {
      foreach($this->list_hears as $h) {
        if(is_callable($h['format'])) {
          $rp = $h['format']($say);
          if(!empty($rp)) {
            return $h['call']($this, $say, $rp);
          }
        } elseif($h['format'] == $say->text) {
          return $h['call']($this, $say, null);
        }
      }
    }
    if(!is_null($this->hears_none)) {
      ($this->hears_none)($this, $say);
    }
  }
}
