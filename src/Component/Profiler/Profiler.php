<?php
namespace Hostnet\Component\WebpackBundle\Profiler;

use Hostnet\Bundle\WebpackBundle\DependencyInjection\Configuration;
use Hostnet\Component\WebpackBundle\Asset\Compiler;
use Hostnet\Component\WebpackBundle\Asset\Tracker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class Profiler implements DataCollectorInterface
{
    private $data = [];

    /** {@inheritdoc} */
    public function getName()
    {
        return Configuration::CONFIG_ROOT;
    }

    /** {@inheritdoc} */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param  string $id
     * @param  mixed  $default
     * @return mixed
     */
    public function get($id, $default = false)
    {
        return isset($this->data[$id]) ? $this->data[$id] : $default;
    }
}
