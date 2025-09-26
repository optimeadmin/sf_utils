<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\DependencyInjection;

use Optime\Util\Http\Request\AjaxChecker;
use Optime\Util\Serializer\Normalizer\FormErrorNormalizer;
use Optime\Util\Translation\TranslatableListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Manuel Aguirre
 */
class OptimeUtilExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config'),
            $container->getParameter('kernel.environment'),
        );
        $loader->load('services.yaml');

        $container->setParameter('optime.sf_utils.default_locale', $config['default_locale']);
        $container->setParameter('optime.sf_utils.locales', $config['locales']);
        $container->setParameter(
            'optime.sf_utils.use_translations_extension',
            $config['use_translations_extension']
        );
        $container->setParameter(
            'optime.sf_utils.use_ajax_twig_loader',
            $config['use_ajax_twig_loader']
        );

        $container
            ->getDefinition(AjaxChecker::class)
            ->setArgument(1, $config['ajax_check']);

        $container->getDefinition(TranslatableListener::class)
            ->setArgument(0, $config['use_translations_extension']);

        if (!$config['use_form_error_normalizer']) {
            $container->removeDefinition(FormErrorNormalizer::class);
        }

        $container->setParameter(
            'optime.sf_utils.mailer_allowed_recipients',
            $config['mailer']['allowed_recipients']
        );
        $container->setParameter(
            'optime.sf_utils.mailer_fallback_recipients',
            $config['mailer']['fallback_recipients']
        );
    }
}