<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

/**
 * @author Manuel Aguirre
 */
class StringUuidType extends Type
{

    /**
     * @param array $column
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        $column['fixed'] = true;
        $column['length'] = 36;
        $column['unique'] ??= true;

        return $platform->getVarcharTypeDeclarationSQL($column);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'string_uuid';
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return AbstractUid|null
     * @throws ConversionException
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?AbstractUid
    {
        if ($value instanceof AbstractUid || null === $value) {
            return $value;
        }

        if (!\is_string($value)) {
            throw ConversionException::conversionFailedInvalidType(
                $value,
                $this->getName(),
                ['null', 'string', AbstractUid::class]
            );
        }

        try {
            return Uuid::fromString($value);
        } catch (\InvalidArgumentException $e) {
            throw ConversionException::conversionFailed($value, $this->getName(), $e);
        }
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return string|null
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof AbstractUid) {
            return $value->toRfc4122();
        }

        if (null === $value || '' === $value) {
            return null;
        }

        if (!\is_string($value)) {
            throw ConversionException::conversionFailedInvalidType(
                $value,
                $this->getName(),
                ['null', 'string', AbstractUid::class]
            );
        }

        try {
            return Uuid::fromString($value)->toRfc4122();
        } catch (\InvalidArgumentException $e) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }
    }

    /**
     * @param AbstractPlatform $platform
     * @return bool
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}