<?php

class Database_Optimizer {
    
    /**
     * Добавляет индексы для оптимизации производительности
     */
    public static function add_indexes() {
        global $wpdb;
        $prefix = $wpdb->prefix;
        
        // Индексы для mlm_users
        $wpdb->query("ALTER TABLE {$prefix}mlm_users 
            ADD INDEX idx_sponsor_id (sponsor_id),
            ADD INDEX idx_user_id (user_id),
            ADD INDEX idx_rank (rank),
            ADD INDEX idx_city_id (city_id),
            ADD INDEX idx_role_rank (role, rank),
            ADD INDEX idx_date (date)");
        
        // Индексы для mlm_transactions
        $wpdb->query("ALTER TABLE {$prefix}mlm_transactions 
            ADD INDEX idx_tran_user_id (tran_user_id),
            ADD INDEX idx_date (date),
            ADD INDEX idx_post_id (post_id),
            ADD INDEX idx_user_date (tran_user_id, date)");
        
        // Индексы для mlm_rewards
        $wpdb->query("ALTER TABLE {$prefix}mlm_rewards 
            ADD INDEX idx_mlm_user_id (mlm_user_id),
            ADD INDEX idx_br (br),
            ADD INDEX idx_scc_at (scc_at)");
        
        // Индексы для mlm_rewards_history
        $wpdb->query("ALTER TABLE {$prefix}mlm_rewards_history 
            ADD INDEX idx_user_id (user_id),
            ADD INDEX idx_created_at (created_at),
            ADD INDEX idx_user_created (user_id, created_at)");
        
        // Индексы для mlm_users_rank
        $wpdb->query("ALTER TABLE {$prefix}mlm_users_rank 
            ADD INDEX idx_unique_id (unique_id),
            ADD INDEX idx_rank_id (rank_id),
            ADD INDEX idx_created_at (created_at),
            ADD INDEX idx_unique_rank_created (unique_id, rank_id, created_at)");
    }
    
    /**
     * Оптимизирует существующие таблицы
     */
    public static function optimize_tables() {
        global $wpdb;
        $prefix = $wpdb->prefix;
        
        $tables = [
            'mlm_users',
            'mlm_transactions',
            'mlm_rewards',
            'mlm_rewards_history',
            'mlm_users_rank'
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("OPTIMIZE TABLE {$prefix}{$table}");
        }
    }
}