<?php
namespace LePush;
use InvalidArgumentException;

class LePush {

    private $appKey;
    private $masterSecret;
    private $retryTimes;
    private $logFile;

    const API_DOMAIN = 'https://pushapi.lemajestic.com';

    const CLIENT_SIGN_VERSION = '1';

    public function __construct($appKey, $masterSecret, array $options = []) {
        if (!is_string($appKey) || !is_string($masterSecret)) {
            throw new InvalidArgumentException("Invalid appKey or masterSecret");
        }
        $this->appKey = $appKey;
        $this->masterSecret = $masterSecret;
        if (!empty($options['retryTimes'])) {
            $this->retryTimes = $options['retryTimes'];
        } else {
            $this->retryTimes = 1;
        }

        $this->logFile = empty($options['logFile'])  ? Config::DEFAULT_LOG_FILE : $options['logFile'];
    }

    public function push() { return new PushPayload($this); }
    public function report() { return new ReportPayload($this); }
    public function device() { return new DevicePayload($this); }

    public function getAuthStr() { return self::generateTempToken(); }
    public function getRetryTimes() { return $this->retryTimes; }
    public function getLogFile() { return $this->logFile; }

    public function is_group() {
        $str = substr($this->appKey, 0, 6);
        return $str === 'group-';
    }

    public function makeURL($key) {
        return self::API_DOMAIN;
    }

    public function getAppkey() {
        return $this->appKey;
    }

    public function getMasterKey() {
        return $this->masterSecret;
    }

    /**
     * 生成 临时token
     * @param int $expireTime  过期时间，默认 86400 * 30
     * @return bool|string
     */
    public function generateTempToken($expireTime = 2592000)
    {
        $expire = time() + $expireTime;
        $auth_str =   $this->appKey . '||' . microtime(true) .  '||' .  $expire ;

        $auth_str = self::CLIENT_SIGN_VERSION . ':'. $this->appKey . ':' . $this->_authcode($auth_str, 'ENCODE', $this->masterSecret);
        $auth_str = base64_encode($auth_str);
        return $auth_str;
    }

    public static function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

        $ckey_length = 4;
        $key = md5($key);

        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));

        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();

        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;

            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if($operation == 'DECODE') {

            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }
}
