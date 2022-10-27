<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Twig\Extension;

use Optime\Util\Translation\FlashMessageParser;
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
    public function __construct(
        private readonly FlashMessageParser $parser
    ) {
    }

    public function getFilters()
    {
        return [
            new TwigFilter('parse_flash', [$this, 'parseFlash'], ['is_safe' => ['html']]),
        ];
    }

    public function parseFlash($message, bool $stripTags = true): string
    {
        return $this->parser->parse($message, $stripTags);
    }
}