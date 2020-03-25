<?php
namespace LePush;
use InvalidArgumentException;

class DevicePayload {
    private $client;
    const API_DOMAIN = 'http://api.upush.aoidc.net';
    /**
     * DevicePayload constructor.
     * @param $client LePush
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    public function getDevices($registration_id) {

        $uri = self::API_DOMAIN . '/?ct=device&ac=show';

        $payload =  [
            'registration_id' => $registration_id,
        ];

        return Http::post($this->client, $uri, $payload);
    }

    public function updateAlias($registration_id, $alias) {

        $uri = self::API_DOMAIN . '/?ct=device&ac=set_alias';

        $payload =  [
            'registration_id' => $registration_id,
            'alias' => $alias,
        ];

        return Http::post($this->client, $uri, $payload);
    }

    public function addTags($registration_id, array $tags) {

        $uri = self::API_DOMAIN . '/?ct=device&ac=add_tags';

        $payload =  [
            'registration_id' => $registration_id,
            'tags' => $tags,
        ];

        return Http::post($this->client, $uri, $payload);
    }

    public function removeTags($registration_id, $tags) {
        $uri = self::API_DOMAIN . '/?ct=device&ac=del_tags';

        $payload =  [
            'registration_id' => $registration_id,
            'tags' => $tags,
        ];

        return Http::post($this->client, $uri, $payload);
    }



    public function clearTags($registration_id) {

        $uri = self::API_DOMAIN . '/?ct=device&ac=set_tags';

        $payload =  [
            'registration_id' => $registration_id,
            'tags' => [],
        ];

        return Http::post($this->client, $uri, $payload);
    }

    public function isDeviceInTag($registration_id, $tag) {

        $uri = self::API_DOMAIN . '/?ct=device&ac=is_device_in_tag';

        $payload =  [
            'registration_id' => $registration_id,
            'tag' => $tag,
        ];

        return Http::post($this->client, $uri, $payload);
    }


    public function deleteTag($tag) {

        $uri = self::API_DOMAIN . '/?ct=tag&ac=del';

        $payload =  [
            'tag' => $tag,
        ];

        return Http::post($this->client, $uri, $payload);
    }

    public function getAliasDevices($alias, $platform = '') {
        $uri = self::API_DOMAIN . '/?ct=device&ac=list_by_alias';

        $payload =  [
            'alias' => $alias,
            'platform' => $platform
        ];

        return Http::post($this->client, $uri, $payload);
    }

    public function deleteAlias($alias) {
        $uri = self::API_DOMAIN . '/?ct=device&ac=del_alias';

        $payload =  [
            'alias' => $alias,
        ];

        return Http::post($this->client, $uri, $payload);
    }

    /**
     * @param $device_id  设备ID
     * @param $os         操作系统
     */
    public function register($device_id, $os) {
        $uri = self::API_DOMAIN . '/?ct=device&ac=register';

        $payload =  [
            'device_id' => $device_id,
            'os' => $os,
        ];

        return Http::post($this->client, $uri, $payload);
    }

    /**
     * 更新推送token
     * @param $registration_id
     * @param $token
     * @return array
     */
    public function updateToken($registration_id, $token) {
        $uri = self::API_DOMAIN . '/?ct=registration&ac=update_token';

        $payload =  [
            'registration_id' => $registration_id,
            'token' => $token,
        ];

        return Http::post($this->client, $uri, $payload);
    }

}
