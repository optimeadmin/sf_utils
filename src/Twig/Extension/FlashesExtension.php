<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use function nl2br;
use function preg_replace;
use function strip_tags;

/**
 * @author Manuel Aguirre
 */
class FlashesExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('parse_flash', [$this, 'parseFlash'], ['is_safe' => ['html']]),
        ];
    }

    public function parseFlash($message, bool $stripTags = true): string
    {
        if ($stripTags) {
            $message = strip_tags($message, '<br>');
        }

        $message = preg_replace('/\*\*(.+?)\*\*/', '<b>$1</b>', $message);
        $message = preg_replace('/\*(.+?)\*/', '<i>$1</i>', $message);

        return nl2br($message);
    }
}