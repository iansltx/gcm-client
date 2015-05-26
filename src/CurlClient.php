<?php

namespace iansltx\GCMClient;

class CurlClient implements HttpClientInterface
{
    protected static $baseOptions = [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true];
    protected $userOptions = [];

    public function __construct($user_options = [])
    {
        $this->userOptions = $user_options;
    }

    public function postJson($url, array $data = [], array $headers = [], $decode_flags = 0)
    {
        $curlHeaders = [];
        if (!isset($headers['content-type'])) {
            $headers['content-type'] = 'application/json';
        }
        foreach ($headers as $k => $v) {
            $curlHeaders[] = $k . ': ' . $v;
        }

        $ch = $this->getHandle($url, [
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $curlHeaders
        ]);

        $res = curl_exec($ch);
        if (!$res) {
            throw new \RuntimeException('Empty response; ' . json_encode(curl_getinfo($ch)));
        }

        list($rawHeaders, $body) = explode("\r\n\r\n", $res, 2);
        $resHeaders = [];
        foreach (array_slice(explode("\r\n", $rawHeaders), 1) as $rawHeader) {
            list($key, $value) = explode(': ', $rawHeader, 2);
            $resHeaders[$key] = $value;
        }

        $decoded = json_decode($body, $decode_flags);
        if ($decoded === false || $decoded === null) {
            throw new \RuntimeException('JSON decoding error: ' . json_last_error_msg());
        }

        return new HttpResponse($decoded, $resHeaders, curl_getinfo($ch, CURLINFO_HTTP_CODE));
    }

    protected function getHandle($url, $options = [])
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, static::$baseOptions);
        curl_setopt_array($ch, $this->userOptions);
        curl_setopt_array($ch, $options);
        return $ch;
    }
}
