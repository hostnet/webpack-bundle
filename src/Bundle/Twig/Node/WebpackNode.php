<?php
declare(strict_types = 1);
namespace Hostnet\Bundle\WebpackBundle\Twig\Node;

use Twig\Compiler;
use Twig\Node\Node;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackNode extends Node
{
    /** {@inheritdoc} */
    public function compile(Compiler $compiler)
    {
        foreach ($this->getAttribute('files') as $file) {
            $compiler->write('$context["asset"] = "'. $file .'";');
            $this->nodes[0]->compile($compiler);
        }
    }
}
