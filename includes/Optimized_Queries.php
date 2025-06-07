<?php

class Optimized_Queries {
    
    /**
     * Получает пользователей с их спонсорами и наградами одним запросом
     */
    public static function get_users_with_rewards($limit = 100, $offset = 0) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        
        $sql = "SELECT 
                u.id, u.unique_id, u.user_name, u.user_phone, u.rank, u.sponsor_id,
                u.city_id, u.date, u.sr_at,
                r.pcc, r.scc, r.dr, r.sr, r.mr, r.br, r.br_car,
                s.user_name as sponsor_name,
                c.name as city_name
            FROM {$prefix}mlm_users u
            LEFT JOIN {$prefix}mlm_rewards r ON u.unique_id = r.mlm_user_id
            LEFT JOIN {$prefix}mlm_users s ON u.sponsor_id = s.unique_id
            LEFT JOIN {$prefix}mlm_city c ON u.city_id = c.id
            WHERE u.role = 'distributor'
            ORDER BY u.id DESC
            LIMIT %d OFFSET %d";
            
        return $wpdb->get_results($wpdb->prepare($sql, $limit, $offset), ARRAY_A);
    }
    
    /**
     * Получает транзакции с информацией о пользователе
     */
    public static function get_transactions_with_users($limit = 100, $offset = 0) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        
        $sql = "SELECT 
                t.id, t.amount, t.date, t.post_id,
                u.unique_id, u.user_name, u.rank
            FROM {$prefix}mlm_transactions t
            INNER JOIN {$prefix}mlm_users u ON t.tran_user_id = u.unique_id
            WHERE u.role = 'distributor'
            ORDER BY t.date DESC
            LIMIT %d OFFSET %d";
            
        return $wpdb->get_results($wpdb->prepare($sql, $limit, $offset), ARRAY_A);
    }
    
    /**
     * Получает детей пользователя с оптимизированным запросом
     */
    public static function get_user_children_optimized($user_id, $max_depth = 10) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        
        // Ограничиваем глубину рекурсии для безопасности
        $sql = "WITH RECURSIVE user_tree AS (
            SELECT u.*, r.pcc, r.scc, r.dr, r.sr, r.mr, 0 as depth
            FROM {$prefix}mlm_users u
            LEFT JOIN {$prefix}mlm_rewards r ON u.unique_id = r.mlm_user_id
            WHERE u.sponsor_id = %s
            
            UNION ALL
            
            SELECT u.*, r.pcc, r.scc, r.dr, r.sr, r.mr, ut.depth + 1
            FROM {$prefix}mlm_users u
            LEFT JOIN {$prefix}mlm_rewards r ON u.unique_id = r.mlm_user_id
            INNER JOIN user_tree ut ON u.sponsor_id = ut.unique_id
            WHERE ut.depth < %d
        )
        SELECT * FROM user_tree ORDER BY depth, id";
        
        return $wpdb->get_results($wpdb->prepare($sql, $user_id, $max_depth), ARRAY_A);
    }
    
    /**
     * Получает сумму транзакций для списка пользователей
     */
    public static function get_transactions_sum_bulk($user_ids, $start_date, $end_date) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        
        if (empty($user_ids)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($user_ids), '%s'));
        
        $sql = "SELECT 
                tran_user_id,
                SUM(amount) as total_amount,
                COUNT(*) as transaction_count
            FROM {$prefix}mlm_transactions
            WHERE tran_user_id IN ($placeholders)
                AND FROM_UNIXTIME(date, '%%Y-%%m-%%d') >= %s
                AND FROM_UNIXTIME(date, '%%Y-%%m-%%d') <= %s
            GROUP BY tran_user_id";
        
        $params = array_merge($user_ids, [$start_date, $end_date]);
        $results = $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
        
        // Преобразуем в ассоциативный массив для быстрого доступа
        $sums = [];
        foreach ($results as $row) {
            $sums[$row['tran_user_id']] = [
                'total' => $row['total_amount'],
                'count' => $row['transaction_count']
            ];
        }
        
        return $sums;
    }
    
    /**
     * Batch update для наград
     */
    public static function update_rewards_batch($updates) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        
        if (empty($updates)) {
            return true;
        }
        
        // Группируем обновления по типу
        $dr_updates = [];
        $sr_updates = [];
        $mr_updates = [];
        $pcc_updates = [];
        $scc_updates = [];
        
        foreach ($updates as $user_id => $fields) {
            if (isset($fields['dr'])) $dr_updates[$user_id] = $fields['dr'];
            if (isset($fields['sr'])) $sr_updates[$user_id] = $fields['sr'];
            if (isset($fields['mr'])) $mr_updates[$user_id] = $fields['mr'];
            if (isset($fields['pcc'])) $pcc_updates[$user_id] = $fields['pcc'];
            if (isset($fields['scc'])) $scc_updates[$user_id] = $fields['scc'];
        }
        
        // Выполняем batch update для каждого поля
        $success = true;
        
        if (!empty($dr_updates)) {
            $success &= self::batch_update_field('dr', $dr_updates);
        }
        if (!empty($sr_updates)) {
            $success &= self::batch_update_field('sr', $sr_updates);
        }
        if (!empty($mr_updates)) {
            $success &= self::batch_update_field('mr', $mr_updates);
        }
        if (!empty($pcc_updates)) {
            $success &= self::batch_update_field('pcc', $pcc_updates);
        }
        if (!empty($scc_updates)) {
            $success &= self::batch_update_field('scc', $scc_updates);
        }
        
        return $success;
    }
    
    /**
     * Helper для batch update одного поля
     */
    private static function batch_update_field($field, $values) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        
        $cases = [];
        $user_ids = [];
        
        foreach ($values as $user_id => $value) {
            $cases[] = $wpdb->prepare("WHEN %s THEN %f", $user_id, $value);
            $user_ids[] = $user_id;
        }
        
        if (empty($cases)) {
            return true;
        }
        
        $placeholders = implode(',', array_fill(0, count($user_ids), '%s'));
        
        $sql = "UPDATE {$prefix}mlm_rewards 
                SET $field = CASE mlm_user_id " . implode(' ', $cases) . " END
                WHERE mlm_user_id IN ($placeholders)";
        
        return $wpdb->query($wpdb->prepare($sql, $user_ids)) !== false;
    }
    
    /**
     * Получает историю рангов с пагинацией
     */
    public static function get_rank_history($user_id = null, $limit = 50, $offset = 0) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        
        $sql = "SELECT 
                ur.*, 
                u.user_name,
                r.name as rank_name
            FROM {$prefix}mlm_users_rank ur
            INNER JOIN {$prefix}mlm_users u ON ur.unique_id = u.unique_id
            LEFT JOIN {$prefix}mlm_rank r ON ur.rank_id = r.id";
        
        if ($user_id) {
            $sql .= $wpdb->prepare(" WHERE ur.unique_id = %s", $user_id);
        }
        
        $sql .= " ORDER BY ur.created_at DESC";
        $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $limit, $offset);
        
        return $wpdb->get_results($sql, ARRAY_A);
    }
}