<?php

class RewordHistory
{
    public function allUserRewardHistory(array $users) {
        $added = false;
        $result = ['status' => false, 'message' => __('Error to add!', 'marketing')];

        try {
            if (!$users) {
                return $result;
            }

            foreach ($users as $userId) {
                $reword = self::getUserReward($userId);

                if (!$reword) {
                    continue;
                }

                $amount = (float)($reword['dr'] + $reword['sr'] + $reword['mr']);
                if (!$amount) {
                    continue;
                }


                $added = $this->saveRewardHistory($reword['mlm_user_id'], $amount);

            }

            if ($added) {
                $result['status'] = (bool)$result;
                $result['message'] = __('Successfully added!', 'marketing');
            }
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
        }

        return $result;
    }

    public static function getUserReward(string $userUniqueId)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT * FROM {$prefix}mlm_rewards  WHERE mlm_user_id = '{$userUniqueId}'";
        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ? $result[0] : [];
    }

    public function saveRewardHistory(string $uniqueUserId, float $amount)
    {
        global $wpdb;
        $result = $wpdb->insert("{$wpdb->prefix}mlm_rewards_history", ['user_id' => $uniqueUserId, 'after_rewords_balance'=> 0, 'amount' => $amount, 'is_regular_payment' => true], ['%s', '%f', '%f']);

        if ($result) {
            $result = $wpdb->update("{$wpdb->prefix}mlm_rewards", ['dr' => 0, 'sr' => 0, 'mr' => 0], ['mlm_user_id' => $uniqueUserId], ['%f', '%f', '%f']);
        }

        return $result;
    }
}