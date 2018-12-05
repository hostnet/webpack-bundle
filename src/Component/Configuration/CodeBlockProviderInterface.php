<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Configuration;

interface CodeBlockProviderInterface
{
    /**
     * Returns the CodeBlock for this plugin.
     *
     * @return CodeBlock[]
     */
    public function getCodeBlocks();
}
