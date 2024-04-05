<?php
/**
 * ExportDataCSV - Exports to CSV (comma separated value) format.
 */
 namespace Zf\Ext\Utilities\PhpExp;
 use Zf\Ext\Utilities\PhpExp\ExportData;
 
class ExportCSV extends ExportData {
	
	function generateRow($row) {
		foreach ($row as $key => $value) {
		    if ( is_array($value) ) $value = implode("\n", $value);
			// Escape inner quotes and wrap all contents in new quotes.
			// Note that we are using \" to escape double quote not ""
			$row[$key] = '"'. str_replace('"', '\"', $value) .'"';
		}
		return implode($this->csv_separator, $row) . "\n" ;
	}
	
	function sendHttpHeaders() {
		header("Content-type: text/csv; charset= UTF-8");
		header("Content-Disposition: attachment; filename=".basename($this->filename));
	}
}