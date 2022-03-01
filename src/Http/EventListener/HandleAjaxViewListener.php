<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Http\EventListener;


use Optime\Util\Http\Controller\PartialAjaxView;
use Optime\Util\Http\Request\AjaxChecker;
use Optime\Util\Twig\Extension\Runtime\AjaxViewRuntime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use function count;
use function is_array;
use function strlen;

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

        $this->apply(array_map(fn($attr) => $attr->newInstance(), $attributes));
        $this->runtimeExtension->activate();
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->hasAttribute() || !$this->runtimeExtension->hasContents()) {
            return;
        }

        $attribute = null;
        $request = $event->getRequest();

        /** @var PartialAjaxView $attribute */
        foreach ($this->getAttributes() as $attr) {
            if (null === $attr->getMethod() || $request->isMethod($attr->getMethod())) {
                $attribute = $attr;
                break;
            }
        }

        if (null === $attribute) {
            return;
        }

        $partialName = $attribute->getName();

        if (is_array($partialName)) {
            $contents = [];

            foreach ($partialName as $name) {
                $contents[$name] = $this->runtimeExtension->getPartialContent($name);
            }

            $event->setResponse(new JsonResponse($contents));
        } else {
            $content = $this->runtimeExtension->getPartialContent($partialName);

            if (0 === strlen($content) && $attribute->isIgnoreOnEmpty()) {
                return;
            }

            $event->getResponse()->setContent($content);
        }
    }
}