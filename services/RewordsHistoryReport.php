<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RewordsHistoryReport
{
    public function generateExcel(): string
    {
        $status = '';

        try {
            // Устанавливаем часовой пояс Алматы
            date_default_timezone_set('Asia/Almaty');
            
            var_dump('history report start');
            
            // Получаем даты начала и конца недели с учетом часового пояса
            $timezone = new DateTimeZone('Asia/Almaty');
            $now = new DateTime('now', $timezone);
            
            // Получаем понедельник этой недели
            $monday = clone $now;
            $monday->modify('monday this week');
            $startWeek = $monday->format('d-m-Y');
            
            // Получаем воскресенье этой недели
            $sunday = clone $now;
            $sunday->modify('sunday this week');
            $endWeek = $sunday->format('d-m-Y');
            
            $filename = 'h-'. $startWeek . '-' . $endWeek .'.xlsx';
            $uploadPath = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'report';
            $filePath = $uploadPath . "/" . $filename;

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $this->initHeader($sheet);

            $data = $this->getReportData();
            if ($data) {
                var_dump('data get');
            }

            $this->initBody($sheet, $data);

            $writer = new Xlsx($spreadsheet);

            $this->createDir($uploadPath);
            $writer->save($filePath);

            $status = $this->saveReportFile($filename, $filePath, 2);
        } catch (Exception $e) {
            var_dump($e);
        }

        return $status;
    }

    protected function initBody(Worksheet $sheet, array $data): void
    {
        if (!$data) {
            return;
        }

        $i = 2;
        $timezone = new DateTimeZone('Asia/Almaty');
        
        foreach ($data as $key => $val) {
            $date = new DateTime($val['created_at']);
            $date->setTimezone($timezone);
            
            $sheet->setCellValue('A' . $i, $key + 1);
            $sheet->setCellValue('B' . $i, $val['unique_id']);
            $sheet->setCellValue('C' . $i, $val['user_name']);
            $sheet->setCellValue('D' . $i, $val['amount']);
            $sheet->setCellValue('E' . $i, $val['after_rewords_balance']);
            $sheet->setCellValue('F' . $i, $date->format('d.m.Y H:i:s'));
            $i++;
        }
    }

    protected function initHeader(Worksheet $sheet): void
    {
        $sheet->setCellValue('A1', 'Sl no');
        $sheet->setCellValue('B1', 'User ID');
        $sheet->setCellValue('C1', 'Name');
        $sheet->setCellValue('D1', 'Payout Rewards');
        $sheet->setCellValue('E1', 'After account balance');
        $sheet->setCellValue('F1', 'Payout Date and Time');
    }

    protected function getReportData(): array
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        
        // Устанавливаем часовой пояс для MySQL
        $wpdb->query("SET time_zone = '+05:00'");

        // Получаем даты с учетом часового пояса
        $timezone = new DateTimeZone('Asia/Almaty');
        $now = new DateTime('now', $timezone);
        
        // Понедельник этой недели в 00:00:00
        $monday = clone $now;
        $monday->modify('monday this week');
        $monday->setTime(0, 0, 0);
        $startWeek = $monday->format('Y-m-d H:i:s');
        
        // Воскресенье этой недели в 23:59:59
        $sunday = clone $now;
        $sunday->modify('sunday this week');
        $sunday->setTime(23, 59, 59);
        $endWeek = $sunday->format('Y-m-d H:i:s');

        $sql = "SELECT h.amount, h.after_rewords_balance, h.created_at, u.unique_id, u.user_name FROM {$prefix}mlm_rewards_history h ";
        $sql .= "INNER JOIN {$prefix}mlm_users u ON h.user_id = u.unique_id ";
        $sql .= "WHERE h.created_at >= '{$startWeek}' and h.created_at <= '{$endWeek}' and h.is_regular_payment = true ";
        $sql .= "ORDER BY h.id DESC";

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result;
    }

    protected function saveReportFile(string $fileName, string $path, int $typeId)
    {
        global $wpdb;
        
        // Устанавливаем часовой пояс для корректного сохранения времени
        $wpdb->query("SET time_zone = '+05:00'");
        
        $insert_data = [
            'file_name' => $fileName, 
            'file_path' => $path, 
            'type_id' => $typeId,
            'created_at' => current_time('mysql') // Используем WordPress функцию для получения текущего времени
        ];

        return $wpdb->insert("{$wpdb->prefix}mlm_report", $insert_data, ['%s', '%s', '%d', '%s']);
    }

    protected function createDir($uploadPath): void
    {
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
    }
}