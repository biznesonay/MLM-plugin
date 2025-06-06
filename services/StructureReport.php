<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StructureReport
{
    public function generateExcel(): string
    {
        $status = '';

        try {
            // Устанавливаем часовой пояс Алматы
            date_default_timezone_set('Asia/Almaty');
            
            var_dump('structure report run');
            
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
            
            $filename = 's-' . $startWeek . '-' . $endWeek .'.xlsx';
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

            $status = $this->saveReportFile($filename, $filePath, 1);
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
        foreach ($data as $key => $val) {

            $sheet->setCellValue('A' . $i, $key + 1);
            $sheet->setCellValue('B' . $i, $val['unique_id']);
            $sheet->setCellValue('C' . $i, $val['user_name']);
            $sheet->setCellValue('D' . $i, $val['rank']);
            $sheet->setCellValue('E' . $i, $val['sponsor_id']);
            $sheet->setCellValue('F' . $i, $val['sponsor_name']);
            $sheet->setCellValue('G' . $i, $val['pcc']);
            $sheet->setCellValue('H' . $i, $val['scc']);
            $sheet->setCellValue('I' . $i, $val['dr']);
            $sheet->setCellValue('J' . $i, $val['sr']);
            $sheet->setCellValue('K' . $i, $val['mr']);
            $sheet->setCellValue('L' . $i, $val['br']);
            $sheet->setCellValue('M' . $i, $val['br_car']);
            $sheet->setCellValue('N' . $i, $val['dr'] + $val['sr'] + $val['mr']);

            $i++;
        }
    }

    protected function initHeader(Worksheet $sheet): void
    {
        $sheet->setCellValue('A1', 'Sl no');
        $sheet->setCellValue('B1', 'Distributor ID');
        $sheet->setCellValue('C1', 'Distributor Name');
        $sheet->setCellValue('D1', 'Rank');
        $sheet->setCellValue('E1', 'Sponsor ID');
        $sheet->setCellValue('F1', 'Sponsor Name');
        $sheet->setCellValue('G1', 'PCC');
        $sheet->setCellValue('H1', 'SCC');
        $sheet->setCellValue('I1', 'DR');
        $sheet->setCellValue('J1', 'SR');
        $sheet->setCellValue('K1', 'MR');
        $sheet->setCellValue('L1', 'BR');
        $sheet->setCellValue('M1', 'BRC');
        $sheet->setCellValue('N1', 'ALLR');
    }

    protected function getReportData(): array
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        
        // Устанавливаем часовой пояс для MySQL
        $wpdb->query("SET time_zone = '+05:00'");

        $sql = "SELECT u.*, r.pcc, r.scc, r.dr, r.sr, r.mr, r.br, r.br_car, us.user_name sponsor_name FROM {$prefix}mlm_users as u 
                left join {$prefix}mlm_users us ON us.unique_id = u.sponsor_id
                inner join {$prefix}mlm_rewards AS r ON r.mlm_user_id = u.unique_id
                WHERE r.dr > 0 or r.sr > 0 or r.mr > 0";

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