<?php
namespace LePush;
use InvalidArgumentException;

class PushPayload {

    private static $EFFECTIVE_DEVICE_TYPES = array('ios', 'android');

    private $client;
    private $url;

    private $cid;
    private $platform;

    private $audience;
    private $tags;
    private $tagAnds;
    private $tagNots;
    private $alias;
    private $registrationIds;
    private $segmentIds;
    private $abtests;

    private $notificationAlert;
    private $iosNotification;
    private $androidNotification;
    private $smsMessage;
    private $message;
    private $options;

    /**
     * PushPayload constructor.
     * @param $client LePush
     */
    function __construct($client) {
        $this->client = $client;
        //$url = $this->client->is_group() ? 'grouppush' : 'push';
        $this->url = LePush::API_DOMAIN .  '/?ct=push&ac=index';
    }

    public function getCid() {
        return $this->client->getAppkey() . '_' . uniqid();
    }

    public function setCid($cid) {
        $this->cid = trim($cid);
        return $this;
    }

    public function setPlatform($platform) {
        # $required_keys = array('all', 'android', 'ios', 'winphone');
        if (is_string($platform)) {
            $ptf = strtolower($platform);
            if ('all' === $ptf) {
                $this->platform = 'all';
            } elseif (in_array($ptf, self::$EFFECTIVE_DEVICE_TYPES)) {
                $this->platform = array($ptf);
            }
        } elseif (is_array($platform)) {
            $ptf = array_map('strtolower', $platform);
            $this->platform = array_intersect($ptf, self::$EFFECTIVE_DEVICE_TYPES);
        }
        return $this;
    }

    public function setAudience($all) {
        if (strtolower($all) === 'all') {
            $this->addAllAudience();
            return $this;
        } else {
            throw new InvalidArgumentException('Invalid audience value');
        }
    }

    public function addAllAudience() {
        $this->audience = "all";
        return $this;
    }

    public function addTag($tag) {
        return $this->updateAudience('tags', $tag, 'tag');
    }

    public function addTagAnd($tag) {
        return $this->updateAudience('tagAnds', $tag, 'tag_and');
    }

    public function addTagNot($tag) {
        return $this->updateAudience('tagNots', $tag, 'tag_not');
    }

    public function addAlias($alias) {
        return $this->updateAudience('alias', $alias, 'alias');
    }

    public function addRegistrationId($registrationId) {
        return $this->updateAudience('registrationIds', $registrationId, 'registration_id');
    }

    public function addSegmentId($segmentId) {
        return $this->updateAudience('segmentIds', $segmentId, 'segment');
    }

    public function addAbtest($abtest) {
        return $this->updateAudience('abtests', $abtest, 'abtest');
    }

    private function updateAudience($key, $value, $name) {
        if (is_null($this->$key)) {
            $this->$key = array();
        }

        if (is_array($value)) {
            foreach($value as $v) {
                if (!is_string($v)) {
                    throw new InvalidArgumentException("Invalid $name value");
                }
                if (!in_array($v, $this->$key)) {
                    array_push($this->$key, $v);
                }
            }
        } else if (is_string($value)) {
            if (!in_array($value, $this->$key)) {
                array_push($this->$key, $value);
            }
        } else {
            throw new InvalidArgumentException("Invalid $name value");
        }

        return $this;
    }

    public function setNotificationAlert($alert) {
        if (!is_string($alert)) {
            throw new InvalidArgumentException("Invalid alert value");
        }
        $this->notificationAlert = $alert;
        return $this;
    }

    public function build() {
        $payload = array();

        // validate platform
        if (is_null($this->platform)) {
            throw new InvalidArgumentException("platform must be set");
        }
        $payload["platform"] = $this->platform;

        if (!is_null($this->cid)) {
            $payload['cid'] = $this->cid;
        } else {
            $payload['cid'] = $this->getCid();
        }

        // validate audience
        $audience = array();
        if (!is_null($this->tags)) {
            $audience["tag"] = $this->tags;
        }
        if (!is_null($this->tagAnds)) {
            $audience["tag_and"] = $this->tagAnds;
        }
        if (!is_null($this->tagNots)) {
            $audience["tag_not"] = $this->tagNots;
        }
        if (!is_null($this->alias)) {
            $audience["alias"] = $this->alias;
        }
        if (!is_null($this->registrationIds)) {
            $audience["registration_id"] = $this->registrationIds;
        }
        if (!is_null($this->segmentIds)) {
            $audience["segment"] = $this->segmentIds;
        }
        if (!is_null($this->abtests)) {
            $audience["abtest"] = $this->abtests;
        }
        if (is_null($this->audience) && count($audience) <= 0) {
            throw new InvalidArgumentException("audience must be set");
        } else if (!is_null($this->audience) && count($audience) > 0) {
            throw new InvalidArgumentException("you can't add tags/alias/registration_id/tag_and when audience='all'");
        } else if (is_null($this->audience)) {
            $payload["audience"] = $audience;
        } else {
            $payload["audience"] = $this->audience;
        }


        // validate notification
        $notification = array();

        if (!is_null($this->notificationAlert)) {
            $notification['alert'] = $this->notificationAlert;
        }

        if (!is_null($this->androidNotification)) {
            $notification['android'] = $this->androidNotification;
            if (is_null($this->androidNotification['alert'])) {
                if (is_null($this->notificationAlert)) {
                    throw new InvalidArgumentException("Android alert can not be null");
                } else {
                    $notification['android']['alert'] = $this->notificationAlert;
                }
            }
        }

        if (!is_null($this->iosNotification)) {
            $notification['ios'] = $this->iosNotification;
            if (is_null($this->iosNotification['alert'])) {
                if (is_null($this->notificationAlert)) {
                    throw new InvalidArgumentException("iOS alert can not be null");
                } else {
                    $notification['ios']['alert'] = $this->notificationAlert;
                }
            }
        }

        if (count($notification) > 0) {
            $payload['notification'] = $notification;
        }

        if (!is_null($this->message)) {
            $payload['message'] = $this->message;
        }
        if (!array_key_exists('notification', $payload) && !array_key_exists('message', $payload)) {
            throw new InvalidArgumentException('notification and message can not all be null');
        }

        if (!is_null($this->smsMessage)) {
            $payload['sms_message'] = $this->smsMessage;
        }

        if (is_null($this->options)) {
            $this->options();
        }

        $payload['options'] = $this->options;

        return $payload;
    }

    public function toJSON() {
        $payload = $this->build();
        return json_encode($payload);
    }

    public function printJSON() {
        echo $this->toJSON();
        return $this;
    }

    public function send() {
        echo "send........";
        return Http::post($this->client, $this->url, $this->build());
    }

    public function validate() {
        $url = $this->client->makeURL('push') . '/push/validate';
        return Http::post($this->client, $url, $this->build());
    }

    private function generateSendno() {
        return rand(100000, getrandmax());
    }

    # new methods
    public function iosNotification($alert = '', array $notification = array()) {
        $ios = array();
        $ios['alert'] = (is_string($alert) || is_array($alert)) ? $alert : '';
        if (!empty($notification)) {
            if (isset($notification['sound'])) {
                if (is_string($notification['sound']) || is_array($notification['sound'])) {
                    $ios['sound'] = $notification['sound'];
                } else {
                    unset($notification['sound']);
                }
            }
            if (isset($notification['content-available'])) {
                if (is_bool($notification['content-available'])) {
                    $ios['content-available'] = $notification['content-available'];
                } else {
                    unset($notification['content-available']);
                }
            }
            if (isset($notification['mutable-content'])) {
                if (is_bool($notification['mutable-content'])) {
                    $ios['mutable-content'] = $notification['mutable-content'];
                } else {
                    unset($notification['mutable-content']);
                }
            }
            if (isset($notification['extras'])) {
                if (is_array($notification['extras']) && !empty($notification['extras'])) {
                    $ios['extras'] = $notification['extras'];
                } else {
                    unset($notification['extras']);
                }
            }
            $ios = array_merge($notification, $ios);
        }
        if (!isset($ios['sound'])) {
            $ios['sound'] = '';
        }
        if (!isset($ios['badge'])) {
            $ios['badge'] = '+1';
        }
        $this->iosNotification = $ios;
        return $this;
    }

    public function androidNotification($alert = '', array $notification = array()) {
        $android = array();
        $android['alert'] = is_string($alert) ? $alert : '';
        if (!empty($notification)) {
            if (isset($notification['builder_id'])) {
                if (is_int($notification['builder_id'])) {
                    $android['builder_id'] = $notification['builder_id'];
                } else {
                    unset($notification['builder_id']);
                }
            }
            if (isset($notification['priority'])) {
                if (is_int($notification['priority'])) {
                    $android['priority'] = $notification['priority'];
                } else {
                    unset($notification['priority']);
                }
            }
            if (isset($notification['style'])) {
                if (is_int($notification['style'])) {
                    $android['style'] = $notification['style'];
                } else {
                    unset($notification['style']);
                }
            }
            if (isset($notification['alert_type'])) {
                if (is_int($notification['alert_type'])) {
                    $android['alert_type'] = $notification['alert_type'];
                } else {
                    unset($notification['alert_type']);
                }
            }
            if (isset($notification['inbox'])) {
                if (is_array($notification['inbox']) && !empty($notification['inbox'])) {
                    $android['inbox'] = $notification['inbox'];
                } else {
                    unset($notification['inbox']);
                }
            }
            if (isset($notification['intent'])) {
                if (is_array($notification['intent']) && !empty($notification['intent'])) {
                    $android['intent'] = $notification['intent'];
                } else {
                    unset($notification['intent']);
                }
            }
            if (isset($notification['extras'])) {
                if (is_array($notification['extras']) && !empty($notification['extras'])) {
                    $android['extras'] = $notification['extras'];
                } else {
                    unset($notification['extras']);
                }
            }
            $android = array_merge($notification, $android);
        }
        $this->androidNotification = $android;
        return $this;
    }

    public function message($title, $content, $type, $data) {
        # $required_keys = array('title', 'content_type', 'extras');
        $message = array();
        $message['content'] = $content;
        $message['title']   = $title;
        $message['type']    = $type;
        $message['data']    = $data;

        $this->message = $message;
        return $this;
    }

    public function options(array $opts = array()) {
        # $required_keys = array('sendno', 'time_to_live', 'override_msg_id', 'apns_production', 'apns_collapse_id', 'big_push_duration');
        $options = array();
        if (isset($opts['sendno'])) {
            $options['sendno'] = $opts['sendno'];
        } else {
            $options['sendno'] = $this->generateSendno();
        }
        if (isset($opts['time_to_live']) && $opts['time_to_live'] <= 864000 && $opts['time_to_live'] >= 0) {
            $options['time_to_live'] = $opts['time_to_live'];
        }

        if (isset($opts['apns_production'])) {
            $options['apns_production'] = (bool)$opts['apns_production'];
        } else {
            $options['apns_production'] = false;
        }
        if (isset($opts['apns_collapse_id'])) {
            $options['apns_collapse_id'] = $opts['apns_collapse_id'];
        }

        $this->options = $options;

        return $this;
    }

}
