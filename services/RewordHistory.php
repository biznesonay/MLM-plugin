<?php


class RewordHistory
{
    public function allUserRewardHistory($users = [])
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        
        if (empty($users)) {
            return [
                'status' => false,
                'message' => __('Error to add!', 'marketing')
            ];
        }

        $history = false;
        foreach ($users as $user) {
            $rewards = $this->getUserReward($user);
            $data = $this->createRewardHistory($user, $rewards);
            if (!$data) {
                return [
                    'status' => false,
                    'message' => __('Error to add!', 'marketing')
                ];
            }
            $history = $this->updateUserReward($user, $rewards);
        }

        if ($history) {
            $report = $this->createReport($users);
            return [
                'status' => true,
                'message' => __('Successfully added!', 'marketing')
            ];
        }

        return [
            'status' => false,
            'message' => __('Error to add!', 'marketing')
        ];
    }

    private function getUserReward($user)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT * FROM {$prefix}mlm_rewards WHERE mlm_user_id = '{$user}'";
        $rewards = $wpdb->get_results($sql, 'ARRAY_A');

        return $rewards ? $rewards[0] : [];
    }

    private function updateUserReward($user, $reward)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;

        return $wpdb->update(
            "{$prefix}mlm_rewards",
            [
                'dr' => 0,
                'sr' => 0,
                'mr' => 0,
            ],
            [
                'mlm_user_id' => $user
            ]
        );
    }

    private function createRewardHistory($user, $reward)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;

        $amount = (float)$reward['dr'] + (float)$reward['sr'] + (float)$reward['mr'];
        if ($amount > 0) {
            $afterBalance = 0;
            $history = $wpdb->insert(
                "{$prefix}mlm_rewards_history",
                [
                    'user_id' => $user,
                    'amount' => $amount,
                    'after_rewords_balance' => $afterBalance,
                    'is_regular_payment' => true
                ]
            );

            return $history;
        }

        return 0;
    }

    private function createReport($users)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;

        // TODO excel file
        $now = date("Y_m_d_his");
        $fileName = "report_{$now}.txt";

        $history = $wpdb->insert(
            "{$prefix}mlm_report",
            [
                'file_name' => $fileName,
                'file_path' => '',
                'type_id' => 1
            ]
        );

        return $history;
    }
}