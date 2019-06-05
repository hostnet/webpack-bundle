<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Profiler;

use Hostnet\Bundle\WebpackBundle\DependencyInjection\Configuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

final class WebpackDataCollector implements DataCollectorInterface
{
    /**
     * @var Profiler
     */
    private $profiler;

    public function __construct(Profiler $profiler)
    {
        $this->profiler = $profiler;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Configuration::CONFIG_ROOT;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
    }

    /**
     * @param  string $id
     * @param  mixed  $default
     * @return string
     */
    public function get($id, $default = false): string
    {
        return $this->profiler->get($id, $default);
    }
}
