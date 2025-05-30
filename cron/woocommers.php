<?php

ini_set('memory_limit', '256M');

require_once(__DIR__ . '/../../../../wp-config.php');
include(__DIR__ . '/../services/RegisterUser.php');
//rank db
include(__DIR__ . '/../services/RankDB.php');
include(__DIR__ . '/../helpers/RankHelper.php');
include(__DIR__ . '/../services/Reward.php');
include(__DIR__ . '/../services/RankReward.php');

include(__DIR__ . '/../services/WoocommerceIntegrator.php');

$sponsorId = get_option('mlm_default_sponsor', 'USER1');

$sponsorId = 'USER1';
$registryDistributorProducts = ['подписка', 'subscription'];

$registry = new RegisterUser();

$integrator = new WoocommerceIntegrator($sponsorId, $registryDistributorProducts, $registry);
$result = $integrator->integrate();

echo 'Count = ' .  $result . PHP_EOL;


