<?php

namespace Vendi\ThumbnailSizesFromYaml;

use Vendi\YamlLoader\YamlLoaderBaseWithObjectCache;

class ThumbnailLoader extends YamlLoaderBaseWithObjectCache
{
    public function __construct()
    {
        parent::__construct('THUMBNAIL_YAML_FILE', 'thumbnails.yaml', 'thumbnail-config');
    }

    public function is_config_valid(array $config): bool
    {
        return true;
    }

    /**
     * @return ThumbnailSize[]
     */
    public function get_thumbnail_sizes(): array
    {
        $items = [];
        foreach ($this->get_config() as $name => $options) {
            $items[] = new ThumbnailSize(
                $name,
                $options['width'] ?? 0,
                $options['height'] ?? 0,
                $options['crop'] ?? false,
                $options['multipliers'] ?? []
            );
        }

        return $items;
    }

    protected function get_thumbnail_function(): callable
    {
        return static function ($name, $width, $height, $crop) {
            if (function_exists('fly_add_image_size')) {
                fly_add_image_size($name, $width, $height, $crop);
            } elseif (function_exists('add_image_size')) {
                add_image_size($name, $width, $height, $crop);
            } else {
                // TODO
            }
        };
    }

    public function register_all(): void
    {
        $items = $this->get_thumbnail_sizes();
        $func = $this->get_thumbnail_function();

        foreach ($items as $item) {
            $func($item->name, $item->width, $item->height, $item->crop);
            foreach ($item->multipliers as $multiplier) {
                // We'll allow a multiplier for zero, although I don't know if it makes sense or not
                $func(sprintf('%1$s-%2$sx', $item->name, $multiplier), $item->width * $multiplier, $item->height * $multiplier, $item->crop);
            }
        }
    }
}