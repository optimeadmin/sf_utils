<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation;

/**
 * @author Manuel Aguirre
 */
class DefaultLocalesProvider implements LocalesProviderInterface
{
    /**
     * @var string
     */
    private $defaultLocale;
    /**
     * @var array
     */
    private $locales;

    public function __construct(string $defaultLocale, array $locales = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales ?: [];
    }

    public function getLocales(): array
    {
        return $this->locales;
    }

    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }
}