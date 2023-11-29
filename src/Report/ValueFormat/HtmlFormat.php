<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report\ValueFormat;

use PhpOffice\PhpSpreadsheet\Helper\Html;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

/**
 * @author Manuel Aguirre
 */
class HtmlFormat extends StringFormat
{
    public function __construct(private RichText $richText, bool $centered = false)
    {
        parent::__construct((string)$this->richText, $centered);
    }

    public static function fromString(string $content, bool $centered = false): self
    {
        $htmlHelper = new Html();

        return new self($htmlHelper->toRichTextObject($content), $centered);
    }

    public function getRichText(): RichText
    {
        return $this->richText;
    }
}