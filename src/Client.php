<?php

namespace iansltx\GCMClient;

class Client
{
    const MESSAGE_URL = 'https://android.googleapis.com/gcm/send';
    const NOTIFICATION_KEY_URL = 'https://android.googleapis.com/gcm/notification';

    protected $apiKey;
    protected $projectId;
    protected $http;

    /**
     * Creates a GCM client value object; change operations (e.g. withProjectId) return
     * a new instance of the client rather than modifying the existing one.
     *
     * @param string $api_key
     * @param string $project_id used for managing Notification Keys
     * @param HttpClientInterface $http will create a CurlClient if nothing is provided
     */
    public function __construct($api_key, $project_id = null, HttpClientInterface $http = null)
    {
        $this->apiKey = $api_key;
        $this->projectId = $project_id;
        $this->http = ($http === null ? new CurlClient() : $http);
    }

    /**
     * @return string|null
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * Returns a new instance of the Client with the project ID set to the
     * supplied value, with other attributes unmodified.
     *
     * @param string $id
     * @return Client
     */
    public function withProjectId($id)
    {
        return new self($this->apiKey, $id, $this->http);
    }

    /**
     * Sends a message to one or more devices identified by registration ID
     *
     * @param string|string[] $registration_ids if an array, sends as a multicast message
     * @param Message $message
     * @return MessageSendResult
     */
    public function sendToRegIds($registration_ids, Message $message)
    {
        $reqBody = $message->toArray();
        $reqBody[!is_array($registration_ids) ? 'to' : 'registration_ids'] = $registration_ids;

        return new MessageSendResult($this->sendRequest(self::MESSAGE_URL, $reqBody));
    }

    /**
     * Sends a message to a group of devices identified by a notification key
     *
     * @param string $key
     * @param Message $message
     * @return MessageSendResult
     */
    public function sendToNotificationKey($key, Message $message)
    {
        $reqBody = $message->toArray();
        $reqBody['to'] = $key;

        return new MessageSendResult($this->sendRequest(self::MESSAGE_URL, $reqBody));
    }

    /**
     * Creates a notification key with a group of registration IDs and a name
     * that is unique to the project. Note that notification keys are, for
     * modification and message-sending purposes, only referred to by their
     * key, rather than by their name.
     *
     * @param string $name
     * @param string[] $registration_ids
     * @return string the newly created notification key
     */
    public function createNotificationKey($name, array $registration_ids)
    {
        return $this->sendRequest(self::NOTIFICATION_KEY_URL, [
            'operation' => 'create',
            'notification_key_name' => $name,
            'registration_ids' => $registration_ids
        ], ['project_id' => $this->projectId])->notification_key;
    }

    /**
     * Adds a set of registration IDs to an existing notification key
     *
     * @param string $key
     * @param string[] $registration_ids
     * @param string $name only modify a notification key if it has the supplied name
     * @return string the modified notification key
     */
    public function addToNotificationKey($key, array $registration_ids, $name = null)
    {
        return $this->modifyNotificationKey('add', $key, $registration_ids, $name);
    }

    /**
     * Removes a set of registration IDs from an existing notification key
     *
     * @param string $key
     * @param string[] $registration_ids
     * @param string $name only modify a notification key if it has the supplied name
     * @return string the modified notification key
     */
    public function removeFromNotificationKey($key, array $registration_ids, $name = null)
    {
        return $this->modifyNotificationKey('remove', $key, $registration_ids, $name);
    }

    /**
     * Performs an operation on an existing notification key
     *
     * @param string $op the operation to perform on the supplied key
     * @param string $key
     * @param string[] $registration_ids
     * @param string $name only modify a notification key if it has the supplied name
     * @return string the modified notification key
     */
    protected function modifyNotificationKey($op, $key, array $registration_ids, $name = null)
    {
        if (!$this->projectId)
            throw new \InvalidArgumentException('Missing project ID. Use ->withProjectId().');

        $body = ['operation' => $op, 'notification_key' => $key, 'registration_ids' => $registration_ids];

        if ($name !== null)
            $body['notification_key_name'] = $name;

        return $this->sendRequest(self::NOTIFICATION_KEY_URL, $body, ['project_id' => $this->projectId])
            ->notification_key;
    }

    /**
     * Sends a POST with the internal auth information plus any supplied headers,
     * with the supplied URL and an array representing not-yet-encoded body
     * content. Throws an exception on a non-200 status. Otherwise, returns the
     * JSON-decoded response body, without the convert-to-array decode flag set.
     *
     * @param string $url
     * @param array $body
     * @param string[] $headers
     * @return mixed
     * @throws \RuntimeException
     */
    protected function sendRequest($url, $body, $headers = [])
    {
        $headers['Authorization'] = 'key=' . $this->apiKey;
        $res = $this->http->postJson($url, $body, $headers);

        if (!$res->isSuccess())
            throw new \RuntimeException(json_encode($res->getBody()), $res->getStatusCode());

        return $res->getBody();
    }
}
