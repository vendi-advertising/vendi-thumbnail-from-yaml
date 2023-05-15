<?php

namespace Vendi\ThumbnailSizesFromYaml\tests;

use JsonException;
use org\bovigo\vfs\vfsStream;
use Vendi\ThumbnailSizesFromYaml\ThumbnailLoader;
use Vendi\ThumbnailSizesFromYaml\ThumbnailSize;
use Vendi\YamlLoader\YamlLoaderBase;
use Vendi\YamlLoader\YamlLoaderBaseWithObjectCache;
use Symfony\Component\Filesystem\Path;

class Test_YamlLoader extends BaseClass
{
    public function get_simple_mock(string $envVariableForFile = null, string $defaultFileName = null, string $cacheKey = null): YamlLoaderBase
    {
        if (!$envVariableForFile) {
            $envVariableForFile = 'THUMBNAIL_YAML_FILE';
        }

        if (!$defaultFileName) {
            $defaultFileName = 'test-config.yaml';
        }

        if (!$cacheKey) {
            $cacheKey = 'test-cache-key';
        }

        return new class ($envVariableForFile, $defaultFileName, $cacheKey) extends YamlLoaderBaseWithObjectCache {

            public function is_config_valid(array $config): bool
            {
                return true;
            }

            public function get_env_key(): string
            {
                return $this->envVariableForFile;
            }
        };
    }

    /**
     * @throws JsonException
     */
    public function test__thing(): void
    {
        $loader = $this->get_simple_mock();
        $key = $loader->get_env_key();

        global $current_test_dir;
        $current_test_dir = '/cheese/';

        $file = vfsStream::url(Path::join($this->get_root_dir_name_no_trailing_slash(), 'test.yaml'));
        \putenv("${key}=${file}");
        \touch($file);
        file_put_contents(
            $file,
            <<<TAG
normal-crop-true:
  width: 1920
  height: 1281
  crop: true
normal-array:
  crop: [center, bottom]
normal-multiplier:
  width: 100
  height: 100
  multipliers: [ 2 ]
weird-multiplier:
  multipliers: [ 'cheese' ]
TAG
        );

        $loader = new ThumbnailLoader();
        $items = $loader->get_thumbnail_sizes();
        $this->assertCount(4, $items);

        $item = array_shift($items);
        $this->assertSame('normal-crop-true', $item->name);
        $this->assertSame(1920, $item->width);
        $this->assertSame(1281, $item->height);
        $this->assertTrue($item->crop);

        $item = array_shift($items);
        $this->assertSame('normal-array', $item->name);
        $this->assertSame(0, $item->width);
        $this->assertSame(0, $item->height);
        $this->assertSame(['center', 'bottom'], $item->crop);

        $item = array_shift($items);
        $this->assertSame('normal-multiplier', $item->name);
        $this->assertSame(100, $item->width);
        $this->assertSame(100, $item->height);
        $this->assertFalse($item->crop);
        $this->assertSame([2], $item->multipliers);

        $item = array_shift($items);
        $this->assertSame('weird-multiplier', $item->name);
        $this->assertSame(0, $item->width);
        $this->assertSame(0, $item->height);
        $this->assertFalse($item->crop);
        $this->assertSame([], $item->multipliers);

        global $test_results;
        $test_results = [];
        ThumbnailLoader::register_all();

        $this->assertCount(5, $test_results);

        $item = json_decode(array_shift($test_results), true, 512, JSON_THROW_ON_ERROR);
        $item = new ThumbnailSize($item[0], $item[1], $item[2], $item[3], []);
        $this->assertSame('normal-crop-true', $item->name);
        $this->assertSame(1920, $item->width);
        $this->assertSame(1281, $item->height);
        $this->assertTrue($item->crop);

        $item = json_decode(array_shift($test_results), true, 512, JSON_THROW_ON_ERROR);
        $item = new ThumbnailSize($item[0], $item[1], $item[2], $item[3], []);
        $this->assertSame('normal-array', $item->name);
        $this->assertSame(0, $item->width);
        $this->assertSame(0, $item->height);
        $this->assertSame(['center', 'bottom'], $item->crop);

        $item = json_decode(array_shift($test_results), true, 512, JSON_THROW_ON_ERROR);
        $item = new ThumbnailSize($item[0], $item[1], $item[2], $item[3], []);
        $this->assertSame('normal-multiplier', $item->name);
        $this->assertSame(100, $item->width);
        $this->assertSame(100, $item->height);
        $this->assertFalse($item->crop);

        $item = json_decode(array_shift($test_results), true, 512, JSON_THROW_ON_ERROR);
        $item = new ThumbnailSize($item[0], $item[1], $item[2], $item[3], []);
        $this->assertSame('normal-multiplier-2x', $item->name);
        $this->assertSame(200, $item->width);
        $this->assertSame(200, $item->height);
        $this->assertFalse($item->crop);

        $item = json_decode(array_shift($test_results), true, 512, JSON_THROW_ON_ERROR);
        $item = new ThumbnailSize($item[0], $item[1], $item[2], $item[3], []);
        $this->assertSame('weird-multiplier', $item->name);
        $this->assertSame(0, $item->width);
        $this->assertSame(0, $item->height);
        $this->assertFalse($item->crop);
        $this->assertSame([], $item->multipliers);
    }
}