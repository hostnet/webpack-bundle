<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Bundle\WebpackBundle\Twig\Node;

use Twig\Compiler;
use Twig\Node\Node;

class WebpackNode extends Node
{
    /**
     * {@inheritdoc}
     */
    public function compile(Compiler $compiler)
    {
        foreach ($this->getAttribute('files') as $file) {
            $compiler->write('$context["asset"] = "' . $file . '";');
            $this->nodes[0]->compile($compiler);
        }
    }
}
