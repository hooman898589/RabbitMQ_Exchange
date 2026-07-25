<?php
require '../vendor/autoload.php';
$connect=new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel=$connect->channel();

$channel->exchange_declare('exchange_topic', 'topic', false, false, false);
$channel->queue_declare("user_event", false, true, false, false);

$channel->queue_bind('user_event', 'exchange_topic', 'user.*');


$callback=function(\PhpAmqpLib\Message\AMQPMessage  $msg){
 echo $msg->body;
 $msg->ack();
};

$channel->basic_consume('user_event', '', false, false, false,false ,$callback);
while($channel->is_consuming()) {
    $channel->wait();
}
