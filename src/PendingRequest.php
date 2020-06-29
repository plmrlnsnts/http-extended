<?php

namespace Plmrlnsnts\HttpExtended;

use Illuminate\Http\Client\Response;
use RuntimeException;

class PendingRequest extends \Illuminate\Http\Client\PendingRequest
{
    public $canContinue = true;

    protected $wrapper;
    protected $afterCallback;
    protected $extOptions = [
        'url' => '',
        'query' => [],
        'body' => [],
    ];

    public function prepare($wrapper = null)
    {
        if (is_null($wrapper)) {
            return $this;
        }

        return $this->withWrapper($wrapper);
    }

    public function withWrapper($wrapper)
    {
        if (is_string($wrapper)) {
            $wrapper = new $wrapper();
        }

        if (! method_exists($wrapper, 'boot')) {
            throw new RuntimeException('Missing \'boot\' method from ' . get_class($wrapper));
        }

        $this->wrapper = $wrapper;
        $this->wrapper->boot($this);

        return $this;
    }

    public function withUrl($url)
    {
        $this->extOptions['url'] = $url;

        return $this;
    }

    public function withQuery($key, $value = null)
    {
        if (is_string($key)) {
            data_set($this->extOptions['query'], $key, $value);
        }

        if (is_array($key)) {
            $this->extOptions['query'] = array_merge($this->extOptions['query'], $key);
        };

        return $this;
    }

    public function withBody($key, $value = null)
    {
        if (is_string($key)) {
            data_set($this->extOptions['body'], $key, $value);
        }

        if (is_array($key)) {
            $this->extOptions['body'] = array_merge($this->extOptions['body'], $key);
        };

        return $this;
    }

    public function afterSending($callback)
    {
        $this->afterCallback = $callback;

        return $this;
    }

    public function execute($method): Response
    {
        $options = [
            'query' => $this->extOptions['query']
        ];

        if (! in_array($method, ['GET', 'HEAD'])) {
            $options[$this->bodyFormat] = $this->extOptions['body'];
        }

        return tap($this->send(
            strtoupper($method),
            $this->extOptions['url'],
            $options
        ), function ($response) {
            if (isset($this->afterCallback)) {
                ($this->afterCallback)($this, $response);
            }
        });
    }

    public function getWrapper()
    {
        return $this->wrapper;
    }

    public function getBody($key, $default = null)
    {
        return data_get($this->extOptions['body'], $key, $default);
    }

    public function getQuery($key, $default = null)
    {
        return data_get($this->extOptions['query'], $key, $default);
    }

    public function incrementBody($key, $value = 1)
    {
        data_set($this->extOptions['body'], $key, (
            $this->getBody($key) + $value
        ));
    }

    public function incrementQuery($key, $value = 1)
    {
        data_set($this->extOptions['query'], $key, (
            $this->getQuery($key) + $value
        ));
    }
}
