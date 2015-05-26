<?php

namespace iansltx\GCMClient;

interface HttpClientInterface
{
    /**
     * @param $url
     * @param array $body
     * @param array $headers
     * @param $decode_flags
     * @return HttpResponse
     */
    public function postJson($url, array $body = [], array $headers = [], $decode_flags);
}
