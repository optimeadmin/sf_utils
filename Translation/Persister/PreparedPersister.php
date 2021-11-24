<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation\Persister;

use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\TranslatableListener;
use Optime\Util\Entity\Event;
use Optime\Util\Translation\TranslatableContent;
use Optime\Util\Translation\TranslationsAwareInterface;

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
     * @var TranslatableListener
     */
    private $listener;
    /**
     * @var TranslationsAwareInterface
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
        TranslatableListener $listener,
        TranslationsAwareInterface $entity,
        array $locales,
        string $defaultLocale
    ) {
        $this->repository = $repository;
        $this->listener = $listener;
        $this->entity = $entity;
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
    }

    public function persist(string $property, TranslatableContent $translations): void
    {
        $listenerLocale = $this->listener->getListenerLocale();
        if ($listenerLocale != $this->defaultLocale){
            $this->listener->setTranslatableLocale($this->defaultLocale);
        }

        foreach ($this->locales as $locale) {
            if ($locale != $this->defaultLocale) {
                $value = $translations->byLocale($locale);
                $this->repository->translate($this->entity, $property, $locale, $value);
            }
        }

        $this->listener->setTranslatableLocale($listenerLocale);
    }
}