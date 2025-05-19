<?php
ini_set('memory_limit', '256M');

require_once(__DIR__ . '/../../../../wp-config.php');
include (__DIR__ . '/../lib/spreadsheet/lib/vendor/autoload.php');
include(__DIR__ . '/../services/RewordsHistoryReport.php');

$report = new RewordsHistoryReport();
$r = $report->generateExcel();

echo $r ? 'true' : 'false';