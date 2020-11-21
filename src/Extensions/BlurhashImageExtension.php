<?php

namespace Chrometoaster\Blurhash\Extensions;

use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataExtension;
use kornrunner\Blurhash\Blurhash;

/**
 * Class BlurhashImageExtension
 *
 * An extension adding blurha.sh data to an image via onBeforeWrite hook.
 */
class BlurhashImageExtension extends DataExtension
{
    /**
     * Number of horizontal components
     * https://github.com/woltapp/blurhash#how-do-i-pick-the-number-of-x-and-y-components
     *
     * @var int
     */
    private static $components_x = 4;

    /**
     * Number of vertical components
     * https://github.com/woltapp/blurhash#how-do-i-pick-the-number-of-x-and-y-components
     *
     * @var int
     */
    private static $components_y = 3;

    /**
     * @var string[]
     */
    private static $db = [
        'Blurhash' => 'Varchar(50)',
    ];


    /**
     * Hook it in!
     */
    public function onBeforeWrite()
    {
        /** @var Image $image */
        $image = $this->getOwner();

        $img    = imagecreatefromstring($image->getString());
        $width  = $image->getWidth();
        $height = $image->getHeight();

        $pixels = [];
        for ($y = 0; $y < $height; ++$y) {
            $row = [];
            for ($x = 0; $x < $width; ++$x) {
                $index  = imagecolorat($img, $x, $y);
                $colors = imagecolorsforindex($img, $index);

                $row[] = [$colors['red'], $colors['green'], $colors['blue']];
            }
            $pixels[] = $row;
        }

        $cfg = Config::forClass(self::class);
        $image->setField('Blurhash', Blurhash::encode($pixels, $cfg->get('components_x'), $cfg->get('components_y')));
    }
}
