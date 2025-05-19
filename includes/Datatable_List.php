<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(ABSPATH . 'wp-config.php');

class Datatable_List
{

    public function getUserByPhone($phone)
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}users WHERE user_phone = '{$phone}'";
        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ? $result[0] : [];
    }

    public function get_all_data($table)
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}$table";
        $result = $wpdb->get_results($sql, 'OBJECT');
        return $result;
    }

    public function get_all_cond_data($table, $condition)
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}$table where $condition";
        $result = $wpdb->get_results($sql, 'OBJECT');
        return $result;
    }

    public function get_user_city_data()
    {
        global $wpdb;
        $sql = "SELECT u.*, c.name as city_name FROM {$wpdb->prefix}mlm_users as u left join {$wpdb->prefix}mlm_city as c on c.id = u.city_id where u.role = 'distributor' AND u.rank > 0";
        $result = $wpdb->get_results($sql, 'OBJECT');
        return $result;
    }

    public function getTransactions($table, $userId)
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}$table where tran_user_id = '{$userId}'";
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        return $result;
    }

    public function insert_data($table, $insert_data)
    {
        global $wpdb;
        $result = $wpdb->insert("{$wpdb->prefix}$table", $insert_data);
        return $result;
    }

    public function delete($table, $delete_data)
    {
        global $wpdb;
        return $wpdb->delete("{$wpdb->prefix}$table", $delete_data);
    }

    public function deleteUser($table, $userId)
    {
        global $wpdb;
        $result = ['status' => false, 'message' => __('Error to delete', 'marketing')];
        $children = $this->getChildren($userId);
        if (!$children) {
            $deleted = $wpdb->delete("{$wpdb->prefix}mlm_users", ['id' => $userId]);
            if ($deleted) {
                $result['status'] = true;
                $result['message'] = __('Successfully deleted!', 'marketing');
            }
        } else {
            $result['message'] = __('Exist children, can not delete!', 'marketing');
        }

        return $result;
    }

    public function deleteTransaction($table, $transactionId)
    {
        $result = ['status' => false, 'message' => __('Error to delete', 'marketing')];

        $transactionReward = $this->getTransactionReward($transactionId);
        if (!$transactionReward) return $result;

        if ($transactionReward['mlm_user_id'] == $transactionReward['tran_user_id']) {
            $amount = $transactionReward['pcc'] - $transactionReward['amount'];
            $updated = $this->updateData('mlm_rewards', ['pcc' => $amount], ['id' => $transactionReward['reward_id']]);

            if ($updated) {
                $deleted = $this->delete("mlm_transactions", ['id' => $transactionId]);

                if ($deleted) {
                    $result['status'] = true;
                    $result['message'] = __('Successfully deleted!', 'marketing');
                }
            }
        }

        return $result;
    }

    public function getTransactionReward($userId)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT r.id as reward_id, r.pcc, r.scc, r.mlm_user_id, t.tran_user_id, t.id as transaction_id, t.amount
                FROM {$prefix}mlm_rewards AS r
                INNER JOIN {$prefix}mlm_transactions AS t ON t.tran_user_id = r.mlm_user_id WHERE t.id = {$userId}";
        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ? $result[0] : [];
    }

    public function getChildren($userId)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT * FROM {$prefix}mlm_users WHERE sponsor_id = (SELECT unique_id FROM {$prefix}mlm_users WHERE id = {$userId} LIMIT 1) ";

        return $wpdb->get_results($sql, 'ARRAY_A');
    }

    public function registerUser($formField = array())
    {
        $register_details = $this->register_user(
            $formField['mlm_distributor_email'] ?? null,
            $formField['mlm_distributor_password'] ?? null,
            $formField['mlm_distributor_name'],
            $formField['mlm_distributor_sponsor'],
            $formField['mlm_distributor_phone'],
            $formField['city_id']
        );
        return $register_details;
    }

    private function register_user($email, $password, $name, $sponsorid, $phone, $cityId)
    {
        global $wpdb;
        $result = null;

        $phoneNumber = preg_replace('![^0-9]+[+]*!', '', $phone);
        $phoneNumber = $phoneNumber ? '+' . $phoneNumber : null;
        if ($this->getUserByPhone($phoneNumber)) {
            $errors['error'] = __('Phone already exits', 'marketing');
            return $errors;
        } else {
            $user_data = array(
                'user_login' => $phoneNumber,
                'user_phone' => $phoneNumber,
                'user_pass' => $password,
                'first_name' => $name,
                'last_name' => '',
                'nickname' => '',
                'role' => 'distributor',
            );

            $user_id = wp_insert_user($user_data);

            if ($user_id) {
                $this->updateOrCreateUserPhone($user_id, $phoneNumber);
                wp_new_user_notification($user_id);
                //wp_set_password($password, $user_id);
                $last_uniqid = 'USER' . $user_id;

                $insert_data = array(
                    'unique_id' => $last_uniqid,
                    'user_id' => $user_id,
                    'user_name' => $name,
                    'user_phone' => $phoneNumber,
                    'sponsor_id' => $sponsorid,
                    'rank' => '0',
                    'role' => 'distributor',
                    'date' => strtotime("now"),
                    'city_id' => $cityId
                );

                $this->insert_data('mlm_users', $insert_data);
                $insert_data2 = array('mlm_user_id' => $last_uniqid);
                $this->insert_data('mlm_rewards', $insert_data2);

                $date = new \DateTime();
                $sr_at = $date->modify('+30 day')->format('Y-m-d');

                $this->updateData('mlm_users', ['sr_at' => $sr_at], ['unique_id' => $sponsorid]);

                $result['userid'] = $user_id;
            }
        }

        return $result;
    }


    public function get_all_current_distrubutor($table)
    {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}$table ";
        $sql .= "WHERE role = 'distributor' ";
        $sql .= "ORDER BY id DESC";

        $result = $wpdb->get_results($sql, 'OBJECT');

        return $result;
    }

    public function get_all_current_distrubutor_city()
    {
        global $wpdb;
        $sql = "SELECT u.*, c.name as city_name FROM {$wpdb->prefix}mlm_users as u left join {$wpdb->prefix}mlm_city as c on c.id = u.city_id where u.role = 'distributor' ORDER BY id DESC";

        $result = $wpdb->get_results($sql, 'OBJECT');

        return $result;
    }


    public function get_all_distributor($table)
    {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}$table ";
        $sql .= "WHERE role = 'distributor' ";

        $result = $wpdb->get_results($sql, 'OBJECT');

        return $result;
    }

    public function getAllUserRank(string $uniqueId = null)
    {
        global $wpdb;

        $sql = "select r.*, u.user_name from {$wpdb->prefix}mlm_users_rank as r  inner join {$wpdb->prefix}mlm_users u on r.mlm_user_id = u.id";

        if ($uniqueId) {
            $sql .= ' where unique_id = "'. $uniqueId . '"';
        }

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result;
    }

    public function getAllDistributorWithRewards($table)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT u.*, r.pcc, r.scc, r.dr, r.sr, r.mr, r.br FROM {$prefix}$table as u inner join {$prefix}mlm_rewards AS r ON r.mlm_user_id = u.unique_id ";
        $sql .= "WHERE role = 'distributor' ";

        $result = $wpdb->get_results($sql, 'OBJECT');

        return $result;
    }

    public function getAllWillPayReportFile()
    {
        global $wpdb;
        $prefix = $wpdb->prefix;

        $result = $wpdb->get_results("SELECT * FROM {$prefix}mlm_report where type_id = 1 order by id desc", 'ARRAY_A');

        return $result;
    }

    public function getAllPayedReportFile()
    {
        global $wpdb;
        $prefix = $wpdb->prefix;

        $result = $wpdb->get_results("SELECT * FROM {$prefix}mlm_report where type_id = 2 order by id desc", 'ARRAY_A');

        return $result;
    }

    public function get_all_transuctions()
    {
        global $wpdb;

        $sql = "SELECT {$wpdb->prefix}mlm_transactions.date as transuction_date,{$wpdb->prefix}mlm_transactions.id as transuction_id,{$wpdb->prefix}mlm_transactions.*,{$wpdb->prefix}mlm_users.* FROM {$wpdb->prefix}mlm_transactions ";
        $sql .= "INNER JOIN {$wpdb->prefix}mlm_users ";
        $sql .= "ON {$wpdb->prefix}mlm_transactions.tran_user_id = {$wpdb->prefix}mlm_users.unique_id ";
        $sql .= "WHERE {$wpdb->prefix}mlm_users.role='distributor'";
        $sql .= "ORDER BY {$wpdb->prefix}mlm_transactions.date DESC";

        $result = $wpdb->get_results($sql, 'OBJECT');

        return $result;
    }


    public function getAllRewardsHistory()
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT {$prefix}mlm_rewards_history.*, {$prefix}mlm_users.user_name FROM {$prefix}mlm_rewards_history ";
        $sql .= "INNER JOIN {$prefix}mlm_users ";
        $sql .= "ON {$prefix}mlm_rewards_history.user_id = {$prefix}mlm_users.unique_id ";
        $sql .= "ORDER BY {$prefix}mlm_rewards_history.id DESC";

        $result = $wpdb->get_results($sql, 'OBJECT');

        return $result;
    }

    public function getAllRewardsHistoryByUser($uniqueId)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT {$prefix}mlm_rewards_history.*, {$prefix}mlm_users.* FROM {$prefix}mlm_rewards_history ";
        $sql .= "INNER JOIN {$prefix}mlm_users ";
        $sql .= "ON {$prefix}mlm_rewards_history.user_id = {$prefix}mlm_users.unique_id ";
        $sql .= "where  {$prefix}mlm_users.unique_id = '{$uniqueId}' ";
        $sql .= "ORDER BY {$prefix}mlm_rewards_history.id DESC";

        $result = $wpdb->get_results($sql, 'OBJECT');

        return $result;
    }

    public function userLogin($user_login, $user_password)
    {
        $creds = array();

        $creds['user_login'] = $user_login;
        $creds['user_password'] = $user_password;
        $creds['remember'] = true;
        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            return 'error';
        } else {
            $userID = $user->ID;

            wp_set_current_user($userID, $user_login);
            wp_set_auth_cookie($userID, true, false);

            do_action('wp_login', $user_login);

            return $userID;
        }
    }

    public function updateData($table, $update, $condition)
    {
        global $wpdb;

        return $wpdb->update("{$wpdb->prefix}$table", $update, $condition);
    }

    public function createRewardsHistory($data)
    {
        $result = false;
        global $wpdb;
        $historyData['user_id'] = (string)$data['user_id'];
        $historyData['amount'] = (float)$data['amount'];

        $userRewards = $this->get_all_cond_data('mlm_rewards', 'mlm_user_id = "' . $historyData['user_id'] . '"');

        if ($userRewards) {
            $availableBalance = (float)$userRewards[0]->dr + (float)$userRewards[0]->sr + (float)$userRewards[0]->mr;

            if ($availableBalance >= $historyData['amount']) {
                $historyData['after_rewords_balance'] = $availableBalance - $data['amount'];

                if ((float)$userRewards[0]->dr >= $historyData['amount']) {
                    $afterDr = (float)$userRewards[0]->dr - $historyData['amount'];
                    $result = $this->saveRewardsAndHistory($historyData['user_id'], $historyData, ['dr' => $afterDr]);
                } elseif ((float)$userRewards[0]->sr >= $historyData['amount']) {
                    $afterSr = (float)$userRewards[0]->sr - $historyData['amount'];
                    $result = $this->saveRewardsAndHistory($historyData['user_id'], $historyData, ['sr' => $afterSr]);
                } elseif ((float)$userRewards[0]->mr >= $historyData['amount']) {
                    $afterMr = (float)$userRewards[0]->mr - $historyData['amount'];
                    $result = $this->saveRewardsAndHistory($historyData['user_id'], $historyData, ['mr' => $afterMr]);
                } else {
                    $afterDr = $historyData['amount'] - (float)$userRewards[0]->dr;
                    $afterSr = (float)$userRewards[0]->sr - $afterDr;
                    if ($afterSr >= 0) {
                        $rewardsData = ['dr' => 0, 'sr' => $afterSr];
                        $result = $this->saveRewardsAndHistory($historyData['user_id'], $historyData, $rewardsData);
                    } else {
                        $afterMr = (float)$userRewards[0]->mr - abs($afterSr);
                        if ($afterMr >= 0) {
                            $rewardsData = ['dr' => 0, 'sr' => 0, 'mr' => $afterMr];
                            $result = $this->saveRewardsAndHistory($historyData['user_id'], $historyData, $rewardsData);
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function saveRewardsAndHistory(string $userId, array $historyData, array $rewardsData)
    {
        global $wpdb;
        $result = $wpdb->insert("{$wpdb->prefix}mlm_rewards_history", $historyData, ['%s', '%f', '%f']);

        if ($result) {
            $result = $wpdb->update("{$wpdb->prefix}mlm_rewards", $rewardsData, ['mlm_user_id' => $userId], ['%f', '%f', '%f']);
        }

        return $result;
    }

    public function updateOrCreateUserPhone($userId, $phone)
    {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}usermeta WHERE meta_key = 'digits_phone' is not null and user_id = " . $userId . "  LIMIT 1";
        $get = $wpdb->get_results($sql, 'ARRAY_A');

        if ($get && $get[0]['umeta_id']) {
            $status = $wpdb->update("{$wpdb->prefix}usermeta", ['meta_key' => 'digits_phone', 'meta_value' => $phone], ['umeta_id' => $get[0]['umeta_id']]);
        } else {
            $status = $wpdb->insert("{$wpdb->prefix}usermeta", ['meta_key' => 'digits_phone', 'meta_value' => $phone], ['user_id' => $userId]);
        }

        return $status;
    }

    public function getUserRewardNotification(string $userUniqueId)
    {
        global $wpdb;
        $sql = "SELECT message FROM {$wpdb->prefix}mlm_reward_notification  WHERE unique_id = '{$userUniqueId}'";
        $result = $wpdb->get_var($sql);

        return $result;
    }

    public function getCity()
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}mlm_city";
        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ? $result : [];
    }
}