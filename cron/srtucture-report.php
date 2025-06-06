<?php
ini_set('memory_limit', '256M');

// Устанавливаем часовой пояс PHP
date_default_timezone_set('Asia/Almaty');

require_once(__DIR__ . '/../../../../wp-config.php');

// Устанавливаем часовой пояс для MySQL
global $wpdb;
$wpdb->query("SET time_zone = '+05:00'");

include (__DIR__ . '/../lib/spreadsheet/lib/vendor/autoload.php');
include(__DIR__ . '/../services/StructureReport.php');

$report = new StructureReport();
$r = $report->generateExcel();

echo $r ? 'true' : 'false';