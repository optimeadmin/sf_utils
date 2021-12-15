<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Form\Type;

use Optime\Util\Form\DataMapper\AutoTransDataMapper;
use Optime\Util\Form\DataMapper\AutoTransDataTransformer;
use Optime\Util\Translation\Translation;
use Optime\Util\Translation\TranslationsFormHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Manuel Aguirre
 */
class AutoTransFieldType extends AbstractType
{
    /**
     * @var Translation
     */
    private $translation;
    /**
     * @var TranslationsFormHandler
     */
    private $formHandler;

    public function __construct(Translation $translation, TranslationsFormHandler $formHandler)
    {
        $this->translation = $translation;
        $this->formHandler = $formHandler;
    }

    public function getParent()
    {
        return TranslatableContentType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $propertyPath = $builder->getPropertyPath() ?: ($options['property_path'] ?? $builder->getName());
        $builder->setPropertyPath($builder->getName());

        $transformer = new AutoTransDataTransformer(
            $this->translation, $propertyPath, $options['mapped']
        );

        $builder->addModelTransformer($transformer);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($transformer) {
            $root = $event->getForm()->getParent()->getData();

            if (is_object($root)) {
                $transformer->setTargetObject($root);
            }
        });

        if ($options['auto_save']) {
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $this->formHandler->forAutoSave($event->getForm());
            });
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('auto_save', false);
        $resolver->setAllowedTypes('auto_save', 'bool');
    }
}