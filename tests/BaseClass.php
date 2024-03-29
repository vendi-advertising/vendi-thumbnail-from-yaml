<?php

declare(strict_types=1);

namespace Vendi\ThumbnailSizesFromYaml\tests;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class BaseClass extends TestCase
{
    //This is name of our FS root for testing
    private string $_test_root_name = 'vendi-cpt-loader-test';

    //This is an instance of the Virtual File System
    private ?vfsStreamDirectory $_root = null;

    public function get_vfs_root(): vfsStreamDirectory
    {
        if (!$this->_root) {
            $this->_root = vfsStream::setup(
                $this->get_root_dir_name_no_trailing_slash()
            );
        }

        return $this->_root;
    }

    public function get_root_dir_name_no_trailing_slash(): string
    {
        return $this->_test_root_name;
    }

    public function setUp(): void
    {
        global $current_test_dir;
        $current_test_dir = null;
        $this->get_vfs_root();
        $this->reset_env();
    }

    public function tearDown(): void
    {
        global $current_test_dir;
        $current_test_dir = null;
        $this->reset_env();
    }

    private function reset_env(): void
    {
        \putenv('THUMBNAIL_YAML_FILE');
    }
}