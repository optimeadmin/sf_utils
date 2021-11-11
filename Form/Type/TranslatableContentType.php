<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Form\Type;

use Optime\Util\Translation\LocalesProviderInterface;
use Optime\Util\Translation\TranslatableContent;
use Optime\Util\Translation\TranslatableContentFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function array_merge;
use function count;
use function strtoupper;

/**
 * @author Manuel Aguirre
 */
class TranslatableContentType extends AbstractType
{
    /**
     * @var LocalesProviderInterface
     */
    private $localesProvider;
    /**
     * @var TranslatableContentFactory
     */
    private $contentFactory;

    public function __construct(
        LocalesProviderInterface $localesProvider,
        TranslatableContentFactory $contentFactory
    ) {
        $this->localesProvider = $localesProvider;
        $this->contentFactory = $contentFactory;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('type', TextType::class);
        $resolver->setDefault('data_class', TranslatableContent::class);
        $resolver->setDefault('error_bubbling', false);
        $resolver->setDefault('col', 12);
        $resolver->setDefault('item_options', []);
        $resolver->setAllowedTypes('item_options', 'array');
        $resolver->setAllowedTypes('col', ['int', 'string']);

        $resolver->setDefault('empty_data', function (FormInterface $form) {
            $values = [];

            foreach ($form as $key => $value) {
                $values[$key] = $value->getData();
            }

            return $this->contentFactory->newInstance($values);
        });

        $resolver->setNormalizer('col', function (Options $options, $value) {
            if (!is_numeric($value) && 'auto' !== $value) {
                return 12;
            }

            return $value;
        });

        $resolver->setNormalizer('item_options', function (Options $options, $value) {
            return $value + [
                'required' => $options['required'],
                ];
        });

        $resolver->setDefault('default_data', null);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locales = $this->localesProvider->getLocales();
        $formType = $options['type'];

        foreach ($locales as $locale) {
            $builder->add($locale, $formType, array_merge($options['item_options'], [
                'label' => strtoupper($locale),
                'property_path' => sprintf('values[%s]', $locale),
            ]));
        }

        if ($options['default_data']) {
            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($options) {
                    if (null == $event->getData()) {
                        $event->setData($this->contentFactory->filledWith($options['default_data']));
                    }
                }
            );
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $locales = $this->localesProvider->getLocales();

        /** @var TranslatableContent|null $data */
        $data = $form->getViewData();
        $isEdit = $data ? $data->isEdit() : false;

        $view->vars['locales_count'] = count($locales);
        $view->vars['col'] = $options['col'];

        foreach ($locales as $locale) {
            $view[$locale]->vars['block_prefixes'][] = "translatable_content_entry";
            $view[$locale]->vars['row_attr'] = array_merge($view[$locale]->vars['row_attr'] ?? [], [
                'class' => 'translatable-content-row',
                'data-translatable-content-row' => $locale,
            ]);
        }
    }
}