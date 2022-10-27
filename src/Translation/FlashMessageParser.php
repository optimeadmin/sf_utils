<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Translation;

use Stringable;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function nl2br;
use function preg_replace;
use function strip_tags;

/**
 * @author Manuel Aguirre
 */
class FlashMessageParser
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function parse(string|Stringable|TranslatableInterface $value, bool $stripTags = true): string
    {
        if ($value instanceof TranslatableInterface) {
            $value = $value->trans($this->translator);
        }

        $message = (string)$value;

        if ($stripTags) {
            $message = strip_tags($message, '<br>');
        }

        $message = preg_replace('/\*\*(.+?)\*\*/', '<b>$1</b>', $message);
        $message = preg_replace('/\*(.+?)\*/', '<i>$1</i>', $message);

        return nl2br($message);
    }

}