<?php
require '../vendor/autoload.php';

$connect = new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connect->channel();


$channel->queue_declare('email', false, true, false, false);


$callback = function (\PhpAmqpLib\Message\AMQPMessage  $msg) {
  echo $msg->body ."\n";
  $msg->ack();

};

$channel->basic_consume('email', 'email', false, false, false, false, $callback);
while ($channel->is_consuming()) {
    $channel->wait();
}