<?php
/**
 * @copyright 2017-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Asset;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * This class tracks a set of files, and offers a function to see if one of the files is changed after a certain time.
 */
class TrackedFiles
{
    /**
     * The last modified time for the set of files, in a Unix timestamp.
     *
     * @var int
     */
    private $modification_time;

    /**
     * Track a set of paths
     *
     * @param array $paths the list of directories or files to track / follow
     */
    public function __construct(array $paths)
    {
        //Filter out the files, the Finder class can not handle files in the ->in() call.
        $files = array_filter($paths, 'is_file');

        //Filter out the directories to be used for searching using the Filter class.
        $dirs = array_filter($paths, 'is_dir');

        $finder = new Finder();

        //Add the given 'stand-alone-files'
        $finder->append($files);

        //Add the Directores recursively
        $finder = $finder->in($dirs);

        //Filter out non readable files
        $finder = $finder->filter(
            function (SplFileInfo $finder) {
                return $finder->isReadable();
            }
        );

        //Loop through all the files and save the latest modification time.
        foreach ($finder->files() as $file) {
            /**@var $file \SplFileInfo */
            if ($this->modification_time < $file->getMTime()) {
                $this->modification_time = $file->getMTime();
            }
        }
    }

    /**
     * Is one of the Tracked files in this set changed later than the other set.
     *
     * @param TrackedFiles $other the other set of files to compare to.
     * @return bool true if this set if this set is modified after (later) the other set.
     */
    public function modifiedAfter(TrackedFiles $other): bool
    {
        return $this->modification_time > $other->modification_time;
    }
}
