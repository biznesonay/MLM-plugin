<?php

class Plugin_Optimizer_Integration {
    
    /**
     * Инициализация оптимизаций
     */
    public static function init() {
        // Добавляем индексы при активации плагина
        register_activation_hook(__FILE__, [__CLASS__, 'activate_optimizations']);
        
        // Заменяем старые функции на оптимизированные
        add_action('init', [__CLASS__, 'replace_functions']);
        
        // Добавляем CRON задачи для оптимизации
        add_action('init', [__CLASS__, 'schedule_optimization_tasks']);
        
        // Оптимизация AJAX запросов
        add_action('init', [__CLASS__, 'optimize_ajax_handlers']);
    }
    
    /**
     * Активация оптимизаций
     */
    public static function activate_optimizations() {
        require_once plugin_dir_path(__FILE__) . 'Database_Optimizer.php';
        
        // Добавляем индексы
        Database_Optimizer::add_indexes();
        
        // Оптимизируем таблицы
        Database_Optimizer::optimize_tables();
        
        // Добавляем опцию версии оптимизации
        update_option('mlm_optimization_version', '1.0');
    }
    
    /**
     * Замена функций на оптимизированные
     */
    public static function replace_functions() {
        // Проверяем версию оптимизации
        $current_version = get_option('mlm_optimization_version', '0');
        
        if (version_compare($current_version, '1.0', '<')) {
            self::activate_optimizations();
        }
        
        // Загружаем оптимизированные классы
        require_once plugin_dir_path(__FILE__) . 'Optimized_Queries.php';
        require_once plugin_dir_path(__FILE__) . '../services/Optimized_RankReward.php';
    }
    
    /**
     * Планирование задач оптимизации
     */
    public static function schedule_optimization_tasks() {
        // Еженедельная оптимизация таблиц
        if (!wp_next_scheduled('mlm_optimize_tables')) {
            wp_schedule_event(time(), 'weekly', 'mlm_optimize_tables');
        }
        
        add_action('mlm_optimize_tables', [__CLASS__, 'run_table_optimization']);
    }
    
    /**
     * Запуск оптимизации таблиц
     */
    public static function run_table_optimization() {
        require_once plugin_dir_path(__FILE__) . 'Database_Optimizer.php';
        Database_Optimizer::optimize_tables();
    }
    
    /**
     * Оптимизация AJAX обработчиков
     */
    public static function optimize_ajax_handlers() {
        // Заменяем старые обработчики на оптимизированные
        remove_action('wp_ajax_circulation_commodity', 'circulationCommodity');
        remove_action('wp_ajax_circulation_commodity', 'optimized_circulationCommodity');
        
        add_action('wp_ajax_circulation_commodity', [__CLASS__, 'optimized_circulationCommodity']);
    }
    
    /**
     * Оптимизированный обработчик для commodity circulation
     */
    public static function optimized_circulationCommodity() {
        // Загружаем необходимые файлы
        include_once plugin_dir_path(__FILE__) . '../services/Optimized_RankReward.php';
        
        // Используем оптимизированный класс
        $rankCalc = new Optimized_RankReward();
        $result = $rankCalc->calculate($_POST['mlm_circulation_commodity'], $_POST['mlm_distributor_id']);
        
        wp_send_json($result);
    }
}

// Модификация основного файла плагина для использования оптимизаций
// Добавьте эти строки в mlm-marketing.php после определения основных функций:

// Загрузка оптимизаций
require_once plugin_dir_path(__FILE__) . 'includes/Plugin_Optimizer_Integration.php';
Plugin_Optimizer_Integration::init();

// Обновленная функция для получения дистрибьюторов с пагинацией
function get_distributors_optimized($page = 1, $per_page = 50) {
    require_once plugin_dir_path(__FILE__) . 'includes/Optimized_Queries.php';
    
    $offset = ($page - 1) * $per_page;
    return Optimized_Queries::get_users_with_rewards($per_page, $offset);
}

// Обновленная функция для получения транзакций с пагинацией
function get_transactions_optimized($page = 1, $per_page = 50) {
    require_once plugin_dir_path(__FILE__) . 'includes/Optimized_Queries.php';
    
    $offset = ($page - 1) * $per_page;
    return Optimized_Queries::get_transactions_with_users($per_page, $offset);
}