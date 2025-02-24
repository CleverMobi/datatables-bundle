<?php

/*
 * Symfony DataTables Bundle
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Omines\DataTablesBundle\Exporter\Excel;

use Omines\DataTablesBundle\Exporter\AbstractDataTableExporter;
use PhpOffice\PhpSpreadsheet\Cell\CellAddress;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Helper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Exports DataTable data to Excel.
 *
 * @author Maxime Pinot <contact@maximepinot.com>
 */
class ExcelExporter extends AbstractDataTableExporter
{
    #[\Override]
    public function export(array $columnNames, \Iterator $data, array $columnOptions): \SplFileInfo
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getSheet(0);

        $sheet->fromArray($columnNames, null, 'A1');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

        $rowIndex = 2;
        $htmlHelper = new Helper\Html();
        foreach ($data as $row) {
            $colIndex = 1;
            foreach ($row as $value) {
                $sheet->getCell(CellAddress::fromColumnAndRow($colIndex++, $rowIndex))->setValue($htmlHelper->toRichTextObject($value ?? ''));
            }
            ++$rowIndex;
        }

        $this->autoSizeColumnWidth($sheet);

        $filePath = sys_get_temp_dir() . '/' . uniqid('dt') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return new \SplFileInfo($filePath);
    }

    /**
     * Sets the columns width to automatically fit the contents.
     */
    private function autoSizeColumnWidth(Worksheet $sheet): void
    {
        foreach (range(1, Coordinate::columnIndexFromString($sheet->getHighestColumn(1))) as $column) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setAutoSize(true);
        }
    }

    public function getMimeType(): string
    {
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }

    public function getName(): string
    {
        return 'excel';
    }
}
