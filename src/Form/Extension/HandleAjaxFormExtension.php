<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @author Manuel Aguirre
 */
class HandleAjaxFormExtension extends AbstractTypeExtension
{
    private array $registeredForms = [];
    private bool $active = false;

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function activate(): void
    {
        $this->active = true;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$this->active) {
            return;
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            if ($event->getForm()->isRoot()) {
                $this->registeredForms[] = $event->getForm();
            }
        });
    }

    public function getRegisteredForms(): array
    {
        return $this->registeredForms;
    }
}