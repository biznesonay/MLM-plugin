<?php

class Optimized_RankReward extends RankReward {
    
    private $batch_updates = [];
    private $cache = [];
    
    /**
     * Оптимизированный расчет с batch операциями
     */
    public function calculate($balance, $userUniqueId): array {
        $this->batch_updates = [];
        $this->cache = [];
        
        // Получаем все необходимые данные одним запросом
        $data = $this->preloadData($userUniqueId);
        
        if (!$data) {
            return ['status' => false, 'message' => 'User not found'];
        }
        
        $result = ['status' => false, 'message' => translate('Error to create')];
        
        // Начинаем транзакцию
        global $wpdb;
        $wpdb->query('START TRANSACTION');
        
        try {
            // Основные расчеты
            $curUserWithReward = $data['user'];
            $userRank = (int)$curUserWithReward['rank'];
            $clearBalance = $balance;
            $balance = $curUserWithReward['pcc'] + $balance;
            
            $parents = $data['parents'];
            $children = $data['children'];
            $childrenFirstHashArray = RankHelper::firstLevelRankHashArray($children);
            
            $curUserScc = $curUserWithReward['scc'];
            
            // Расчет нового ранга
            $rank = $this->getRank($userRank, $balance, $curUserScc, $childrenFirstHashArray);
            
            if ($rank && $userRank != $rank) {
                $bonusPccScc = (float)$curUserWithReward['pcc'] + (float)$curUserWithReward['scc'];
                RankDB::saveUserRank($userUniqueId, $rank, $curUserWithReward['user_id'], $bonusPccScc);
            }
            
            // Создаем транзакцию
            $status = RankDB::createTransaction($userUniqueId, $clearBalance);
            
            if ($status) {
                // Добавляем обновление PCC в batch
                $this->addBatchUpdate($userUniqueId, ['pcc' => $balance]);
                
                // Расчет всех вознаграждений
                $this->calculateAllRewardsBatch($userUniqueId, $userRank, $clearBalance, $parents, $curUserWithReward);
                
                // Выполняем все batch обновления
                $this->executeBatchUpdates();
                
                // Расчет BR
                $this->calculateBrAndBrCar($userUniqueId, $userRank);
                
                $wpdb->query('COMMIT');
                
                $result['status'] = true;
                $result['message'] = translate('Successfully created');
            } else {
                $wpdb->query('ROLLBACK');
            }
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            $result['message'] = $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Предзагрузка всех необходимых данных
     */
    private function preloadData($userUniqueId) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        
        // Получаем данные пользователя с наградами
        $sql = "SELECT u.*, r.* 
                FROM {$prefix}mlm_users u
                LEFT JOIN {$prefix}mlm_rewards r ON u.unique_id = r.mlm_user_id
                WHERE u.unique_id = %s";
        
        $user = $wpdb->get_row($wpdb->prepare($sql, $userUniqueId), ARRAY_A);
        
        if (!$user) {
            return null;
        }
        
        // Получаем родителей одним запросом
        $parents = RankDB::getUserPatents($userUniqueId);
        
        // Получаем детей одним запросом
        $children = RankDB::getUserChildren($userUniqueId);
        
        return [
            'user' => $user,
            'parents' => $parents,
            'children' => $children
        ];
    }
    
    /**
     * Расчет всех вознаграждений с batch операциями
     */
    private function calculateAllRewardsBatch($userUniqueId, $userRank, $clearBalance, $parents, $curUserWithReward) {
        // SCC для всех родителей
        foreach ($parents as $parent) {
            $newScc = $clearBalance + (float)$parent['scc'];
            $this->addBatchUpdate($parent['unique_id'], ['scc' => $newScc]);
        }
        
        // DR для первого родителя
        $this->calculateDrBatch($userRank, $parents, $clearBalance);
        
        // SR для подходящих родителей
        $this->calculateSrBatch($parents, $clearBalance, $curUserWithReward);
        
        // MR для подходящих родителей
        $this->calculateMrBatch($userRank, $clearBalance, array_reverse($parents));
    }
    
    /**
     * Batch расчет DR
     */
    private function calculateDrBatch($setUserRank, $parents, $clearBalance) {
        $closerFirstParent = $parents ? end($parents) : false;
        
        if (!$closerFirstParent) {
            return;
        }
        
        $parentUniqueId = $closerFirstParent['unique_id'];
        $parentRank = (int)$closerFirstParent['rank'];
        
        // Проверка sr_at для рангов 4+
        if ($parentRank >= 4) {
            $parentSrAt = $closerFirstParent['sr_at'] ?? null;
            $now = date('Y-m-d');
            
            if (!$parentSrAt || $parentSrAt < $now) {
                return;
            }
        }
        
        $curUserRewardDr = Reward::countDr($setUserRank, $parentRank, $clearBalance);
        
        if ($curUserRewardDr > 0) {
            $newDr = (float)$closerFirstParent['dr'] + $curUserRewardDr;
            $this->addBatchUpdate($parentUniqueId, ['dr' => $newDr]);
        }
    }
    
    /**
     * Batch расчет SR
     */
    private function calculateSrBatch($parents, $clearBalance, $curUserWithReward) {
        $lastParent = end($parents);
        if (!$lastParent) {
            $lastParent['unique_id'] = null;
        }
        
        if ($parents) {
            $parents[] = $curUserWithReward;
        }
        
        $usersRewardSr = Reward::countSr($clearBalance, $parents, $lastParent);
        
        foreach ($usersRewardSr as $parentId => $item) {
            // Проверка sr_at
            $parentData = null;
            foreach ($parents as $parent) {
                if ($parent['unique_id'] == $parentId) {
                    $parentData = $parent;
                    break;
                }
            }
            
            if ($parentData && (int)$parentData['rank'] >= 4) {
                $srAt = $parentData['sr_at'] ?? null;
                $now = date('Y-m-d');
                
                if (!$srAt || $srAt < $now) {
                    continue;
                }
            }
            
            $newSr = $item['pre_sr'] + $item['sr'];
            $this->addBatchUpdate($parentId, ['sr' => $newSr]);
        }
    }
    
    /**
     * Batch расчет MR
     */
    private function calculateMrBatch($setUserRank, $clearBalance, $parents) {
        $mrWithParent = Reward::countMr($setUserRank, $clearBalance, $parents);
        
        if ($mrWithParent['mr'] && $mrWithParent['parent']) {
            $parentUniqueId = $mrWithParent['parent']['data']['unique_id'] ?? null;
            $parentRank = (int)$mrWithParent['parent']['data']['rank'] ?? 0;
            
            // Проверка sr_at для рангов 4+
            if ($parentRank >= 4) {
                $parentSrAt = $mrWithParent['parent']['data']['sr_at'] ?? null;
                $now = date('Y-m-d');
                
                if (!$parentSrAt || $parentSrAt < $now) {
                    return;
                }
            }
            
            if ($parentUniqueId) {
                $parentRewardMr = (float)$mrWithParent['parent']['data']['mr'];
                $newMr = $parentRewardMr + $mrWithParent['mr'];
                $this->addBatchUpdate($parentUniqueId, ['mr' => $newMr]);
            }
        }
    }
    
    /**
     * Добавляет обновление в batch
     */
    private function addBatchUpdate($userId, $fields) {
        if (!isset($this->batch_updates[$userId])) {
            $this->batch_updates[$userId] = [];
        }
        
        foreach ($fields as $field => $value) {
            $this->batch_updates[$userId][$field] = $value;
        }
    }
    
    /**
     * Выполняет все batch обновления
     */
    private function executeBatchUpdates() {
        if (empty($this->batch_updates)) {
            return true;
        }
        
        return Optimized_Queries::update_rewards_batch($this->batch_updates);
    }
    
    /**
     * Массовый расчет рангов
     */
    public function calculateRanksBatch($userIds) {
        global $wpdb;
        $wpdb->query('START TRANSACTION');
        
        try {
            $count = 0;
            
            foreach ($userIds as $userId) {
                $status = $this->calculateRank($userId);
                if ($status) {
                    $count++;
                }
            }
            
            $wpdb->query('COMMIT');
            
            return ['success' => true, 'count' => $count];
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}