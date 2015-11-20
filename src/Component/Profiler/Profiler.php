<?php
namespace Hostnet\Component\Webpack\Profiler;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
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
    public function set($key, $value)
    {
        $this->logs[$key] = $value;
    }

    /**
     * @param  string $id
     * @return mixed
     */
    public function get($id)
    {
        return isset($this->logs[$id]) ? $this->logs[$id] : null;
    }
}
