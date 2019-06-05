<?php
/**
 * @copyright 2019-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Asset;

use Symfony\Component\Templating\TemplateReference as BaseTemplateReference;

class TemplateReference extends BaseTemplateReference
{
    public function __construct(
        string $bundle = null,
        string $controller = null,
        string $name = null,
        string $format = null,
        string $engine = null
    ) {
        $this->parameters = [
            'bundle'     => $bundle,
            'controller' => $controller,
            'name'       => $name,
            'format'     => $format,
            'engine'     => $engine,
        ];
    }

    /**
     * Returns the path to the template
     *  - as a path when the template is not part of a bundle
     *  - as a resource when the template is part of a bundle.
     *
     * @return string A path to the template or a resource
     */
    public function getPath(): string
    {
        $controller = str_replace('\\', '/', $this->get('controller'));

        $name   = $this->get('name');
        $format = $this->get('format');
        $engine = $this->get('engine');

        $path = (empty($controller) ? '' : $controller . '/') . $name . '.' . $format . '.' . $engine;

        return empty($this->parameters['bundle'])
            ? 'views/' . $path
            : '@' . $this->get('bundle') . '/Resources/views/' . $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogicalName(): string
    {
        return sprintf(
            '%s:%s:%s.%s.%s',
            $this->parameters['bundle'],
            $this->parameters['controller'],
            $this->parameters['name'],
            $this->parameters['format'],
            $this->parameters['engine']
        );
    }
}
