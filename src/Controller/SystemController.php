<?php

declare(strict_types=1);

namespace Optime\Util\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_CACHE_ADMIN") or is_granted("ROLE_SUPER_ADMIN")'))]
class SystemController extends AbstractController
{
    #[Route('/', name: 'optime_util_system')]
    public function index(): Response
    {
        $opcacheInfo = @opcache_get_status(false) ?: null;

        return $this->render('@OptimeUtil/system/home.html.twig', [
            'opcache' => $opcacheInfo,
        ]);
    }

    #[Route('/phpinfo', name: 'optime_util_system_phpinfo')]
    public function phpinfo(): never
    {
        phpinfo();
        die;
    }

    #[Route('/opcache-clear', name: 'optime_util_system_clear_opcache')]
    public function clearOpCache(): Response
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();

            $this->addFlash('success', 'Opcache cleared');
        } else {
            $this->addFlash('warning', 'Opcache is not enabled');
        }

        return $this->redirectToRoute('optime_util_system');
    }

    #[Route('/cache-clear', name: 'optime_util_system_clear_cache')]
    public function clearAppCache(
        #[Autowire('%kernel.cache_dir%')] string $cacheDir,
        EventDispatcherInterface $eventDispatcher,
    ): Response {
        $eventDispatcher->addListener(KernelEvents::TERMINATE, function () use ($cacheDir) {
            $filesystem = new Filesystem();
            // check if cache dir exists and is removable
            if (!$filesystem->exists($cacheDir) || !is_writable($cacheDir)) {
                return;
            }

            sleep(2);
            $filesystem->remove($cacheDir);
            sleep(1);
        }, -5000);

        $this->addFlash('success', 'Cache Cleared');

        return $this->redirectToRoute('optime_util_system');
    }
}
