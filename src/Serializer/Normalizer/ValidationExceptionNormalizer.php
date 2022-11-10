<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Serializer\Normalizer;

use Optime\Util\Exception\ValidationException;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Manuel Aguirre
 */
class ValidationExceptionNormalizer implements ContextAwareNormalizerInterface
{
    public const MULTIPLE = 'multiple';
    public const INDEXED = 'indexed';

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof ValidationException;
    }

    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        /** @var ConstraintViolationListInterface $errors */
        $errors = $object->getErrors();
        $data = [];
        $invalidProperties = [];

        $indexed = (bool)($context[self::INDEXED] ?? false);
        $multiple = (bool)($context[self::MULTIPLE] ?? false);

        /** @var ConstraintViolationInterface $error */
        foreach ($errors as $error) {
            $index = $error->getPropertyPath() ?? '__root__';

            if (isset($invalidProperties[$index]) && !$multiple) {
                continue;
            }

            $invalidProperties[$index] = true;

            if ($indexed) {
                if ($multiple) {
                    $data[$index] ??= [];
                    $data[$index][] = $this->normalizeError($error);
                } else {
                    $data[$index] = $this->normalizeError($error);
                }
            } else {
                $data[] = $this->normalizeError($error);
            }
        }

        return $data;
    }

    private function normalizeError(ConstraintViolationInterface $error): array
    {
        $message = $this->translator->trans($error->getMessage(), $error->getParameters(), 'validators');

        return [
            'message' => $message,
            'property' => $error->getPropertyPath(),
        ];
    }
}