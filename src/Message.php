<?php

namespace iansltx\GCMClient;

class Message
{
    protected $data = [];
    protected $collapseKey = null;
    protected $delayWhileIdle = null;
    protected $ttl = null;
    protected $isDryRun = null;

    /**
     * Builds the GCM message value object
     *
     * @param string[] $data key value pairs to pass to the message consumer;
     *  values must be strings (no nesting)
     * @param string|null $collapse_key if multiple messages with the same
     *  collapse key are sent before a device wakes up to receive messages
     *  (delay_while_idle === true) then only show the latest message with
     *  that collapse_key.
     * @param bool|null $delay_while_idle if true, don't send the message
     *  until the device is awake (for better power usage); GCM-side default
     *  is true as of Android 4.2.2 (hence why the default here is true), so
     *  a null value will default to true at this point. To send a notification
     *  that a device will receive and notify from when the screen is turned
     *  off, set this to false.
     * @param int|null $ttl if set, discards the message if it isn't received
     *  by the device within the supplied number of seconds of sending.
     * @param bool|null $dry_run if true, don't send the message to the
     *  device; GCM-side default is false, which will be used if this
     *  is set to null (default here is false).
     */
    public function __construct (
        array $data,
        $collapse_key = null,
        $delay_while_idle = true,
        $ttl = null,
        $dry_run = false
    ) {
        $this->data = $data;
        $this->collapseKey = ($collapse_key === null ? null : $collapse_key);
        $this->delayWhileIdle = ($delay_while_idle === null ? null : (bool) $delay_while_idle);
        $this->ttl = ($ttl === null ? null : (int) $ttl);
        $this->isDryRun = ($dry_run === null ? null : (bool) $dry_run);
    }

    /**
     * @return string[]
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @return null|string
     */
    public function getCollapseKey() {
        return $this->collapseKey;
    }

    /**
     * @return bool|null
     */
    public function isDelayedWhileIdle() {
        return $this->delayWhileIdle;
    }

    /**
     * @return int|null
     */
    public function getTTL() {
        return $this->ttl;
    }

    /**
     * @return bool|null
     */
    public function isDryRun() {
        return $this->isDryRun;
    }

    /**
     * Returns, ready for JSON encoding except for recipient info, the
     * message as it should be submitted to GCM's HTTP API
     *
     * @return array
     */
    public function toArray() {
        $msg = ['data' => $this->data];
        if ($this->collapseKey !== null)
            $msg['collapse_key'] = $this->collapseKey;
        if ($this->delayWhileIdle !== null)
            $msg['delay_while_idle'] = $this->delayWhileIdle;
        if ($this->ttl !== null)
            $msg['ttl'] = $this->ttl;
        if ($this->isDryRun !== null)
            $msg['dry_run'] = $this->isDryRun;

        return $msg;
    }
}
