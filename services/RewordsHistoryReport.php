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
            var_dump('history report start');
            $startWeek = date("d-m-Y", strtotime('monday this week'));
            $endWeek = date("d-m-Y", strtotime('sunday this week'));
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
        foreach ($data as $key => $val) {
            $sheet->setCellValue('A' . $i, $key + 1);
            $sheet->setCellValue('B' . $i, $val['unique_id']);
            $sheet->setCellValue('C' . $i, $val['user_name']);
            $sheet->setCellValue('D' . $i, $val['amount']);
            $sheet->setCellValue('E' . $i, $val['after_rewords_balance']);
            $sheet->setCellValue('F' . $i,  \DateTime::createFromFormat('Y-m-d H:i:s', $val['created_at'])->format('F j, Y H:i:s') );
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

        $startWeek = date("Y-m-d 00:00:00", strtotime('monday this week'));
        $endWeek = date("Y-m-d 23:59:59.999999", strtotime('sunday this week'));

        $sql = "SELECT h.amount, h.after_rewords_balance, h.created_at, u.unique_id, u.user_name FROM {$prefix}mlm_rewards_history h ";
        $sql .= "INNER JOIN {$prefix}mlm_users u ON h.user_id = u.unique_id ";
        $sql .= "WHERE h.created_at >= '{$startWeek}' and  h.created_at <= '{$endWeek}' and h.is_regular_payment = true ";
        $sql .= "ORDER BY h.id DESC";

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result;
    }

    protected function saveReportFile(string $fileName, string $path, int $typeId)
    {
        global $wpdb;
        $insert_data = ['file_name' => $fileName, 'file_path' => $path, 'type_id' => $typeId];

        return $wpdb->insert("{$wpdb->prefix}mlm_report", $insert_data);
    }

    protected function createDir($uploadPath): void
    {
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
    }
}