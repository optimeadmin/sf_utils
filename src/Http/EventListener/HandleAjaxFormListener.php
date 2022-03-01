<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Http\EventListener;


use Optime\Util\Form\Extension\HandleAjaxFormExtension;
use Optime\Util\Http\Controller\HandleAjaxForm;
use Optime\Util\Http\Request\AjaxChecker;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use function count;

/**
 * @author Manuel Aguirre
 */
class HandleAjaxFormListener extends AbstractControllerAttributeListener
{
    public function __construct(
        private HandleAjaxFormExtension $formRuntimeExtension,
        AjaxChecker $ajaxChecker,
    ) {
        parent::__construct($ajaxChecker);
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $attributes = $this->getAttributesIfApply($event, HandleAjaxForm::class);

        if (0 === count($attributes)) {
            return;
        }

        $attribute = $attributes[0]?->newInstance();

        if (!$attribute instanceof HandleAjaxForm) {
            return;
        }

        $this->formRuntimeExtension->activate();
        $this->apply($attribute);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->hasAttribute()) {
            return;
        }

        $forms = $this->formRuntimeExtension->getRegisteredForms();

        if (0 === count($forms)) {
            return;
        }

        /** @var HandleAjaxForm $attribute */
        $attribute = $this->getFirstAttribute();

        if (null === $attribute->getType()) {
            $form = $forms[0] ?? null;
        } else {
            $form = $this->getFirstFormByType($forms, $attribute->getType());
        }

        if (!$form || !$form->isSubmitted()) {
            return;
        }

        $response = $event->getResponse();

        if (!$form->isValid()) {
            $response->setStatusCode($attribute->getInvalidStatus());
        } elseif ($attribute->isPreventRedirect()) {
            $event->setResponse(new Response(
                $attribute->isReplaceRedirectContent()
                    ? 'Ok'
                    : $response->getContent()
            ));
        }
    }

    private function getFirstFormByType(iterable $forms, string $type): ?FormInterface
    {
        foreach ($forms as $form) {
            if ($form->getConfig()->getType()->getInnerType()::class == $type) {
                return $form;
            }
        }

        return null;
    }
}