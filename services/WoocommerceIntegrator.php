<?php

class WoocommerceIntegrator
{
    private $sponsorId;
    private $registryDistributorProducts;
    private $registry;

    /**
     * @param string $sponsorId
     * @param array $registryDistributorProducts
     * @param RegisterUser $registry
     */
    public function __construct($sponsorId, $registryDistributorProducts, $registry)
    {
        $this->sponsorId = $sponsorId;
        $this->registryDistributorProducts = $registryDistributorProducts;
        $this->registry = $registry;
    }


    public function integrate(): int
    {
        return $this->integratePosts();
    }

    private function integratePosts(): int
    {
        $count = 0;
        try {
            $posts = $this->getPosts();

            if (!$posts) {
                return 0;
            }

            $lastPostId = 0;

            foreach ($posts as $post) {
                $lastPostId = $post['id'];

                $userContent = $this->getUserContentData($post['id']);
                $userUniqueId = 'USER' . $userContent['user_id'];

                if ($this->isDistributorProduct($post['product_name']) && !$this->isDistributorExist($userContent['user_id'])) {
                    var_dump('registryDistributor');

                    $this->registryDistributor($userContent['user_id'], $userUniqueId, $userContent['fist_name'], $userContent['last_name'], $userContent['phone'], $userContent['city_id'], $this->sponsorId, $userContent['email']);

                } else {
                    var_dump('registryCommodityCirculation');

                    //$this->saveTransaction($userUniqueId, $userContent['total_amount'], $post['id']);

                    $rank = new RankReward();
                    $rank->calculate($userContent['total_amount'], $userUniqueId);
                }

                $count++;
            }

            $this->saveLastPostId($lastPostId);
        } catch (Throwable $exception) {
            var_dump($exception);
        }

        return $count;
    }

    private function isDistributorExist(int $id)
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT id FROM {$prefix}mlm_users u WHERE u.user_id = {$id}";

        return $wpdb->get_var($sql);
    }

    private function registryDistributor(int $userId, string $uniqueId, string $firstName, string $lastName, string $userPhone, int $cityId, string $sponsorId, $email): array
    {
        return $this->registry->register($userId, $uniqueId, $firstName, $lastName, $userPhone, $cityId, $sponsorId, $email);
    }


    private function saveTransaction(string $userUniqueId, $balance, int $postId)
    {
        if ($this->getTransationByPostId($postId)) {
            return false;
        }

        var_dump('Create transaction, Increase reword pcc, user = ' . $userUniqueId);

        $reward = $this->getUserReword($userUniqueId);
        $totalBalance = isset($reward['pcc']) ? ($balance + $reward['pcc']) : $balance;

        $this->saveReward($userUniqueId, $totalBalance);

        return $this->createTransaction($userUniqueId, $balance, $postId);
    }

    private function saveReward(string $userUniqueId, $balance)
    {
        global $wpdb;

        return $wpdb->update("{$wpdb->prefix}mlm_rewards", ['pcc' => $balance], ['mlm_user_id' => $userUniqueId]);
    }

    private function getUserReword(string $uniqueUserId)
    {
        global $wpdb;
        $sql = "select * from {$wpdb->prefix}mlm_rewards where mlm_user_id = '{$uniqueUserId}'";

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ? $result[0] : [];

    }

    private function getTransationByPostId(int $postId)
    {
        global $wpdb;

        $sql = "select id from {$wpdb->prefix}mlm_transactions where post_id = {$postId}";

        return $wpdb->get_var($sql);
    }

    private function createTransaction(string $userUniqueId, $balance, int $postId)
    {
        if (!$balance) return false;

        global $wpdb;

        $insert_data = ['tran_user_id' => $userUniqueId, 'amount' => $balance, 'post_id' => $postId, 'date' => strtotime("now")];

        return $wpdb->insert("{$wpdb->prefix}mlm_transactions", $insert_data);
    }


    private function isDistributorProduct(string $productName): bool
    {
        $productName = mb_strtolower($productName);

        return in_array($productName, $this->registryDistributorProducts);
    }

    private function getPosts(): array
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $lastPostId = $this->getLasPostId();

        $sql = "SELECT p.id, i.order_item_name product_name FROM {$prefix}posts p
                left join {$prefix}woocommerce_order_items i on i.order_id = p.id
                WHERE p.post_type = 'shop_order' and p.post_status = 'wc-completed' and 
                      i.order_item_type ='line_item' and p.id > {$lastPostId}";

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result;
    }

    private function getUserContentData(int $postId): array
    {
        $data = ['post_id' => null, 'total_amount' => null, 'user_id' => null, 'fist_name' => null, 'last_name' => null, 'phone' => null, 'city_id' => null, 'email'];
        $meta = $this->getMetaData($postId);

        if (!$meta) {
            return $data;
        }

        $hashCity = $this->getCityHash();
        $data['post_id'] = $postId;
        $lastName = null;
        $firstName = null;

        foreach ($meta as $item) {
            if ('_customer_user' == $item['meta_key']) {
                $data['user_id'] = $item['meta_value'];
            } elseif ('_order_total' == $item['meta_key']) {
                $data['total_amount'] = $item['meta_value'];
            } elseif ('_billing_first_name' == $item['meta_key']) {
                $data['fist_name'] = $item['meta_value'];
            } elseif ('_billing_last_name' == $item['meta_key']) {
                $data['last_name'] = $item['meta_value'];
            } elseif ('_billing_email' == $item['meta_key']) {
                $data['email'] = $item['meta_value'];
            } elseif ('_billing_city' == $item['meta_key']) {
                $cityName = $item['meta_value'];

                $cityId = $hashCity[mb_strtolower($cityName)];
                if ($cityId) {
                    $data['city_id'] = $cityId;
                }
            } elseif ('_billing_phone' == $item['meta_key']) {
                $phone = $item['meta_value'];
                if (substr($phone, 0, 1) == '8') {
                    $data['phone'] = '+7' . substr($phone, 1, strlen($phone));
                } else {
                    $data['phone'] = $phone;
                }

            }
        }

        $data['name'] = ($lastName . ($lastName ? ' ' : '')) . $firstName;

        return $data;
    }

    private function getMetaData(int $postId): array
    {
        global $wpdb;
            $sql = "select * from {$wpdb->prefix}postmeta m where m.post_id = {$postId}";
        return $wpdb->get_results($sql, 'ARRAY_A');
    }

    public function getCityHash(): array
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}mlm_city";
        $cities = $wpdb->get_results($sql, 'ARRAY_A');

        $hashCity = [];

        if ($cities) {
            foreach ($cities as $city) {
                $hashCity[mb_strtolower($city['name'])] = $city['id'];
            }
        }

        return $hashCity;
    }

    private function getLasPostId()
    {
        global $wpdb;
        $prefix = $wpdb->prefix;

        $sql = "SELECT option_value FROM {$prefix}mlm_options where option_key = 'last_post_id'";

        return $wpdb->get_var($sql);
    }

    private function saveLastPostId(string $value)
    {
        global $wpdb;

        return $wpdb->update("{$wpdb->prefix}mlm_options", ['option_value' => $value], ['option_key' => 'last_post_id']);
    }

}