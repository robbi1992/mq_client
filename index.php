<?php

require_once('lib/mq_transactions.php');

$mq = new Lib_mq();
$get = $mq->get_queue();
//$put = $mq->put_queue('Tes put message');

var_dump($get);
//var_dump($put);
