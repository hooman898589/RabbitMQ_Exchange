<?php

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

require '../vendor/autoload.php';
$connect=new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connect->channel();


$channel->exchange_declare('orther_exchange', 'direct', false, true, false);
$channel->exchange_declare('orther_exchange-dead', "direct", false, true, false);


$channel->queue_declare('email', false, true, false, false, false , new AMQPTable([
    'x-dead-letter-exchange'=>"orther_exchange-dead",
    'x-dead-letter-routing-key'=>"email_dead",
]) );
$channel->queue_declare("email_dead", false, true, false, false);


$channel->queue_bind('email', 'orther_exchange', 'email');
$channel->queue_bind('email_dead', 'orther_exchange-dead', 'email_dead');


$channel->basic_publish(new AMQPMessage('hello world!'), "orther_exchange", 'email');


$channel->close();
$connect->close();