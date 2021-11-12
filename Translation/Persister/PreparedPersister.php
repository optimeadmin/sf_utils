<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation\Persister;

use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Optime\Util\Entity\Event;
use Optime\Util\Translation\TranslatableContent;

/**
 * @author Manuel Aguirre
 */
class PreparedPersister
{
    /**
     * @var TranslationRepository
     */
    private $repository;
    /**
     * @var object
     */
    private $entity;
    /**
     * @var array
     */
    private $locales;
    /**
     * @var string
     */
    private $defaultLocale;

    public function __construct(
        TranslationRepository $repository,
        object $entity,
        array $locales,
        string $defaultLocale
    ) {
        $this->repository = $repository;
        $this->entity = $entity;
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
    }

    public function persist(string $property, TranslatableContent $translations): void
    {
        foreach ($this->locales as $locale) {
            if ($locale != $this->defaultLocale) {
                $value = $translations->byLocale($locale);
                $this->repository->translate($this->entity, $property, $locale, $value);
            }
        }
    }
}