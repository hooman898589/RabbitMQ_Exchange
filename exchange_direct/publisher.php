<?php


require '../vendor/autoload.php';
$connect=new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connect->channel();


$channel->exchange_declare('exchange_order', 'direct', false, true, false);

$channel->queue_declare('sms', false, true, false, false);
$channel->queue_declare('email', false, true, false, false);


$channel->queue_bind('sms', 'exchange_order', 'sms');
$channel->queue_bind('email', 'exchange_order', 'sms');
$channel->basic_publish(new \PhpAmqpLib\Message\AMQPMessage("hello"), 'exchange_order', 'sms');

$channel->close();
$connect->close();