<?php
namespace Hostnet\Component\Webpack\Asset;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Exports resources from the 'public' directory to web/bundles/<bundle_name> whenever a resource has been changed.
 *
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class Dumper
{
    private $logger;
    private $bundle_paths;
    private $public_dir;
    private $output_dir;

    /**
     * @param LoggerInterface $logger
     * @param array           $bundle_paths
     * @param string          $public_dir
     * @param string          $output_dir
     */
    public function __construct(LoggerInterface $logger, array $bundle_paths, $public_dir, $output_dir)
    {
        $this->logger       = $logger;
        $this->bundle_paths = $bundle_paths;
        $this->public_dir   = $public_dir;
        $this->output_dir   = $output_dir;
    }

    /**
     * Iterates through resources and dump all modified resources to the bundle directory in web/
     *
     * @param Filesystem $fs
     */
    public function dump(Filesystem $fs)
    {
        foreach ($this->bundle_paths as $name => $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $this->public_dir)) {
                $this->dumpBundle($fs, $name, $path . DIRECTORY_SEPARATOR . $this->public_dir);
            }
        }
    }

    /**
     * @param string $name
     * @param string $files
     */
    private function dumpBundle(Filesystem $fs, $name, $path)
    {
        $target_dir = $this->normalize($this->getTargetDir($name));
        $path       = $this->normalize($path);

        $this->logger->info(sprintf('Dumping public assets for "%s" to "%s"...', $name, $target_dir));

        // Start by creating the output directory if it doesn't already exists.
        if (! $fs->exists($this->output_dir)) {
            $fs->mkdir($this->output_dir, 0775);
        }

        // Copy on Windows must be set to true for safety reasons. Although symlinks are supported, they become hard-
        // links instead of soft-links. This means that removing a symlink would essentially also remove any and all
        // files from the source path.
        $is_windows = strpos(strtoupper(php_uname('s')), 'WIN') === 0;
        $fs->mirror($path, $target_dir, null, ['override' => true, 'copy_on_windows' => $is_windows, 'delete' => true]);
    }

    /**
     * @param  string $name
     * @return string
     */
    private function getTargetDir($name)
    {
        if (substr($name, strlen($name) - 6) === 'Bundle') {
            $name = substr($name, 0, strlen($name) - 6);
        }

        return $this->output_dir . DIRECTORY_SEPARATOR . strtolower($name);
    }

    private function normalize($path)
    {
        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
    }
}
