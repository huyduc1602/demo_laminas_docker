<?php
function get_include_file($filename, $mode = 'include' ) {
    if (is_file($filename)) {
        $key = Defuse\Crypto\Key::loadFromAsciiSafeString('def000007f0d3916419580555bf180a40289a7f15267a9b1d58e663532daaafc01e30bf08970dd571e57b96d32416a51b90d4cf3915a7b38084aa56fb14c9ced836aa2b0');
        $tmpl = str_replace('.php', '-d.php', $filename);Defuse\Crypto\File::decryptFile($filename, $tmpl, $key);
        switch ($mode){
            case 'require_once':
                $rs = require_once $tmpl;
                break;
            case 'require':
                $rs = require $tmpl;
                break;
            case 'include':
                $rs = include $tmpl;
                break;
        } @unlink($tmpl);
        return $rs;
    }
    return false;
}