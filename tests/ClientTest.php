<?php

namespace iansltx\GCMClient\Test;

use iansltx\GCMClient\{Client, CurlClient, HttpClientInterface, HttpResponse, Message};
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testCreateAndClone()
    {
        $client = new Client('FAKE_API_KEY');
        $this->assertInstanceOf(Client::class, $client);

        $clientWithProject = $client->withProjectId('FAKE_PROJECT_ID');
        $this->assertInstanceOf(Client::class, $clientWithProject);
        $this->assertNotSame($clientWithProject, $client);

        $newClient = new Client('FAKE_API_KEY', 'FAKE_PROJECT_ID');
        $this->assertInstanceOf(Client::class, $newClient);
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
        $this->assertInstanceOf(CurlClient::class, $curl);
        $this->assertInstanceOf(HttpClientInterface::class, $curl);

        $responseObj = $curl->postJson($url, ['json' => 'json'], ['Test' => 'test']);
        $this->assertInstanceOf(HttpResponse::class, $responseObj);
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
        $this->assertInstanceOf(HttpResponse::class, $responseArr);
        $this->assertArrayHasKey('headers', $responseArr->getBody());
        $this->assertEquals('test', $responseArr->getBody()['headers']['Test']);
        $this->assertArrayHasKey('json', $responseArr->getBody());
        $this->assertEquals('json', $responseArr->getBody()['json']['json']);
    }

    /** @medium */
    public function testCurlNotJson()
    {
        $this->expectException('\RuntimeException');
        (new CurlClient())->postJson('http://example.com', [], []);
    }

    /** @medium */
    public function testCurlNoResponse()
    {
        $this->expectException('\RuntimeException');
        (new CurlClient())->postJson('http://local.nxdomain', [], []);
    }
}
