<?php
/*
Plugin Name: MLM Marketing
Plugin URI:  https://biznesonay.kz
Description: This plugin for multi lavel marketing and rank basis reward.
Version:     1.0.8.8
Author:      BiznesOnay
Author URI:  https://biznesonay.kz
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/*
 * Text Domain: marketing
 * Domain Path: /languages
 */

require_once(ABSPATH . 'wp-config.php');

// Установка часового пояса при загрузке плагина
add_action('init', function() {
    date_default_timezone_set('Asia/Almaty');
    
    // Установка часового пояса для MySQL
    global $wpdb;
    $wpdb->query("SET time_zone = '+06:00'");
});

// Загрузка текстового домена для переводов
function mlm_load_textdomain() {
    load_plugin_textdomain('marketing', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'mlm_load_textdomain');

add_action('admin_menu', 'my_admin_menu');

function my_admin_menu()
{
    add_menu_page('MLM Marketing', 'MLM Marketing', 'manage_options', 'mlm-overview', 'mlm_overview', 'dashicons-analytics', 2);
    add_submenu_page('mlm-overview', __('Overview', 'marketing'), __('Overview', 'marketing'), 'manage_options', 'mlm-overview');
    add_submenu_page('mlm-overview', __('Distributor Panel', 'marketing'), __('Distributor Panel', 'marketing'), 'manage_options', 'mlm-distributor-panel', 'distributor_panel');
    add_submenu_page('mlm-overview', __('Commodity Circulation Panel', 'marketing'), __('Circulation Panel', 'marketing'), 'manage_options', 'mlm-commodity-circulation-panel', 'commodity_circulation_panel');
    add_submenu_page('mlm-overview', __('Structure Panel', 'marketing'), __('Structure Panel', 'marketing'), 'manage_options', 'mlm-structure-panel', 'structure_panel');
    add_submenu_page('mlm-overview', __('Family Panel', 'marketing'), __('Family Panel', 'marketing'), 'manage_options', 'mlm-family-panel', 'family_tree');
    add_submenu_page('mlm-overview', __('Rewards History', 'marketing'), __('Rewards History', 'marketing'), 'manage_options', 'mlm-rewards-history-panel', 'rewards_history');
    add_submenu_page(
    'mlm-overview', 
    __('Settings', 'marketing'), 
    __('Settings', 'marketing'), 
    'manage_options', 
    'mlm-settings', 
    'mlm_settings_page'
);

function mlm_settings_page() {
    if (isset($_POST['submit'])) {
        update_option('mlm_default_sponsor', sanitize_text_field($_POST['default_sponsor']));
        update_option('mlm_auto_process_orders', isset($_POST['auto_process']) ? 'yes' : 'no');
        update_option('mlm_recaptcha_site_key', sanitize_text_field($_POST['recaptcha_site_key']));
        update_option('mlm_recaptcha_secret_key', sanitize_text_field($_POST['recaptcha_secret_key']));
        update_option('mlm_recaptcha_enabled', isset($_POST['recaptcha_enabled']) ? 'yes' : 'no');
        echo '<div class="notice notice-success"><p>Настройки сохранены!</p></div>';
    }
    
    $default_sponsor = get_option('mlm_default_sponsor', 'USER1');
    $auto_process = get_option('mlm_auto_process_orders', 'yes');
    $recaptcha_site_key = get_option('mlm_recaptcha_site_key', '');
    $recaptcha_secret_key = get_option('mlm_recaptcha_secret_key', '');
    $recaptcha_enabled = get_option('mlm_recaptcha_enabled', 'no');
    ?>
    <div class="wrap">
        <h1>MLM Настройки</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th>Спонсор по умолчанию</th>
                    <td><input type="text" name="default_sponsor" value="<?= $default_sponsor ?>"></td>
                </tr>
                <tr>
                    <th>Автоматически обрабатывать заказы</th>
                    <td><input type="checkbox" name="auto_process" <?= $auto_process == 'yes' ? 'checked' : '' ?>></td>
                </tr>
                <tr>
                    <th colspan="2"><h2>Настройки reCAPTCHA</h2></th>
                </tr>
                <tr>
                    <th>Включить reCAPTCHA</th>
                    <td><input type="checkbox" name="recaptcha_enabled" <?= $recaptcha_enabled == 'yes' ? 'checked' : '' ?>></td>
                </tr>
                <tr>
                    <th>Site Key</th>
                    <td>
                        <input type="text" name="recaptcha_site_key" value="<?= $recaptcha_site_key ?>" style="width: 350px;">
                        <p class="description">Получите ключи на <a href="https://www.google.com/recaptcha/admin" target="_blank">https://www.google.com/recaptcha/admin</a></p>
                    </td>
                </tr>
                <tr>
                    <th>Secret Key</th>
                    <td><input type="text" name="recaptcha_secret_key" value="<?= $recaptcha_secret_key ?>" style="width: 350px;"></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
//    add_submenu_page('mlm-overview', __('Parser', 'marketing'), __('Parser', 'marketing'), 'manage_options', 'parser', 'parser');
    add_submenu_page('mlm-overview', __('Date of Rank’s change', 'marketing'), __('Date of Rank’s change', 'marketing'), 'manage_options', 'rank', 'rank');
}

function create_plugin_database_table()
{

    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
    global $table_prefix, $wpdb;
    $userstable = 'mlm_users';
    $mlm_users_table = $table_prefix . "$userstable";

    $transactionstable = 'mlm_transactions';
    $mlm_transactions_table = $table_prefix . "$transactionstable";

    $rewardstable = 'mlm_rewards';
    $mlm_rewards_table = $table_prefix . "$rewardstable";

    $historyTable = 'mlm_rewards_history';
    $mlm_history_table = $table_prefix . "$historyTable";

    $rankTable = 'mlm_rank';
    $mlm_rank_table = $table_prefix . "$rankTable";

    $userRankTable = 'mlm_users_rank';
    $mlm_users_rank_table = $table_prefix . "$userRankTable";

    $args = array(
        'role' => 'administrator'
    );
    $users = get_users($args);

    if ($wpdb->get_var("show tables like '$mlm_users_table'") != $mlm_users_table) {

        $users_table_sql = "CREATE TABLE $mlm_users_table (
			    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			    city_id int(11) DEFAULT NULL,
			    user_id int(11) NOT NULL,
			    unique_id varchar(255) NOT NULL,
			    user_name varchar(255) NOT NULL,
			    user_phone varchar(45),
			    sponsor_id varchar(255) NULL,
			    rank int(11) DEFAULT 0,
			    role varchar(255),
			    sr_at date NULL DEFAULT NULL,
			    date int,
			    INDEX `Index__mlm_users__user_id` (`unique_id`),
			    INDEX `Index__mlm_users__sponsor_id` (`sponsor_id`)
			);";

        dbDelta($users_table_sql);

        foreach ($users as $user) {
            $users_insert_table_sql = "INSERT INTO $mlm_users_table (unique_id, user_id, user_name, sponsor_id, rank, role, date) VALUES ('USER" . $user->data->ID . "', {$user->data->ID}, '" . $user->data->display_name . "', '', '1', 'distributor', '" . strtotime("now") . "');";
            dbDelta($users_insert_table_sql);
        }

    }

    if ($wpdb->get_var("show tables like '$mlm_transactions_table'") != $mlm_transactions_table) {

        $transaction_table_sql = "CREATE TABLE $mlm_transactions_table (
			    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			    tran_user_id varchar(255) NOT NULL,
			    amount decimal NOT NULL,
			    post_id int(11) DEFAULT NULL,
			    date int,
			    INDEX `Index__mlm_transactions__user_id` (`tran_user_id`)
			);";

        dbDelta($transaction_table_sql);
    }

    if ($wpdb->get_var("show tables like '$mlm_rewards_table'") != $mlm_rewards_table) {

        $rewards_table_sql = "CREATE TABLE $mlm_rewards_table (
			    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			    mlm_user_id varchar(255) NOT NULL,
			    pcc decimal DEFAULT 0,
			    scc decimal DEFAULT 0,
			    scc_second decimal DEFAULT 0,
			    dr decimal DEFAULT 0,
			    sr decimal DEFAULT 0,
			    mr decimal DEFAULT 0,
			    br int(3) DEFAULT 0,
			    br_car int(3) DEFAULT 0,
			    scc_at datetime NULL DEFAULT NULL,
			    br_start_at datetime NULL DEFAULT NULL,
			    prev_br_balance DOUBLE NULL DEFAULT NULL,
			    INDEX `Index__mlm_rewards__user_id` (`mlm_user_id`)
			);";

        dbDelta($rewards_table_sql);

        foreach ($users as $user) {
            $rewards_insert_table_sql = "INSERT INTO $mlm_rewards_table (mlm_user_id) VALUES ('USER" . $user->data->ID . "');";
            dbDelta($rewards_insert_table_sql);
        }
    }

    if ($wpdb->get_var("show tables like '$mlm_history_table'") != $mlm_history_table) {

        $sql = "CREATE TABLE $mlm_history_table (
			    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			    user_id varchar(255) NOT NULL,
			    amount decimal DEFAULT 0 NOT NULL,
			    after_rewords_balance decimal DEFAULT 0,
			    is_regular_payment BOOLEAN DEFAULT false,
			    created_at TIMESTAMP NULL DEFAULT current_timestamp(),
			    INDEX `Index__mlm_rewards_history__user_id` (`user_id`)
			);";

        dbDelta($sql);
    }

    if ($wpdb->get_var("show tables like '$mlm_rank_table'") != $mlm_rank_table) {

        $sql = "CREATE TABLE $mlm_rank_table (
			    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			    name varchar(45) NOT NULL,
			    created_at TIMESTAMP NULL DEFAULT current_timestamp()
			);";

        dbDelta($sql);

        for ($i = 1; $i <= 9; $i++) {
            $rank_insert_table_sql = "INSERT INTO $mlm_rank_table (name) VALUES ('" . $i . "')";
            dbDelta($rank_insert_table_sql);
        }
    }

    if ($wpdb->get_var("show tables like '$mlm_users_rank_table'") != $mlm_users_rank_table) {

        $sql = "CREATE TABLE $mlm_users_rank_table (
			    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			    mlm_user_id int(11) NOT NULL,
			    unique_id varchar(255) NOT NULL,
			    rank_id int NOT NULL,
			    pcc_scc DOUBLE NULL,
			    created_at TIMESTAMP NULL DEFAULT current_timestamp(),
			    INDEX `Index__mlm_users_rank__unique_id` (`unique_id`)
			);";

        dbDelta($sql);
    }

    $brNotificationTable = $table_prefix . 'mlm_reward_notification';

    if ($wpdb->get_var("show tables like '$brNotificationTable'") != $brNotificationTable) {

        $sql = "CREATE TABLE $brNotificationTable (
			    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			    unique_id varchar(255) NOT NULL,
			    amount int NOT NULL,
			    message varchar(255) NUll,
			    created_at TIMESTAMP NULL DEFAULT current_timestamp(),
			    INDEX Index__mlm_reward_notification (unique_id)
			);";

        dbDelta($sql);
    }

    $reportTable = $table_prefix . 'mlm_report';
    if ($wpdb->get_var("show tables like '$reportTable'") != $reportTable) {
        $sql = "CREATE TABLE $reportTable (
			    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			    file_name varchar(255) NOT NULL,
			    file_path varchar(255) NUll,
			    type_id tinyint DEFAULT 1,
			    created_at TIMESTAMP NULL DEFAULT current_timestamp()
			);";

        dbDelta($sql);
    }

    $optionsTable = $table_prefix . 'mlm_options';
    if ($wpdb->get_var("show tables like '$optionsTable'") != $optionsTable) {
        $sql = "CREATE TABLE $optionsTable (
			    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			    option_key varchar(255) NOT NULL,
			    option_value varchar(255) NUll,
			    created_at TIMESTAMP NULL DEFAULT current_timestamp()
			);";

        dbDelta($sql);

        $insert_table_sql = "INSERT INTO $optionsTable (option_key, option_value) VALUES ('last_post_id', '0');";
        dbDelta($insert_table_sql);
    }

    $cityTable = $table_prefix . 'mlm_city';
    if ($wpdb->get_var("show tables like '$cityTable'") != $cityTable) {

        $sql = "CREATE TABLE $cityTable (
			    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			    name varchar(255) NOT NULL,
			    is_deleted boolean DEFAULT false,
			    created_at TIMESTAMP NULL DEFAULT current_timestamp()
			);";

        dbDelta($sql);

        $cities = [
            'Алматы',
            'Нур-Султан',
            'Шымкент',
            'Актобе',
            'Караганда',
            'Тараз',
            'Павлодар',
            'Усть-Каменогорск',
            'Семей',
            'Атырау',
            'Костанай',
            'Кызылорда',
            'Уральск',
            'Петропавловск',
            'Актау',
            'Темиртау',
            'Туркестан',
            'Кокшетау',
            'Талдыкорган',
            'Экибастуз',
            'Рудный',
            'Другой город',
        ];

        foreach ($cities as $city) {
            $users_insert_table_sql = "INSERT INTO $cityTable (name) VALUES ('{$city}');";
            dbDelta($users_insert_table_sql);
        }
    }
}

register_activation_hook(__FILE__, 'create_plugin_database_table');

register_activation_hook(__FILE__, 'add_profile_page');

function add_profile_page()
{
    $post = array(
        'post_content' => '[mlm-profile]',
        'post_title' => 'Profile',
        'post_status' => 'publish',
        'post_type' => 'page'
    );
    wp_insert_post($post);
}

register_activation_hook(__FILE__, 'add_login_page');

function add_login_page()
{
    $post = array(
        'post_content' => '[mlm-login]',
        'post_title' => 'Login',
        'post_status' => 'publish',
        'post_type' => 'page'
    );
    wp_insert_post($post);
}

register_activation_hook(__FILE__, 'add_register_page');

function add_register_page()
{
    $post = array(
        'post_content' => '[mlm-registration]',
        'post_title' => 'Register',
        'post_status' => 'publish',
        'post_type' => 'page'
    );
    wp_insert_post($post);
}


add_action('admin_enqueue_scripts', 'my_enqueued_assets');
function my_enqueued_assets()
{
    wp_enqueue_style('mlm_admin_css', plugin_dir_url(__FILE__) . 'assets/css/style.css', false, '1.0.0');
    wp_enqueue_style('mlm_fontawesome_css', plugin_dir_url(__FILE__) . 'assets/css/font-awesome.min.css', false, '4.7.0');

    wp_enqueue_style('sweetalert_css', plugin_dir_url(__FILE__) . 'assets/css/sweetalert2.min.css');

    wp_enqueue_style('semantic_css', plugin_dir_url(__FILE__) . 'assets/css/semantic.min.css');
    wp_enqueue_style('dataTables_semanticui', plugin_dir_url(__FILE__) . 'assets/css/dataTables.semanticui.min.css');

    wp_enqueue_script('jquery_dataTables', plugin_dir_url(__FILE__) . 'assets/js/jquery.dataTables.min.js');
    wp_enqueue_script('dataTables_semanticui', plugin_dir_url(__FILE__) . 'assets/js/dataTables.semanticui.min.js');
    wp_enqueue_script('semantic_js', plugin_dir_url(__FILE__) . 'assets/js/semantic.min.js');

    wp_enqueue_script('sweetalert_js', plugin_dir_url(__FILE__) . 'assets/js/sweetalert2.min.js');

    wp_enqueue_script('masked_input', plugin_dir_url(__FILE__) . 'assets/js/masked_input.min.js');
}

add_action('wp_enqueue_scripts', 'frontend_scripts');
function frontend_scripts()
{
    wp_enqueue_style('mlm_frontend', plugin_dir_url(__FILE__) . 'assets/css/mlm_frontend.css', false, '1.0.0');
    wp_enqueue_style('semantic_css', plugin_dir_url(__FILE__) . 'assets/css/semantic.min.css');
    wp_enqueue_style('mlm_fontawesome_css', plugin_dir_url(__FILE__) . 'assets/css/font-awesome.min.css', false, '4.7.0');
    wp_enqueue_script('jquery');
    wp_enqueue_script('semantic-js', plugin_dir_url(__FILE__) . 'assets/js/semantic.min.js', '', '2.3.1');
    wp_enqueue_script('jquery-validate', plugin_dir_url(__FILE__) . 'assets/js/jquery.validate.min.js', '', '1.16.0');
    wp_enqueue_style('dataTables-semanticui', plugin_dir_url(__FILE__) . 'assets/css/dataTables.semanticui.min.css');
    wp_enqueue_script('jquery-dataTables', plugin_dir_url(__FILE__) . 'assets/js/jquery.dataTables.min.js');
    wp_enqueue_script('dataTables_semanticui', plugin_dir_url(__FILE__) . 'assets/js/dataTables.semanticui.min.js');
}

add_action('after_setup_theme', 'add_role_function');

function add_role_function()
{
    $sponsor_roles_set = get_option('sponsor');
    $distributor_roles_set = get_option('distributor');
    if (!$sponsor_roles_set) {
        add_role('sponsor', 'Sponsor', array(
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => false,
            'upload_files' => false
        ));
        update_option('sponsor', true);
    }
    if (!$distributor_roles_set) {
        add_role('distributor', 'Distributor', array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'upload_files' => false
        ));
        update_option('distributor', true);
    }
}

include plugin_dir_path(__FILE__) . 'includes/Datatable_List.php';
include plugin_dir_path(__FILE__) . 'includes/Reward_Calculator.php';

function mlm_overview()
{
    include(plugin_dir_path(__FILE__) . 'templates/overview.php');
}

function distributor_panel()
{
    include(plugin_dir_path(__FILE__) . 'templates/distributor.php');
}

function commodity_circulation_panel()
{
    include(plugin_dir_path(__FILE__) . 'templates/circulation.php');
}

function structure_panel()
{
    include(plugin_dir_path(__FILE__) . 'templates/structure-p.php');
}

function family_tree()
{
    wp_enqueue_script('jquery351', plugin_dir_url(__FILE__) . 'assets/js/jquery-3.5.1.min.js', false, '3.5.1');
    wp_enqueue_script('d3', plugin_dir_url(__FILE__) . 'assets/js/d3.min.js', false, '3.0.0');
    wp_enqueue_script('dndTree', plugin_dir_url(__FILE__) . 'assets/js/dndTree.js', false, '1.0.0');
    wp_enqueue_style('dndTreeCss', plugin_dir_url(__FILE__) . 'assets/css/dndTree.css');
    include plugin_dir_path(__FILE__) . 'services/UserTree.php';
    include(plugin_dir_path(__FILE__) . 'templates/tree.php');
}

function rewards_history()
{
    include(plugin_dir_path(__FILE__) . 'templates/rewards-history.php');
}

function parser()
{
    include plugin_dir_path(__FILE__) . 'services/RankDB.php';
    include plugin_dir_path(__FILE__) . 'helpers/RankHelper.php';
    include plugin_dir_path(__FILE__) . 'services/RankReward.php';
    include plugin_dir_path(__FILE__) . 'services/TransactionParser.php';
    include(plugin_dir_path(__FILE__) . 'templates/parser.php');
}

function rank()
{
    include(plugin_dir_path(__FILE__) . 'templates/rank.php');
}


add_action('admin_post_mlm_distributor_register', 'distributorRegister');
add_action('admin_post_nopriv_mlm_distributor_register', 'distributorRegister');

function distributorRegister()
{
    $datatable = new Datatable_List();
    $register = $datatable->registerUser($_POST);

    if (isset($register['error'])) {
        wp_redirect(get_admin_url() . 'admin.php?page=mlm-distributor-panel&already_exits=email');
    } else {
        wp_redirect(get_admin_url() . 'admin.php?page=mlm-distributor-panel&newuser=insert');
    }
}

add_action('wp_ajax_circulation_commodity', 'circulationCommodity');
//add_action('admin_post_mlm_circulation_commodity', 'circulationCommodity');

add_action('admin_post_nopriv_mlm_circulation_commodity', 'circulationCommodity');
add_action('admin_post_rewords_history', 'rewordsHistory');

function rewordsHistory()
{
    $datatable = new Datatable_List();
    $register = $datatable->createRewardsHistory($_POST);
    wp_redirect(get_admin_url() . 'admin.php?page=mlm-rewards-history-panel');
}

function circulationCommodity()
{
    include plugin_dir_path(__FILE__) . 'services/RankDB.php';
    include plugin_dir_path(__FILE__) . 'helpers/RankHelper.php';
    include plugin_dir_path(__FILE__) . 'services/Reward.php';
    include plugin_dir_path(__FILE__) . 'services/RankReward.php';

    $rankCalc = new RankReward();
    $result = $rankCalc->calculate($_POST['mlm_circulation_commodity'], $_POST['mlm_distributor_id']);

    echo json_encode($result);
    wp_die();
}

add_action('wp_ajax_all_circulation', 'allCirculation');

function allCirculation() {
    include plugin_dir_path(__FILE__) . 'services/RewordHistory.php';

    $datatable = new RewordHistory();
    $post = $_POST['users'];
    $result = $datatable->allUserRewardHistory($post);

    echo json_encode($result);
    wp_die();
}

add_action('wp_ajax_get_user_reward', 'getUserReward');

function getUserReward()
{
    global $wpdb;
    $prefix = $wpdb->prefix;
    $username = $_GET['user'] ?? null;

    if (!$username) {
        return null;
    }

    $username = preg_replace('/\s\(\d+\)/i', '', $username);
    $sql = "SELECT r.* FROM {$prefix}mlm_rewards r inner join {$prefix}mlm_users u on u.unique_id = r.mlm_user_id WHERE u.user_name = '{$username}'";
    $reward = $wpdb->get_results($sql, 'ARRAY_A');
    if ($reward) {
        $result = $reward[0];
    }

    echo json_encode($result);
    wp_die();
}

function get_sponsor_id($id)
{
    global $wpdb;
    $condition = "unique_id = '" . $id . "'";
    $datatable = new Datatable_List();
    $sponsor = $datatable->get_all_cond_data('mlm_users', $condition);
    if (empty($sponsor)) {
        return '';
    } else {
        return $sponsor[0]->sponsor_id;
    }
}


function get_sponsor($id)
{
    global $wpdb;
    $condition = "id = '" . $id . "'";
    $datatable = new Datatable_List();
    $sponsor = $datatable->get_all_cond_data('mlm_users', $condition);
    $sponsorID = $sponsor[0]->sponsor_id;
    $condition2 = "unique_id = '" . $sponsorID . "'";
    $getsponsor = $datatable->get_all_cond_data('mlm_users', $condition2);

    if (empty($getsponsor)) {
        return '';
    } else {
        return $getsponsor[0]->user_name;
    }
}

function delete_transaction()
{
    $id = $_POST['trnid'];
    $datatable = new Datatable_List();
    $delete = $datatable->deleteTransaction('mlm_transactions', $id);
    echo json_encode($delete);
    wp_die();
}

add_action('wp_ajax_nopriv_delete_transaction', 'delete_transaction');
add_action('wp_ajax_delete_transaction', 'delete_transaction');

function delete_distributor()
{
    $id = $_POST['distid'];
    $datatable = new Datatable_List();
    $delete = $datatable->deleteUser('mlm_users', $id);

    if ($delete['status']) {
        wp_delete_user($id);
    }

    echo json_encode($delete);
    wp_die();
}

add_action('wp_ajax_nopriv_delete_distributor', 'delete_distributor');
add_action('wp_ajax_delete_distributor', 'delete_distributor');

function login_shortcode()
{
    ob_start();
    include(plugin_dir_path(__FILE__) . "shortcodes/login.php");
    $shorcode_php_function = ob_get_clean();

    return $shorcode_php_function;

}

add_shortcode('mlm-login', 'login_shortcode');

function register_shortcode()
{
    ob_start();
    wp_enqueue_script('masked_input', plugin_dir_url(__FILE__) . 'assets/js/masked_input.min.js', false, '1');

    include(plugin_dir_path(__FILE__) . "shortcodes/register.php");
    $shorcode_php_function = ob_get_clean();

    return $shorcode_php_function;

}

add_shortcode('mlm-registration', 'register_shortcode');

function profile_shortcode()
{
    wp_enqueue_script('jquery351', plugin_dir_url(__FILE__) . 'assets/js/jquery-3.5.1.min.js', false, '3.5.1');

    wp_enqueue_style('mlm_admin_css', plugin_dir_url(__FILE__) . 'assets/css/style.css', false, '1.0.0');
    wp_enqueue_style('mlm_admin_css', plugin_dir_url(__FILE__) . 'assets/css/style.css', false, '1.0.0');
    wp_enqueue_style('dataTablesCss', plugin_dir_url(__FILE__) . 'assets/css/datatables.min.css', false, '1.10.22');
    wp_enqueue_style('dndTreeCss', plugin_dir_url(__FILE__) . 'assets/css/dndTree.css');

    wp_enqueue_script('dataTables', plugin_dir_url(__FILE__) . 'assets/js/datatables.min.js', '1.10.22');
    wp_enqueue_script('d3', plugin_dir_url(__FILE__) . 'assets/js/d3.min.js', false, '3.0.0');
    wp_enqueue_script('dndTree', plugin_dir_url(__FILE__) . 'assets/js/dndTree.js', false, '1.0.0');

    include plugin_dir_path(__FILE__) . 'services/UserTree.php';
    include(plugin_dir_path(__FILE__) . "shortcodes/profile2.php");
}

add_shortcode('mlm-profile', 'profile_shortcode');

add_action('admin_post_mlm_frontend_user_login', 'frontendLogin');
add_action('admin_post_nopriv_mlm_frontend_user_login', 'frontendLogin');

function frontendLogin()
{
    if ($_POST['us_email'] == '' || $_POST['us_password'] == '') {
        wp_redirect($_POST['us_return_url'] . '?fieldempty=true');
    } else {
        $datatable = new Datatable_List();
        $login = $datatable->userLogin($_POST['us_email'], $_POST['us_password']);
        if ($login == 'error') {
            wp_redirect($_POST['us_return_url'] . '?loginerror=true');
        } else {
            wp_redirect($_POST['us_return_url']);
        }
    }
}

add_action('admin_post_mlm_frontend_user_register', 'frontendRegister');
add_action('admin_post_nopriv_mlm_frontend_user_register', 'frontendRegister');

// Лимитирование попыток регистрации по IP
function mlm_check_registration_limit($ip_address) {
    $transient_key = 'mlm_reg_attempts_' . md5($ip_address);
    $attempts = get_transient($transient_key);
    
    if ($attempts === false) {
        set_transient($transient_key, 1, HOUR_IN_SECONDS);
        return true;
    }
    
    if ($attempts >= 5) { // Максимум 5 попыток в час
        return false;
    }
    
    set_transient($transient_key, $attempts + 1, HOUR_IN_SECONDS);
    return true;
}

function frontendRegister()
{
    // Проверка лимита регистраций
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if (!mlm_check_registration_limit($ip_address)) {
        wp_redirect($_POST['us_return_url'] . '?registration_limit=true');
        exit;
    }
    
    // Проверка reCAPTCHA
    $recaptcha_enabled = get_option('mlm_recaptcha_enabled', 'no');
    $recaptcha_secret_key = get_option('mlm_recaptcha_secret_key', '');
    
    if ($recaptcha_enabled == 'yes' && !empty($recaptcha_secret_key)) {
        $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        
        if (empty($recaptcha_response)) {
            wp_redirect($_POST['us_return_url'] . '?recaptcha_error=true');
            exit;
        }
        
        // Проверка reCAPTCHA через Google API
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $response = wp_remote_post($verify_url, array(
            'body' => array(
                'secret' => $recaptcha_secret_key,
                'response' => $recaptcha_response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        ));
        
        if (is_wp_error($response)) {
            wp_redirect($_POST['us_return_url'] . '?recaptcha_error=true');
            exit;
        }
        
        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body, true);
        
        if (!$result['success']) {
            wp_redirect($_POST['us_return_url'] . '?recaptcha_error=true');
            exit;
        }
    }
    
    // Существующая проверка полей
    if ($_POST['us_name'] == '' || $_POST['us_email'] == '' || $_POST['us_sponsor_id'] == '') {
        wp_redirect($_POST['us_return_url'] . '?fieldempty=true');
    } else {
        $datatable = new Datatable_List();
        // Удален пароль из массива
        $postArr = array(
            'mlm_distributor_email' => $_POST['us_email'],
            'mlm_distributor_name' => $_POST['us_name'],
            'mlm_distributor_sponsor' => $_POST['us_sponsor_id'],
            'mlm_distributor_phone' => $_POST['us_phone'],
            'city_id' => $_POST['us_city_id']
        );

        $register = $datatable->registerUser($postArr);

        if ($register && !$register['error']) {
            $insert_data = array('id' => '', 'mlm_user_id' => 'USER' . $register);
            $insert = $datatable->insert_data('mlm_rewards', $insert_data);
        }

        $url = rtrim($_POST['us_return_url'], "/");
        if (isset($register['error'])) {
            wp_redirect($url . '?registererror=true');
        } else {
            // Изменено перенаправление на страницу профиля
            wp_redirect('https://asyllike.com/');
        }
    }
}

function update_data()
{
    $datatable = new Datatable_List();
    if ($_POST['us_item'] == 'name') {
        update_user_meta($_POST['us_user_id'], 'first_name', esc_attr($_POST['us_value']));
        $condition = array('user_id' => $_POST['us_user_id']);
        $update_data = array('user_name' => $_POST['us_value']);
        $update = $datatable->updateData('mlm_users', $update_data, $condition);
    } elseif ($_POST['us_item'] == 'email') {
        if (!email_exists($_POST['us_value'])) {
            $args = array(
                'ID' => $_POST['us_user_id'],
                'user_email' => esc_attr($_POST['us_value'])
            );
            wp_update_user($args);
            $condition = array('user_id' => $_POST['us_user_id']);
            $update_data = array('user_email' => $_POST['us_value']);
            $update = $datatable->updateData('mlm_users', $update_data, $condition);
            echo "1";
        } else {
            echo "0";
        }
    } elseif ($_POST['us_item'] == 'password') {
        wp_set_password($_POST['us_value'], $_POST['us_user_id']);
    }
    wp_die();
}

add_action('wp_ajax_nopriv_update_data', 'update_data');
add_action('wp_ajax_update_data', 'update_data');

function get_mlm_children($user_id)
{
    $datatable = new Datatable_List();
    $condition = "sponsor_id='" . $user_id . "'";
    $children = $datatable->get_all_cond_data('mlm_users', $condition);
    return $children;
}

function get_user_name($user_id)
{
    $datatable = new Datatable_List();
    $condition = "unique_id='" . $user_id . "'";
    $user = $datatable->get_all_cond_data('mlm_users', $condition);
    if (empty($user)) {
        return '';
    } else {
        return $user[0]->user_name;
    }
}

function get_user_rank($user_id)
{
    $datatable = new Datatable_List();
    $condition = "unique_id='" . $user_id . "'";
    $user = $datatable->get_all_cond_data('mlm_users', $condition);
    if (empty($user)) {
        return '';
    } else {
        return $user[0]->rank;
    }
}

function get_user_pcc($user_id)
{
    $datatable = new Datatable_List();
    $condition = "mlm_user_id='" . $user_id . "'";
    $user = $datatable->get_all_cond_data('mlm_rewards', $condition);
    if (empty($user)) {
        return '';
    } else {
        return $user[0]->pcc;
    }
}

function get_user_scc($user_id)
{
    $datatable = new Datatable_List();
    $condition = "mlm_user_id='" . $user_id . "'";
    $user = $datatable->get_all_cond_data('mlm_rewards', $condition);
    if (empty($user)) {
        return '';
    } else {
        return $user[0]->scc;
    }
}

function get_user_dr($user_id)
{
    $datatable = new Datatable_List();
    $condition = "mlm_user_id='" . $user_id . "'";
    $user = $datatable->get_all_cond_data('mlm_rewards', $condition);
    if (empty($user)) {
        return '';
    } else {
        return $user[0]->dr;
    }
}

function get_user_sr($user_id)
{
    $datatable = new Datatable_List();
    $condition = "mlm_user_id='" . $user_id . "'";
    $user = $datatable->get_all_cond_data('mlm_rewards', $condition);
    if (empty($user)) {
        return '';
    } else {
        return $user[0]->sr;
    }
}

function get_user_mr($user_id)
{
    $datatable = new Datatable_List();
    $condition = "mlm_user_id='" . $user_id . "'";
    $user = $datatable->get_all_cond_data('mlm_rewards', $condition);
    if (empty($user)) {
        return '';
    } else {
        return $user[0]->mr;
    }
}

add_action('wp_ajax_nopriv_get_user_details', 'get_user_details');
add_action('wp_ajax_get_user_details', 'get_user_details');
function get_user_details()
{
    $datatable = new Datatable_List();
    $cities = $datatable->getCity();

    $condition = "id='" . $_POST['user_id_val'] . "'";
    $user = $datatable->get_all_cond_data('mlm_users', $condition);
    $formHtml = "<form action='" . admin_url('admin-post.php') . "' method='post' class='form_cla form_CLa'>";
    $formHtml .= '<label for="distributor_name">Name <strong>*</strong></label>';
    $formHtml .= '<input type="text" name="mlm_distributor_name" id="distributor_name" required value="' . $user[0]->user_name . '">';
    $formHtml .= '<label for="distributor_phone">Phone <strong>*</strong></label>';
    $formHtml .= '<input type="text" name="mlm_distributor_phone" id="distributor_phone" required value="' . $user[0]->user_phone . '">';
    $formHtml .= '<label for="distributor_name">Sponsor ID <strong>*</strong></label>';
    $formHtml .= '<input type="text" name="mlm_distributor_sponid" id="distributor_sponid" value="' . $user[0]->sponsor_id . '" readonly>';
    $formHtml .= '<label for="city">City <strong>*</strong></label>';

    $formHtml .= '<select class="ui search dropdown" name="city_id">';
    $formHtml .= "<option value=''>Select City</option>";

    foreach ($cities as $city) {
        $selected = ($user[0]->city_id == $city['id']) ? 'selected' : '';
        $formHtml .= "<option value='{$city['id']}' $selected>{$city['name']}</option>";
    }
    $formHtml .= '</select>';

    $formHtml .= '<input type="hidden" value="disrageedt" name="action">';
    $formHtml .= '<input type="hidden" value="' . $user[0]->id . '" name="id">';
    $formHtml .= '<input type="hidden" value="' . $user[0]->user_id . '" name="user_id_val">';
    $formHtml .= '<input type="submit" value="Submit">';
    $formHtml .= "</form>";
    echo $formHtml;
    wp_die();
}

add_action('admin_post_disrageedt', 'editDistributor');
add_action('admin_post_nopriv_disrageedt', 'editDistributor');

function editDistributor()
{
    if (!empty($_POST['mlm_distributor_password'])) {
        wp_set_password($_POST['mlm_distributor_password'], $_POST['user_id_val']);
    }
    if (isset($_POST['mlm_distributor_name']) || isset($_POST['mlm_distributor_email'])) {
        $phoneNumber = preg_replace('![^0-9]+[+]*!', '', $_POST['mlm_distributor_phone']);
        $phoneNumber = $phoneNumber ? '+' . $phoneNumber : null;

        $datatable = new Datatable_List();
        $update = array('user_name' => $_POST['mlm_distributor_name'], 'user_phone' => $phoneNumber, 'city_id' => $_POST['city_id']);
        $condition = array('id' => $_POST['id']);
        $user = $datatable->updateData('mlm_users', $update, $condition);

        update_user_meta($_POST['user_id_val'], 'first_name', esc_attr($_POST['mlm_distributor_name']));
        $datatable->updateOrCreateUserPhone($_POST['user_id_val'], $phoneNumber);
    }
    wp_redirect(get_admin_url() . 'admin.php?page=mlm-distributor-panel&update=true');
}


// Замените существующие функции transactions() и reward_history() на эти версии:

add_action('wp_ajax_nopriv_transactions', 'transactions');
add_action('wp_ajax_transactions', 'transactions');

function transactions()
{
    global $wpdb;

    $userId = 'USER' . get_current_user_id();

    $draw = $_POST['draw'];
    $startPage = $_POST['start'];
    $perPage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    // Маппинг имен колонок для правильной сортировки
    $columns = array(
        0 => 'id',
        1 => 'amount',
        2 => 'date'
    );
    
    $orderColumn = isset($columns[$columnIndex]) ? $columns[$columnIndex] : 'id';

    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " and (amount LIKE '%" . esc_sql($searchValue) . "%' OR id LIKE '%" . esc_sql($searchValue) . "%')";
    }

    # Total number of records without filtering
    $sql = "SELECT count(id) as count FROM {$wpdb->prefix}mlm_transactions where tran_user_id = '{$userId}'";
    $totalRecords = $wpdb->get_row($sql, 'ARRAY_A');

    ## Total number of record with filtering
    $sql = "SELECT count(id) as count FROM {$wpdb->prefix}mlm_transactions where tran_user_id = '{$userId}'" . $searchQuery;
    $totalRecordWithFilter = $wpdb->get_row($sql, 'ARRAY_A');

    # Fetch data
    $sql = "SELECT * FROM {$wpdb->prefix}mlm_transactions where tran_user_id = '{$userId}'" . $searchQuery;
    $sql .= " order by {$orderColumn} {$columnSortOrder} limit {$startPage}, {$perPage} ";
    $transactions = $wpdb->get_results($sql, 'ARRAY_A');

    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords['count'],
        "iTotalDisplayRecords" => $totalRecordWithFilter['count'],
        "aaData" => $transactions
    );

    echo json_encode($response);
    wp_die();
}


add_action('wp_ajax_nopriv_reward_history', 'reward_history');
add_action('wp_ajax_reward_history', 'reward_history');

function reward_history()
{
    global $wpdb;

    $userId = 'USER' . get_current_user_id();

    $draw = $_POST['draw'];
    $startPage = $_POST['start'];
    $perPage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    // Маппинг имен колонок для правильной сортировки
    $columns = array(
        0 => 'id',
        1 => 'amount',
        2 => 'after_rewords_balance',
        3 => 'created_at'
    );
    
    $orderColumn = isset($columns[$columnIndex]) ? $columns[$columnIndex] : 'id';

    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " and (amount LIKE '%" . esc_sql($searchValue) . "%' OR after_rewords_balance LIKE '%" . esc_sql($searchValue) . "%')";
    }

    # Total number of records without filtering
    $sql = "SELECT count(id) as count FROM {$wpdb->prefix}mlm_rewards_history where user_id = '{$userId}'";
    $totalRecords = $wpdb->get_row($sql, 'ARRAY_A');

    ## Total number of record with filtering
    $sql = "SELECT count(id) as count FROM {$wpdb->prefix}mlm_rewards_history where user_id = '{$userId}'" . $searchQuery;
    $totalRecordWithFilter = $wpdb->get_row($sql, 'ARRAY_A');

    # Fetch data
    $sql = "SELECT * FROM {$wpdb->prefix}mlm_rewards_history where user_id = '{$userId}'" . $searchQuery;
    $sql .= " order by {$orderColumn} {$columnSortOrder} limit {$startPage}, {$perPage} ";
    $transactions = $wpdb->get_results($sql, 'ARRAY_A');

    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords['count'],
        "iTotalDisplayRecords" => $totalRecordWithFilter['count'],
        "aaData" => $transactions
    );

    echo json_encode($response);
    wp_die();
}

// WooCommerce hooks для автоматической обработки заказов
add_action('woocommerce_order_status_completed', 'mlm_process_completed_order');
add_action('woocommerce_payment_complete', 'mlm_process_completed_order');

// Замените существующую функцию mlm_process_completed_order этой версией:

function mlm_process_completed_order($order_id) {
    // Получаем заказ
    $order = wc_get_order($order_id);
    if (!$order) return;
    
    // Получаем ID пользователя
    $user_id = $order->get_user_id();
    if (!$user_id) return;
    
    // Проверяем, обработан ли уже этот заказ
    global $wpdb;
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}mlm_transactions WHERE post_id = %d",
        $order_id
    ));
    
    if ($existing) return; // Заказ уже обработан
    
    // Получаем сумму заказа
    $total = $order->get_total();
    $user_unique_id = 'USER' . $user_id;
    
    // Проверяем, есть ли пользователь в MLM системе
    $mlm_user = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}mlm_users WHERE user_id = %d",
        $user_id
    ));
    
    if (!$mlm_user) {
        // Регистрируем пользователя в MLM системе
        include_once plugin_dir_path(__FILE__) . 'services/RegisterUser.php';
        $registry = new RegisterUser();
        
        $user = get_userdata($user_id);
        $billing_phone = $order->get_billing_phone();
        $billing_city = $order->get_billing_city();
        
        // Получаем ID города
        $city_id = 22; // По умолчанию "Другой город"
        $cities = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mlm_city", ARRAY_A);
        foreach ($cities as $city) {
            if (mb_strtolower($city['name']) == mb_strtolower($billing_city)) {
                $city_id = $city['id'];
                break;
            }
        }
        
        // Получаем спонсора по умолчанию из настроек
        $default_sponsor = get_option('mlm_default_sponsor', 'USER1');
        
        $registry->register(
            $user_id,
            $user_unique_id,
            $order->get_billing_first_name(),
            $order->get_billing_last_name(),
            $billing_phone,
            $city_id,
            $default_sponsor,
            $user->user_email
        );
    }
    
    // Обрабатываем транзакцию через RankReward
    include_once plugin_dir_path(__FILE__) . 'services/RankDB.php';
    include_once plugin_dir_path(__FILE__) . 'helpers/RankHelper.php';
    include_once plugin_dir_path(__FILE__) . 'services/Reward.php';
    include_once plugin_dir_path(__FILE__) . 'services/RankReward.php';
    
    $rankReward = new RankReward();
    $rankReward->calculate($total, $user_unique_id);
    
    // Сохраняем post_id для избежания дублирования
    $wpdb->update(
        "{$wpdb->prefix}mlm_transactions",
        array('post_id' => $order_id),
        array('tran_user_id' => $user_unique_id),
        array('%d'),
        array('%s')
    );
}

// AJAX проверка номера телефона
add_action('wp_ajax_check_phone_exists', 'check_phone_exists');
add_action('wp_ajax_nopriv_check_phone_exists', 'check_phone_exists');

function check_phone_exists() {
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    
    if (empty($phone)) {
        wp_send_json(['exists' => false]);
        return;
    }
    
    // Очищаем номер телефона от лишних символов
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    global $wpdb;
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}users WHERE user_login = %s OR user_phone = %s",
        $phone, $phone
    ));
    
    // Также проверяем в таблице mlm_users
    if (!$exists) {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}mlm_users WHERE user_phone = %s",
            $phone
        ));
    }
    
    wp_send_json(['exists' => $exists > 0]);
}