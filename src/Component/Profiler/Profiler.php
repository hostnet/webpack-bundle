<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Profiler;

class Profiler
{
    /**
     * @var string[]
     */
    private $logs = [];

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value): void
    {
        $this->logs[$key] = $value;
    }

    /**
     * @param  string $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->logs[$id] ?? null;
    }
}
