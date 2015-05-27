<?php

namespace iansltx\GCMClient\Test;

use iansltx\GCMClient\Client;
use iansltx\GCMClient\CurlClient;
use iansltx\GCMClient\Message;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    const CLIENT_CLASS = 'iansltx\GCMClient\Client';
    const CURL_CLIENT_CLASS = 'iansltx\GCMClient\CurlClient';
    const HTTP_CLIENT_IFACE = 'iansltx\GCMClient\HttpClientInterface';
    const HTTP_RESPONSE_CLASS = 'iansltx\GCMClient\HttpResponse';

    public function testCreateAndClone()
    {
        $client = new Client('FAKE_API_KEY');
        $this->assertInstanceOf(self::CLIENT_CLASS, $client);

        $clientWithProject = $client->withProjectId('FAKE_PROJECT_ID');
        $this->assertInstanceOf(self::CLIENT_CLASS, $clientWithProject);
        $this->assertNotSame($clientWithProject, $client);

        $newClient = new Client('FAKE_API_KEY', 'FAKE_PROJECT_ID');
        $this->assertInstanceOf(self::CLIENT_CLASS, $newClient);
        $this->assertEquals($newClient, $clientWithProject);
        $this->assertEquals($newClient->getProjectId(), $clientWithProject->getProjectId());
    }

    public function testMessage()
    {
        $msg = new Message(['test' => 'Test'], 'collapse', true, 360, true);
        $this->assertEquals(['test' => 'Test'], $msg->getData());
        $this->assertEquals('collapse', $msg->getCollapseKey());
        $this->assertEquals(360, $msg->getTTL());
        $this->assertTrue($msg->isDelayedWhileIdle());
        $this->assertTrue($msg->isDryRun());
        $this->assertEquals([
            'data' => ['test' => 'Test'],
            'dry_run' => true,
            'delay_while_idle' => true,
            'time_to_live' => 360,
            'collapse_key' => 'collapse'
        ], $msg->toArray());
    }

    /** @medium */
    public function testCurlSuccesses()
    {
        $url = 'http://httpbin.org/post';

        $curl = new CurlClient();
        $this->assertInstanceOf(self::CURL_CLIENT_CLASS, $curl);
        $this->assertInstanceOf(self::HTTP_CLIENT_IFACE, $curl);

        $responseObj = $curl->postJson($url, ['json' => 'json'], ['Test' => 'test']);
        $this->assertInstanceOf(self::HTTP_RESPONSE_CLASS, $responseObj);
        $this->assertEquals(200, $responseObj->getStatusCode());
        $this->assertTrue($responseObj->isSuccess());
        $this->assertInstanceOf('\stdClass', $responseObj->getBody());
        $this->assertInstanceOf('\stdClass', $responseObj->getBody()->headers);
        $this->assertEquals('test', $responseObj->getBody()->headers->Test);
        $this->assertInstanceOf('\stdClass', $responseObj->getBody()->json);
        $this->assertEquals('json', $responseObj->getBody()->json->json);

        $this->assertArrayHasKey('Content-Type', $responseObj->getHeaders());
        $this->assertEquals('application/json', $responseObj->getHeaders()['Content-Type']);
        $this->assertEquals('application/json', $responseObj->getHeader('Content-Type'));
        $this->assertEquals('application/json', $responseObj->getHeader('content-type'));

        $responseArr = $curl->postJson($url, ['json' => 'json'], ['Test' => 'test'], JSON_OBJECT_AS_ARRAY);
        $this->assertInstanceOf(self::HTTP_RESPONSE_CLASS, $responseArr);
        $this->assertArrayHasKey('headers', $responseArr->getBody());
        $this->assertEquals('test', $responseArr->getBody()['headers']['Test']);
        $this->assertArrayHasKey('json', $responseArr->getBody());
        $this->assertEquals('json', $responseArr->getBody()['json']['json']);
    }

    /** @medium */
    public function testCurlNotJson()
    {
        $this->setExpectedException('\RuntimeException');
        (new CurlClient())->postJson('http://curlmyip.com', [], []);
    }

    /** @medium */
    public function testCurlNoResponse()
    {
        $this->setExpectedException('\RuntimeException');
        (new CurlClient())->postJson('http://local.nxdomain', [], []);
    }
}
