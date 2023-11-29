<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Report\ValueFormat;

use DateTimeImmutable;
use function array_map;
use function count;

/**
 * @author Manuel Aguirre
 */
class ReportInfo
{
    private DateTimeImmutable $generatedAt;
    private array $subtitles = [];
    private ?string $tabName = null;
    private ?string $headersBgColor = null;
    private bool $print = true;
    private int $minRowsCount = 0;

    public function __construct(private StringFormat $title)
    {
        $this->title->textBold();
        $this->setGeneratedAt(new DateTimeImmutable());
    }

    public function getRowsCount(): int
    {
        if (!$this->canBePrinted()) {
            return $this->minRowsCount + 1;
        }

        return 2 + count($this->subtitles) + $this->minRowsCount;
    }

    public function setMinRowsCount(int $minRowsCount): void
    {
        $this->minRowsCount = $minRowsCount;
    }

    public function setSubtitles(array $subtitles): void
    {
        $this->subtitles = $subtitles;
    }

    public function getSubtitles(): array
    {
        return array_map(
            fn($value) => $value instanceof StringFormat ? $value : new StringFormat($value),
            $this->subtitles,
        );
    }

    public function setTitle(StringFormat $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): StringFormat
    {
        return $this->title;
    }

    public function setGeneratedAt(DateTimeImmutable $generatedAt): void
    {
        $this->generatedAt = $generatedAt;
    }

    public function getGeneratedAt(): DateTimeImmutable
    {
        return $this->generatedAt;
    }

    public function canBePrinted(): bool
    {
        return $this->print;
    }

    public function cancelPrint(): void
    {
        $this->print = false;
    }

    public function setTabName(string $tabName): void
    {
        $this->tabName = $tabName;
    }

    public function getTabName(): ?string
    {
        return $this->tabName;
    }

    public function setHeadersBgColor(?string $headersBgColor): void
    {
        $this->headersBgColor = $headersBgColor;
    }

    public function getHeadersBgColor(): ?string
    {
        return $this->headersBgColor;
    }
}