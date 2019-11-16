<?php
namespace LePush;
use InvalidArgumentException;

class LePush {

    private $appKey;
    private $masterSecret;
    private $retryTimes;
    private $logFile;
    private $zone;
    private static $zones = [

    ];

    const API_DOMAIN = 'https://pushapi.lemajestic.com';

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

    public function getAuthStr() { return $this->appKey . ":" . $this->masterSecret; }
    public function getRetryTimes() { return $this->retryTimes; }
    public function getLogFile() { return $this->logFile; }

    public function is_group() {
        $str = substr($this->appKey, 0, 6);
        return $str === 'group-';
    }

    public function makeURL($key) {
        return self::API_DOMAIN;
    }
}
