<?php
namespace common\helpers;

use Yii;

class Download
{
    public static function downloadCsv($filename, $query, $format)
    {
        header("Content-Type: application/csv");
        header("Charset=UTF-8");
        header("Content-Disposition: attachment; filename=$filename");
        $output = fopen('php://output', 'w') or die("Can't open php://output");
        $title = [];
        foreach ($format as $item) {
            $title[] = iconv('utf-8', 'GBK//TRANSLIT//IGNORE', $item['label']);
        }
        fputcsv($output, $title);

        foreach ($query->each(200) as $query_item) {
            $row = [];
            foreach ($format as $item) {
                $formatFunction = $item['value'];
                if (is_string($formatFunction)) {
                    $value = $query_item[$formatFunction];
                } else {
                    $value = $formatFunction($query_item);
                }
                $row[] = iconv('utf-8', 'GBK//TRANSLIT//IGNORE', $value);
            }

            fputcsv($output, $row);
        }

        fclose($output);
    }
}