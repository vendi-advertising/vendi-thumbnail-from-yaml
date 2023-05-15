<?php

namespace Vendi\ThumbnailSizesFromYaml;

final class ThumbnailSize
{
    public string $name;

    public int $width;

    public int $height;

    public array|bool $crop;

    /**
     * @var int[]
     */
    public array $multipliers = [];

    /**
     * ThumbnailSize constructor.
     * @param string $name
     * @param int|string $width
     * @param int|string $height
     * @param bool|array $crop
     * @param int[] $multipliers
     */
    public function __construct(string $name, mixed $width, mixed $height, mixed $crop, array $multipliers)
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

    private function fix_crop(mixed $value): bool|array
    {
        $default = false;

        if (is_bool($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $value;
        }

        return $default;
    }

    private function force_int(mixed $value): int
    {
        $default = 0;

        if (is_int($value)) {
            return $value >= 0 ? $value : $default;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int)$value;
        }

        return $default;
    }
}