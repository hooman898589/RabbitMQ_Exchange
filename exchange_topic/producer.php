<?php
require '../vendor/autoload.php';
$connect=new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel=$connect->channel();

$channel->exchange_declare('exchange_topic', 'topic', false, false, false);

$channel->basic_publish(new \PhpAmqpLib\Message\AMQPMessage('HELLO'), 'exchange_topic' , "user.select");

$channel->close();
$connect->close();
