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

        return $this->fromRawData($target, $payload, $context, $validationGroups);
    }

    public function fromRawData(
        string|object $target,
        array|string $data,
        array $context = [],
        string|GroupSequence|array|null|false|callable $validationGroups = null
    ): object {
        if (is_string($target)) {
            $targetClass = $target;
        } else {
            $targetClass = get_class($target);
            $context['object_to_populate'] = $target;
            $context['deep_object_to_populate'] = true;
        }

        if (is_string($data)) {
            $object = $this->serializer->deserialize($data, $targetClass, 'json', $context);
        } else {
            $object = $this->serializer->denormalize($data, $targetClass, null, $context);
        }

        if (false !== $validationGroups) {
            if (is_callable($validationGroups)) {
                $validationGroups = $validationGroups($object);
            }

            $this->validator->handle($object, null, $validationGroups);
        }

        return $object;
    }
}