<?php

namespace iansltx\GCMClient;

class MessageSendResult
{
    protected $raw;

    public function __construct($response_body)
    {
        $this->raw = $response_body;
    }

    public function isCompleteSuccess()
    {
        return $this->raw->failure == 0;
    }

    public function isPartialSuccess()
    {
        return $this->raw->failure > 0;
    }

    public function getMulticastId()
    {
        return $this->raw->multicast_id;
    }

    public function getSuccessfulCount()
    {
        return $this->raw->success;
    }

    public function getFailedCount()
    {
        return $this->raw->failure;
    }

    public function getCanonicalIds()
    {
        return isset($this->raw->canonical_ids) && is_array($this->raw->canonical_ids)
            ? $this->raw->canonicalIds : [];
    }

    public function getResultsArray()
    {
        return isset($this->raw->results) ? $this->raw->results : [];
    }

    public function getMessageIds()
    {
        $ids = [];
        if (!isset($this->raw->results) || !is_array($this->raw->results)) {
            return $ids;
        }
        foreach ($this->raw->results as $result) {
            $ids[] = $result['message_id'];
        }
        return $ids;
    }

    public function getFailedRegistrationIds()
    {
        return isset($this->raw->failed_registration_ids) ? $this->raw->failed_registration_ids : [];
    }
}
