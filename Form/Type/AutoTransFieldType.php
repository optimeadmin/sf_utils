<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Form\Type;

use Optime\Util\Form\DataMapper\AutoTransDataMapper;
use Optime\Util\Form\DataMapper\AutoTransDataTransformer;
use Optime\Util\Translation\Translation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @author Manuel Aguirre
 */
class AutoTransFieldType extends AbstractType
{
    /**
     * @var Translation
     */
    private $translation;

    public function __construct(Translation $translation)
    {
        $this->translation = $translation;
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
    }
}