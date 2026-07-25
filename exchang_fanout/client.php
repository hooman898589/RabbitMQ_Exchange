<?php

require '../vendor/autoload.php';
$connct=new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$chnnel=$connct->channel();


$chnnel->exchange_declare('email_exchange', 'fanout', false, true, false);

list($queue, $msg_count, )=$chnnel->queue_declare('email', false, true, false, false);
$chnnel->queue_bind($queue, 'email_exchange');
echo "{$msg_count} new messages\n";
$callback=function(\PhpAmqpLib\Message\AMQPMessage  $msg) {
  echo " [x] ".$msg->body."\n";
  $msg->ack();
};


$chnnel->basic_consume($queue, '', false, false, false, false, $callback);

while($chnnel->is_consuming()) {
    $chnnel->wait();
}
