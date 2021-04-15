<?php

namespace Vendi\ThumbnailSizesFromYaml;

final class ThumbnailSize
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $width;

    /**
     * @var int
     */
    public $height;

    /**
     * @var bool|array
     */
    public $crop;

    /**
     * @var int[]
     */
    public $multipliers = [];

    /**
     * ThumbnailSize constructor.
     * @param string $name
     * @param int|string $width
     * @param int|string $height
     * @param array|bool $crop
     * @param int[] $multipliers
     */
    public function __construct(string $name, $width, $height, $crop, array $multipliers)
    {
        $this->name = $name;
        $this->width = $this->force_int($width);
        $this->height = $this->force_int($height);
        $this->crop = $this->fix_crop($crop);
        foreach ($multipliers as $multiplier) {
            if ($i = $this->force_int($multiplier)) {
                $this->multipliers[] = $i;
            }
        }
    }

    /**
     * @param $value
     * @param bool $default
     * @return array|bool
     */
    private function fix_crop($value, bool $default = false)
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $value;
        }

        return $default;
    }

    private function force_int($value, int $default = 0): int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : $default;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int)$value;
        }

        return $default;
    }
}