<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types = 1);
namespace Hostnet\Component\Webpack\Profiler;

use Hostnet\Bundle\WebpackBundle\DependencyInjection\Configuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

/**
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 */
final class WebpackDataCollector implements DataCollectorInterface
{
    /**
     * @var Profiler
     */
    private $profiler;

    /**
     * @param Profiler $profiler
     */
    public function __construct(Profiler $profiler)
    {
        $this->profiler = $profiler;
    }

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
     * @param  string $id
     * @param  mixed  $default
     * @return string
     */
    public function get($id, $default = false)
    {
        return $this->profiler->get($id, $default);
    }
}
