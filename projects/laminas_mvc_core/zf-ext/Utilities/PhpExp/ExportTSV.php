<?php
/**
 * ExportDataTSV - Exports to TSV (tab separated value) format.
 */
namespace Zf\Ext\Utilities\PhpExp;

use Zf\Ext\Utilities\PhpExp\ExportData;

class ExportTSV extends ExportData
{

    function generateRow($row)
    {
        foreach ($row as $key => $value) {
            // Escape inner quotes and wrap all contents in new quotes.
            // Note that we are using \" to escape double quote not ""
            $row[$key] = '"' . str_replace('"', '\"', $value) . '"';
        }
        return implode("\t", $row) . "\n";
    }

    function sendHttpHeaders()
    {
        header("Content-type: text/tab-separated-values");
        header("Content-Disposition: attachment; filename=" . basename($this->filename));
    }
}