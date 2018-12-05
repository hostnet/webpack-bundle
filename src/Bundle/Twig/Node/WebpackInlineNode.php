<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Bundle\WebpackBundle\Twig\Node;

use Twig\Compiler;
use Twig\Node\Node;

class WebpackInlineNode extends Node
{
    /**
     * @param array  $attributes An array of attributes (should not be nodes)
     * @param int    $lineno     The line number
     * @param string $tag        The tag name associated with the Node
     */
    public function __construct(array $attributes = [], $lineno = 0, $tag = null)
    {
        parent::__construct([], $attributes, $lineno, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function compile(Compiler $compiler)
    {
        if (false !== ($file = $this->getAttribute('js_file'))) {
            $compiler
                ->write('echo ')
                ->string('<script type="text/javascript" src="' . $file . '"></script>')
                ->raw(";\n");
        }
        if (false !== ($file = $this->getAttribute('css_file'))) {
            $compiler
                ->write('echo ')
                ->string('<link rel="stylesheet" href="' . $file . '">')
                ->raw(";\n");
        }
    }
}
