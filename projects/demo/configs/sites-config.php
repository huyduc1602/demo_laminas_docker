<?php
$dateFormat = [
    'ja' => [
        'date'          => 'Y/n/j',
        'date-time'     => 'Y/n/j G:i',
        'date-str'      => 'Y年n月j日',
        'month'         => 'Y年n月',
        'monthShort'    => 'Y/n',
        'hour'          => 'G:i',
        'mysqlDateTime' => '%Y/%m/%d %H:%i',
        'mysqlDate'     => '%Y/%m/%d',
        'jsDate'        => 'YYYY/M/D',
        'jsDateTime'    => 'YYYY/M/D H:mm',
        'jsDateInWeek'  => 'M月D日[（]ddd[）]',
        'jsTimeDate'    => 'M/D H:mm',
        'jsMonth'       => 'YYYY/M',
        'chatTime'      => 'n/j G:i',
        'jsTime'        => 'H:mm'
    ],
    'vi' => [
        'date'      => 'd.m.Y',
        'date-time' => 'H:i d.m.Y',
        'date-str'  => '\n\g\à\y d \t\h\á\n\g m \n\ă\m Y',
        'month'     => 'm.Y',
        'monthShort'=> 'm.Y',
        'hour'      => 'H:i',
        'mysqlDateTime' => '%H:%i %d/%m/%Y',
        'mysqlDate' => '%d/%m/%Y',
        'jsDate'    => 'DD.MM.YYYY',
        'jsDateTime'=> 'HH:mm DD.MM.YYYY',
        'jsDateInWeek' => '[(]dddd[)] DD.MM',
        'jsTimeDate'=> 'HH:mm DD.MM',
        'jsMonth'   => 'MM.YYYY',
        'chatTime'  => 'H:i d.m',
        'jsTime'    => 'HH:mm'
    ],
    'en' => [
        'date'      => 'm/d/Y',
        'date-time' => 'H:i m/d/Y',
        'date-str'  => 'F d, Y',
        'month'     => 'F Y',
        'monthShort'=> 'm/Y',
        'hour'      => 'H:i',
        'mysqlDateTime' => '%Y/%m/%d %H:%i',
        'mysqlDate' => '%Y/%m/%d',
        'jsDate'    => 'MM/DD/YYYY',
        'jsDateTime'=> 'MM/DD/YYYY HH:mm',
        'jsDateInWeek' => '[(]dddd[)] DD/MMMM',
        'jsTimeDate'=> 'HH:mm DD.MM',
        'jsMonth'   => 'MM/YYYY',
        'chatTime'  => 'H:i d.m',
        'jsTime'    => 'HH:mm'
    ]
];
return [
	[
		[
		    'www.demo.local', 'demo.local'
		],
		[
		    '/' => [
		        'site'     => 'frontend',
		        'upload-folder' => 'frontend',
		        'ss_path' => 'frontend',
		        'language' => 'ja',
		        'locale'   => 'ja_JP',
		        'skin-name'=> 'assets',
		        'date'     => $dateFormat['ja']
		    ],
		]
	],
];
?>