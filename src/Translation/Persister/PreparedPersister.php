<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Translation\Persister;

use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Optime\Util\Entity\Event;
use Optime\Util\Translation\LocalesProviderInterface;
use Optime\Util\Translation\TranslatableContent;
use Optime\Util\Translation\TranslatableListener;
use Optime\Util\Translation\TranslationsAwareInterface;

/**
 * @author Manuel Aguirre
 */
class PreparedPersister
{
    public function __construct(
        private TranslationRepository $repository,
        private TranslatableListener $listener,
        private LocalesProviderInterface $localesProvider,
        private TranslationsAwareInterface $entity,
    ) {
    }

    public function persist(string $property, TranslatableContent $translations): void
    {
        $listenerLocale = $this->listener->getListenerLocale();
        if ($listenerLocale != $this->localesProvider->getDefaultLocale()) {
            $this->listener->setTranslatableLocale($this->localesProvider->getDefaultLocale());
        }

        foreach ($this->localesProvider->getLocales() as $locale) {
            if ($locale != $this->localesProvider->getDefaultLocale()) {
                $value = $translations->byLocale($locale);
                $this->repository->translate($this->entity, $property, $locale, $value);
            }
        }

        $this->listener->setTranslatableLocale($listenerLocale);
    }
}