<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Http\EventListener;

use Optime\Util\Http\Request\AjaxChecker;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Contracts\Service\ResetInterface;
use function count;
use function is_array;
use function is_object;
use function is_string;

/**
 * @author Manuel Aguirre
 */
class AbstractControllerAttributeListener implements ResetInterface
{
    private static array $reflections = [];
    private static array $attributes = [];

    private null|array|object $attribute = null;

    public function __construct(
        private readonly ?AjaxChecker $ajaxChecker = null,
    ) {
    }

    protected function apply(array|object $attribute): void
    {
        $this->attribute = is_object($attribute) ? [$attribute] : $attribute;
    }

    public function hasAttribute(): bool
    {
        return null !== $this->attribute;
    }

    public function getAttributes(): array
    {
        return (array)$this->attribute;
    }

    public function getFirstAttribute(): ?object
    {
        return $this->attribute[0] ?? null;
    }

    /**
     * @param ControllerEvent $event
     * @param string $attributeClass
     * @param bool $checkAjax
     * @return array
     */
    protected function getAttributesIfApply(
        ControllerEvent $event,
        string $attributeClass,
        bool $checkAjax = true,
    ): array {
        $controller = $event->getController();
        $request = $event->getRequest();

        if (!$event->isMainRequest()) {
            return [];
        }

        if ($checkAjax && $this->ajaxChecker && !$this->ajaxChecker->isAjax($request)) {
            return [];
        }

        if (!is_array($controller) || 2 !== count($controller)) {
            return [];
        }

        if (!is_object($controller[0] ?? null) || !is_string($controller[1] ?? null)) {
            return [];
        }

        try {
            $reflection = $this->getReflectionMethod($controller);
        } catch (ReflectionException) {
            return [];
        }

        return $this->doGetAttributes($reflection, $attributeClass);
    }

    private function controllerToString(array $controller): string
    {
        return $controller[0]::class.':'.$controller[1];
    }

    /**
     * @throws ReflectionException
     */
    private function getReflectionMethod(array $controller): ReflectionMethod
    {
        return self::$reflections[$this->controllerToString($controller)]
            ??= new ReflectionMethod($controller[0], $controller[1]);
    }

    private function doGetAttributes(ReflectionMethod $reflection, string $attributeClass): array
    {
        return self::$attributes[$reflection->getName()][$attributeClass] ??= array_values(
            array_unique([
                ...$reflection->getAttributes($attributeClass),
                ...$reflection->getDeclaringClass()->getAttributes($attributeClass),
            ])
        );
    }

    public function reset(): void
    {
        $this->attribute = null;
        self::$reflections = [];
        self::$attributes = [];
    }
}