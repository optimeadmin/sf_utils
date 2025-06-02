<?php

declare(strict_types=1);

namespace Optime\Util\Serializer;

use Optime\Util\Validator\DomainValidator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\GroupSequence;

class RequestDeserializer
{
    public function __construct(
        private readonly SerializerInterface&DenormalizerInterface $serializer,
        private readonly RequestStack $requestStack,
        private readonly DomainValidator $validator,
    ) {
    }

    public function fromPayload(
        string|object $target,
        array $context = [],
        string|GroupSequence|array|null|false|callable $validationGroups = null
    ): object {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            throw new \RuntimeException('No current request available.');
        }


        $payload = $request->getPayload()->all();

        if (is_string($target)) {
            $targetClass = $target;
        } else {
            $targetClass = get_class($target);
            $context['object_to_populate'] = $target;
        }

        $object = $this->serializer->denormalize($payload, $targetClass, null, $context);

        if (false !== $validationGroups) {
            if (is_callable($validationGroups)) {
                $validationGroups = $validationGroups($object);
            }

            $this->validator->handle($object, null, $validationGroups);
        }

        return $object;
    }
}