<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Asset;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Exports resources from the 'public' directory to <dump_path>/<bundle_name> whenever a resource has been changed.
 */
class Dumper
{
    private $fs;
    private $logger;
    private $bundle_paths;
    private $public_res_path;
    private $output_dir;

    public function __construct(
        Filesystem $fs,
        LoggerInterface $logger,
        array $bundle_paths,
        string $public_res_path,
        string $output_dir
    ) {
        $this->fs              = $fs;
        $this->logger          = $logger;
        $this->bundle_paths    = $bundle_paths;
        $this->public_res_path = $public_res_path;
        $this->output_dir      = $output_dir;
    }

    /**
     * Iterates through resources and dump all modified resources to the bundle directory in the public dir.
     */
    public function dump(): void
    {
        foreach ($this->bundle_paths as $name => $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $this->public_res_path)) {
                $this->dumpBundle($name, $path . DIRECTORY_SEPARATOR . $this->public_res_path);
            }
        }
    }

    /**
     * @param string $name
     * @param string $path
     */
    private function dumpBundle($name, $path): void
    {
        $target_dir = $this->normalize($this->getTargetDir($name));
        $path       = $this->normalize($path);

        $this->logger->info(sprintf('Dumping public assets for "%s" to "%s".', $name, $target_dir));

        // Always copy on windows.
        if (stripos(PHP_OS, 'WIN') === 0) {
            $this->fs->mirror($path, $target_dir, null, [
                'override'        => true,
                'copy_on_windows' => true,
                'delete'          => true,
            ]);

            return;
        }

        // Create a symlink for non-windows platforms.
        try {
            $this->fs->symlink($path, $target_dir);
        } catch (IOException $e) {
            $this->fs->mirror($path, $target_dir);
        }
    }

    /**
     * @param  string $name
     * @return string
     */
    private function getTargetDir($name): string
    {
        if (substr($name, \strlen($name) - 6) === 'Bundle') {
            $name = substr($name, 0, -6);
        }

        return $this->output_dir . DIRECTORY_SEPARATOR . strtolower($name);
    }

    /**
     * Makes sure the path is always the OS directory separator.
     *
     * @param string $path
     * @return mixed
     */
    private function normalize($path)
    {
        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
    }
}
