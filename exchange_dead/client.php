<?php

require '../vendor/autoload.php';

$connect = new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connect->channel();

$channel->queue_declare('email', false, true, false, false , false, new \PhpAmqpLib\Wire\AMQPTable([
    "x-dead-letter-exchange" => "orther_exchange-dead",
    "x-dead-letter-routing-key" => "email_dead",
]));

$callback = function (\PhpAmqpLib\Message\AMQPMessage $msg) {
    echo $msg->body . "\n";
    $msg->reject(false);

};

$channel->basic_consume('email', 'email', false, false, false, false, $callback);
while ($channel->is_consuming()) {
    $channel->wait();
}