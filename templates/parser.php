<?php

//$limit = (isset($_GET['limit']) && $_GET['limit']) ? (int)$_GET['limit'] : 100;
$limit = null;
$lastId = (isset($_GET['last_id']) && $_GET['last_id']) ? (int)$_GET['last_id'] : 0;

$transactions = RankDB::getTransactions($lastId, $limit);

$parse = TransactionParser::parse($transactions);


$rank = new RankReward();
$rank->calculateRank('USER12');

print_r($parse);