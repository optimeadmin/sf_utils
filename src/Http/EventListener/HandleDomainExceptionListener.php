<?php

declare(strict_types=1);

namespace Optime\Util\Http\EventListener;

use Optime\Util\Exception\DomainException;
use Optime\Util\Exception\ValidationException;
use Optime\Util\Http\Controller\HandleDomainException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class HandleDomainExceptionListener extends AbstractControllerAttributeListener
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly TranslatorInterface $translator,
    ) {
        parent::__construct();
    }

    #[AsEventListener]
    public function onKernelController(ControllerEvent $event): void
    {
        $attributes = $this->getAttributesIfApply($event, HandleDomainException::class, false);

        if (0 === count($attributes)) {
            return;
        }

        $attribute = $attributes[0]?->newInstance();

        if (!$attribute instanceof HandleDomainException) {
            return;
        }

        $this->apply($attribute);
    }

    #[AsEventListener]
    public function onKernelException(ExceptionEvent $event): void
    {
        if (!($attribute = $this->getFirstAttribute()) instanceof HandleDomainException) {
            return;
        }

        $exception = $event->getThrowable();

        if (!$exception instanceof DomainException) {
            return;
        }

        if ($exception instanceof ValidationException) {
            $event->setResponse(new JsonResponse(
                $this->serializer->serialize($exception->getErrors(), 'json'),
                $attribute->statusCode ?? 422,
                json: true,
            ));

            return;
        }

        $errors = ValidationException::create($exception->trans($this->translator), 'root');
        $event->setResponse(new JsonResponse(
            $this->serializer->serialize($errors->getErrors(), 'json'),
            $attribute->statusCode ?? 422,
            json: true,
        ));

    }
}