<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

use JsonSerializable;
use Optime\Util\Entity\Language;
use Traversable;

/**
 * @author Manuel Aguirre
 */
class TranslatableContent implements \IteratorAggregate, JsonSerializable
{
    private array $values;
    private ?object $target = null;
    private string $defaultLocale;
    private bool $pending = false;
    private ?string $property = null;

    public function __construct(array $values, string $defaultLocale)
    {
        $this->setValues($values);
        $this->defaultLocale = $defaultLocale;
    }

    public function getTarget(): ?object
    {
        return $this->target;
    }

    public static function fromExistentData(array $values, object $target, string $defaultLocale): self
    {
        $content = new static($values, $defaultLocale);
        $content->target = $target;

        return $content;
    }

    public static function fromString(string $value, string $defaultLocale): self
    {
        return new static([$defaultLocale => $value], $defaultLocale);
    }

    public static function pending(object $target, string $property): self
    {
        $content = new static([], '');
        $content->target = $target;
        $content->property = $property;
        $content->pending = true;

        return $content;
    }

    public function isEdit(): bool
    {
        return null !== $this->target;
    }

    public function isPending(): bool
    {
        return $this->pending;
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValues(array $values): void
    {
        $this->values = array_filter($values);
    }

    public function byLocale(string $locale): ?string
    {
        return $this->values[$locale] ?? null;
    }

    public function hasLocale(string $locale): bool
    {
        return array_key_exists($locale, $this->values);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->getValues());
    }

    public function __toString()
    {
        return (string)$this->byLocale($this->defaultLocale);
    }

    public function isEmpty(): bool
    {
        return 0 === count($this->getValues());
    }

    public function jsonSerialize(): mixed
    {
        return $this->getValues();
    }
}