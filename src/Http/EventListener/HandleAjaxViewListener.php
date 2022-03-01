<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Http\EventListener;


use Optime\Util\Http\Controller\PartialAjaxView;
use Optime\Util\Http\Request\AjaxChecker;
use Optime\Util\Twig\Extension\Runtime\AjaxViewRuntime;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use function count;
use function dump;

/**
 * @author Manuel Aguirre
 */
class HandleAjaxViewListener extends AbstractControllerAttributeListener
{
    public function __construct(
        private AjaxViewRuntime $runtimeExtension,
        AjaxChecker $ajaxChecker,
    ) {
        parent::__construct($ajaxChecker);
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $attributes = $this->getAttributesIfApply($event, PartialAjaxView::class);

        if (0 === count($attributes)) {
            return;
        }

        $attribute = $attributes[0]?->newInstance();

        if (!$attribute instanceof PartialAjaxView) {
            return;
        }

        $this->apply($attribute);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->hasAttribute()) {
            return;
        }

        if (null !== ($content = $this->runtimeExtension->getPartialContent())) {
            $event->getResponse()->setContent($content);
        }
    }
}