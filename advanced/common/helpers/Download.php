<?php
namespace common\helpers;

use Yii;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Border;
use PHPExcel_CachedObjectStorageFactory;
use PHPExcel_Settings;

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

    public static function downloadExcelNew($filename, $query, $format)
    {
        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
        $cacheSettings = array(' memoryCacheSize ' => '2048MB');

        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        ob_end_clean();
        ob_start();
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename=$filename");
        header("Charset=UTF-8");

        $objectPHPExcel = new PHPExcel();

        $objectPHPExcel->setActiveSheetIndex(0);
        $objectPHPExcel->getSheet(0)->setTitle(self::SHEET_NAME);
        // 获取当前 sheet
        $objWorksheet = $objectPHPExcel->getActiveSheet();
        // 设置标题
        $pColumn = 0;
        foreach ($format as $item) {
            $objWorksheet->setCellValueExplicitByColumnAndRow($pColumn, 1, $item['label']);
            // 获取样式
            $style = $objWorksheet->getStyleByColumnAndRow($pColumn, 1);
            // 设置字体加粗
            $style->getFont()->setBold(true);

            // 设置边框
            $style->getBorders()
                ->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            // 设置列自动
            $objWorksheet->getColumnDimensionByColumn($pColumn)->setAutoSize(true);

            $pColumn++;
        }
        // 设置数据
        $pRow = 1;
        foreach ($query->each(200) as $query_item) {
            $pRow++;
            $pColumn = 0;
            foreach ($format as $item) {
                $formatFunction = $item['value'];
                if (is_string($formatFunction)) {
                    $value = $query_item[$formatFunction];
                } else {
                    $value = $formatFunction($query_item);
                }
                $objWorksheet->setCellValueExplicitByColumnAndRow($pColumn, $pRow, $value);

                $style = $objWorksheet->getStyleByColumnAndRow($pColumn, $pRow);

                // 设置边框
                $style->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                $objWorksheet->getColumnDimensionByColumn($pColumn)->setAutoSize(true);

                $pColumn++;
            }
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objectPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}