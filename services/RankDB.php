<?php

class RankDB
{
    public static function getTransactionSumAmount(string $users, $startDate, $endDate)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT SUM(amount) AS date FROM {$prefix}mlm_transactions WHERE tran_user_id IN  ({$users}) 
                AND FROM_UNIXTIME(date, '%Y-%m-%d') >= '{$startDate}' AND FROM_UNIXTIME(date, '%Y-%m-%d') <= '{$endDate}'";

        $result = $wpdb->get_var($sql);

        return $result ? (float)$result : 0.0;
    }

    public static function getUserFirstTransaction(string $userUniqueId)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT * FROM {$prefix}mlm_transactions WHERE tran_user_id = '{$userUniqueId}'";
        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ? $result[0] : [];
    }

    public static function getUserRank(string $userUniqueId, int $rankId)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT * FROM {$prefix}mlm_users_rank WHERE rank_id = {$rankId} and unique_id = '{$userUniqueId}'";
        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ? $result[0] : [];
    }

    public static function getUserNextTransactionByDate(string $userUniqueId, $date)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT * FROM {$prefix}mlm_transactions WHERE tran_user_id = '{$userUniqueId}'
                AND FROM_UNIXTIME(date, '%Y-%m-%d') > '{$date}' ORDER BY id LIMIT 1";
        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ? $result[0] : [];
    }

    public static function getUserReward(string $userUniqueId)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT * FROM {$prefix}mlm_rewards  WHERE mlm_user_id = '{$userUniqueId}'";
        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ? $result[0] : [];
    }

    public static function geUserRankWithReward(string $userUniqueId)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT r.id, r.pcc, r.scc, r.dr, r.sr, r.mr, u.unique_id, u.rank, u.user_id
                FROM {$prefix}mlm_rewards AS r
                INNER JOIN {$prefix}mlm_users AS u ON u.unique_id = r.mlm_user_id WHERE u.unique_id = '{$userUniqueId}'";
        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ? $result[0] : [];
    }

    public static function saveUserRank(string $userUniqueId, int $rank, int $mlmUserId, $bonusPccScc)
    {
        global $wpdb;

        $wpdb->insert("{$wpdb->prefix}mlm_users_rank",
            ['rank_id' => $rank, 'mlm_user_id' => $mlmUserId, 'unique_id' => $userUniqueId, 'pcc_scc' => $bonusPccScc],
            ['%d', '%d', '%s', '%f']
        );

        return $wpdb->update("{$wpdb->prefix}mlm_users", ['rank' => $rank], ['unique_id' => $userUniqueId]);
    }

    public static function saveRewardByCondition(string $userUniqueId, array $condition)
    {
        if (!$condition) return false;

        global $wpdb;

        return $wpdb->update("{$wpdb->prefix}mlm_rewards", $condition, ['mlm_user_id' => $userUniqueId]);
    }

    public static function createBrNotification(string $userUniqueId, int $amount, string $message)
    {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}mlm_reward_notification  WHERE unique_id = '{$userUniqueId}'";
        $issetNotification = $wpdb->get_results($sql, 'ARRAY_A');

        if ($issetNotification) {
            return false;
        }

        $insert_data = ['unique_id' => $userUniqueId, 'amount' => $amount, 'message' => $message];

        return $wpdb->insert("{$wpdb->prefix}mlm_reward_notification", $insert_data);
    }

    public static function deleteBrNotification(string $userUniqueId)
    {
        global $wpdb;

        return $wpdb->delete("{$wpdb->prefix}mlm_reward_notification", ['unique_id' => $userUniqueId]);
    }

    public static function createTransaction(string $userUniqueId, $balance)
    {
        if (!$balance) return false;

        global $wpdb;

        $insert_data = ['tran_user_id' => $userUniqueId, 'amount' => $balance, 'date' => strtotime("now")];

        return $wpdb->insert("{$wpdb->prefix}mlm_transactions", $insert_data);
    }

    public static function getUserWithPatentsTree(string $uniqueId)
    {
        global $wpdb;

        $sql = "WITH RECURSIVE reporting_chain(unique_id, user_name, path, sponsor_id, level) AS (
                SELECT unique_id, user_name, CONCAT(unique_id, '[', re.scc, ',',  us.rank, ',', re.dr, ',', re.sr, ',', re.mr, ']'), sponsor_id, 0
                FROM {$wpdb->prefix}mlm_users  AS us
                INNER JOIN {$wpdb->prefix}mlm_rewards AS re ON re.mlm_user_id =  us.unique_id
                WHERE sponsor_id = '' 
                UNION ALL 
                SELECT oc.unique_id, oc.user_name,  CONCAT(rc.path,'->', oc.unique_id, '[', re.scc, ',',  oc.rank, ',', re.dr, ',', re.sr, ',', re.mr, ']'), oc.sponsor_id, rc.level+1 FROM reporting_chain rc 
                JOIN {$wpdb->prefix}mlm_users oc ON rc.unique_id=oc.sponsor_id 
                INNER JOIN {$wpdb->prefix}mlm_rewards AS re ON re.mlm_user_id = oc.unique_id
                )";
        $sql .= "SELECT * FROM reporting_chain WHERE unique_id = '{$uniqueId}'";

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ?: [];
    }

    public static function getUserPatents(string $uniqueId)
    {
        global $wpdb;

        $sql = "WITH RECURSIVE rec(id, unique_id, sponsor_id, rank,  pcc, scc, dr, sr, mr, scc_second, sr_at) AS ( 
             SELECT  id, unique_id, sponsor_id, rank, 0, 0, 0, 0, 0, 0, sr_at FROM {$wpdb->prefix}mlm_users AS tmp WHERE unique_id = '{$uniqueId}'
             UNION ALL
             SELECT u.id, u.unique_id, u.sponsor_id, u.rank, rew.pcc, rew.scc, rew.dr, rew.sr, rew.mr, rew.scc_second, u.sr_at FROM rec
             INNER JOIN {$wpdb->prefix}mlm_users as u ON rec.sponsor_id = u.unique_id
             INNER JOIN {$wpdb->prefix}mlm_rewards AS rew ON rew.mlm_user_id = u.unique_id
        ) ";

        $sql .= "SELECT * FROM rec WHERE unique_id != '{$uniqueId}' ORDER BY id ASC;";

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ?: [];
    }

    public static function getUserChildren(string $uniqueId)
    {
        global $wpdb;

        $sql = "WITH RECURSIVE rec(id, unique_id, sponsor_id, path,  rank, pcc, scc, dr, sr, mr, level) AS ( 
             SELECT  id, unique_id, sponsor_id, sponsor_id, rank, 0, 0, 0, 0, 0, 1 FROM {$wpdb->prefix}mlm_users AS tmp WHERE sponsor_id = '{$uniqueId}'
             UNION ALL
             SELECT u.id, u.unique_id, u.sponsor_id, CONCAT(rec.path,'-', u.sponsor_id), u.rank, rew.pcc, rew.scc, rew.dr, rew.sr, rew.mr, rec.level+1 FROM rec 
             INNER JOIN {$wpdb->prefix}mlm_users AS u ON rec.unique_id = u.sponsor_id
             INNER JOIN {$wpdb->prefix}mlm_rewards AS rew ON rew.mlm_user_id = u.unique_id
        ) ";

        $sql .= "SELECT * FROM rec WHERE unique_id != '{$uniqueId}' ORDER BY id ASC;";

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ?: [];
    }

    public static function getUserWithChildrenTree(string $sponsorId)
    {
        global $wpdb;

        $sql = "WITH RECURSIVE reporting_chain(unique_id, user_name, path, level, rank) AS (
               SELECT unique_id, user_name, CONCAT(unique_id), 0, us.rank
               FROM {$wpdb->prefix}mlm_users  AS us
               INNER JOIN {$wpdb->prefix}mlm_rewards AS re ON re.mlm_user_id =  us.unique_id
               WHERE sponsor_id = '{$sponsorId}'
               UNION ALL 
               SELECT oc.unique_id, oc.user_name, CONCAT(rc.path,'->', oc.unique_id), rc.level+1, oc.rank
                 FROM reporting_chain rc 
               JOIN {$wpdb->prefix}mlm_users oc ON rc.unique_id=oc.sponsor_id 
               INNER JOIN {$wpdb->prefix}mlm_rewards AS re ON re.mlm_user_id =  oc.unique_id
               )";
        $sql .= "SELECT * FROM reporting_chain";

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ?: [];
    }

    public static function getTransactions($lastId, $limit)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT * FROM {$prefix}mlm_transactions WHERE id > '{$lastId}' ORDER BY id ASC";
        if ($limit) $sql .= " limit {$limit}";

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result;
    }

    public static function getUserByUniqueId($id)
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}mlm_users WHERE unique_id = '{$id}'";
        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ? $result[0] : [];
    }
}