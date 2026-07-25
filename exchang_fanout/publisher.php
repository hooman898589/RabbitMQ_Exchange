<?php

require '../vendor/autoload.php';
$connct=new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$chnnel=$connct->channel();


$chnnel->exchange_declare('email_exchange', 'fanout', false, true, false);

$chnnel->basic_publish(new \PhpAmqpLib\Message\AMQPMessage("hello"), 'email_exchange');


$chnnel->close();
$connct->close();
