<?php

class RegisterUser
{
    public function register($userId, string $uniqueId, string $firstName, string $lastName, string $userPhone, int $cityId, string $sponsorId, $email): array
    {
        $result = ['status' => false, 'message' => __('Success', 'marketing')];

        try {
            global $wpdb;

            if ($this->getUserByPhone($userPhone)) {
                $result['message'] = __('Phone already exits', 'marketing');
                return $result;
            }

            if (!$userId) {
                var_dump('Create wordpress user');
                $userId = $this->createWordpressUser($userPhone, $firstName, $lastName, $email);
                $uniqueId = 'USER' . $userId;
            }

            $insert_data = [
                'user_id' => $userId,
                'unique_id' => $uniqueId,
                'user_name' => $lastName . ' ' . $firstName,
                'user_phone' => $userPhone,
                'sponsor_id' => $sponsorId,
                'rank' => '0',
                'role' => 'distributor',
                'date' => strtotime("now"),
                'city_id' => $cityId
            ];


            if (!$this->getMlmUser($userId)) {
                var_dump('Create mlm_users');

                $wpdb->insert("{$wpdb->prefix}mlm_users", $insert_data);
            }

            if (!$this->getRewards($uniqueId)) {
                var_dump('Create user_rewards');
                $insertedStatus = $wpdb->insert("{$wpdb->prefix}mlm_rewards", ['mlm_user_id' => $uniqueId]);
                $result['status'] = (bool)$insertedStatus;
            }

            return $result;
        } catch (Exception $exception) {
            var_dump($exception);
        }

        return $result;
    }

    private function createWordpressUser(string $phone, $firstName, string $lastName, $email) {
        // Генерируем случайный пароль
        $random_password = wp_generate_password(12, false);
        
        $user_data = [
            'user_login' => $phone,
            'user_phone' => $phone,
            'user_pass' => $random_password, // Используем сгенерированный пароль
            'user_email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'nickname' => $firstName,
            'role' => 'distributor',
        ];

        $userId = wp_insert_user($user_data);
        
        // Отправляем пользователю email с паролем
        wp_new_user_notification($userId, null, 'both');
        
        var_dump('Wordpress user id = ' . $userId);

        return $userId;
    }

    private function getUserByPhone($phone)
    {
        global $wpdb;
        $sql = "SELECT id FROM {$wpdb->prefix}users WHERE user_login = '{$phone}'";

        return $wpdb->get_var($sql);
    }

    private function getMlmUser($userId)
    {
        global $wpdb;
        $sql = "SELECT id FROM {$wpdb->prefix}mlm_users WHERE user_id = '{$userId}'";

        return $wpdb->get_var($sql);
    }

    private function getRewards($userUniqueId)
    {
        global $wpdb;
        $sql = "SELECT id FROM {$wpdb->prefix}mlm_rewards WHERE mlm_user_id = '{$userUniqueId}'";

        return $wpdb->get_var($sql);
    }
}