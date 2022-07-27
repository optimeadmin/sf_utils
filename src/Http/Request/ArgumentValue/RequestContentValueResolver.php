<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Http\Request\ArgumentValue;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;
use function json_decode;

/**
 * @author Manuel Aguirre
 */
class RequestContentValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private ?SerializerInterface $serializer = null,
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if (!$this->serializer) {
            return false;
        }

        return 0 < count($argument->getAttributes(LoadFromRequestContent::class));
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() === 'array') {
            yield json_decode($request->getContent(), true);
            return;
        }

        yield $this->serializer->deserialize(
            $request->getContent(),
            $argument->getType(),
            $request->getContentType() ?? 'json',
        );
    }
}