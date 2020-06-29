<?php

namespace Plmrlnsnts\HttpExtended;

class Factory extends \Illuminate\Http\Client\Factory
{
    /**
     * Execute a method against a new pending request instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return tap(new PendingRequest($this), function ($request) {
            $request->stub($this->stubCallbacks);
        })->{$method}(...$parameters);
    }
}
