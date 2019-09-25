<?php
if (!function_exists('write_ini_file')) {
    function write_ini_file($file, $array = []) {
        if (!is_string($file)) {
            throw new \InvalidArgumentException('');
        }
        if (!is_array($array)) {
            throw new \InvalidArgumentException('');
        }
        $data = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $data[] = "[$key]";
                foreach ($val as $skey => $sval) {
                    if (is_array($sval)) {
                        foreach ($sval as $_skey => $_sval) {
                            if (is_numeric($_skey)) {
                                $data[] = $skey.'[] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            } else {
                                $data[] = $skey.'['.$_skey.'] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            }
                        }
                    } else {
                        $data[] = $skey.' = '.(is_numeric($sval) ? $sval : (ctype_upper($sval) ? $sval : '"'.$sval.'"'));
                    }
                }
            } else {
                $data[] = $key.' = '.(is_numeric($val) ? $val : (ctype_upper($val) ? $val : '"'.$val.'"'));
            }
            $data[] = null;
        }
        $fp = fopen($file, 'w');
        $retries = 0;
        $max_retries = 100;
        if (!$fp) {
            return false;
        }
        do {
            if ($retries > 0) {
                usleep(rand(1, 5000));
            }
            $retries += 1;
        } while (!flock($fp, LOCK_EX) && $retries <= $max_retries);
        if ($retries == $max_retries) {
            return false;
        }
        fwrite($fp, implode(PHP_EOL, $data).PHP_EOL);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }
}