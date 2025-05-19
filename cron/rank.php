<?php
ini_set('memory_limit', '256M');

require_once(__DIR__ . '/../../../../wp-config.php');
include(__DIR__ . '/../services/RankDB.php');
include(__DIR__ . '/../helpers/RankHelper.php');
include(__DIR__ . '/../services/RankReward.php');


$result = calculateUsersRank();

echo "Cron status = $result";

function getUsers(): array
{
    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}mlm_users";

    $users = $wpdb->get_results($sql, 'ARRAY_A');

    return $users ?: [];
}

function calculateUsersRank(): bool
{
    $users = getUsers();

    if (!$users) {
        return false;
    }

    $service = new RankReward();

    foreach ($users as $user) {
       $rankStatus = $service->calculateRank($user['unique_id']);
       echo $user['unique_id'] . ' rank is calculated = ' . ($rankStatus ? 'true' : 'false') . PHP_EOL;
    }

    return true;
}