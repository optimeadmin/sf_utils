<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Dbal\Query;

use ArrayAccess;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use InvalidArgumentException;
use function array_filter;
use function count;
use function gettype;
use function is_array;
use function strlen;
use function trim;

/**
 * @author Manuel Aguirre
 */
class DbalFilter
{
    private static int $paramIndex = 0;
    private ?bool $hasValue = null;

    public function __construct(
        private readonly QueryBuilder $query,
        private readonly mixed $value,
    ) {
    }

    public static function build(QueryBuilder $query, mixed $value, string $key = null): self
    {
        if ($key !== null) {
            if ((!is_array($value)) && (!$value instanceof ArrayAccess)) {
                throw new InvalidArgumentException("\$value debe ser un array o implementar ArrayAccess. Pero llegó " . gettype($value));
            }

            $value = $value[$key] ?? null;
        }

        return new self($query, $value);
    }

    public function where(string|array $fields): self
    {
        if ($this->hasValue()) {
            $conditions = $this->buildConditions($fields, function ($field, ExpressionBuilder $expr) {
                if (is_array($this->value)) {
                    return $expr->in($field, $this->getParam());
                }

                return $expr->eq($field, $this->getParam());
            });

            $this->addConditions($conditions);
        }

        return $this;
    }

    public function staticWhere(string|array $conditions): self
    {
        if ($this->hasValue()) {
            $this->query->andWhere($this->query->expr()->orX(...(array)$conditions));
        }

        return $this;
    }

    public function like(string|array $fields): self
    {
        if ($this->hasValue()) {
            $conditions = $this->buildConditions($fields, function ($field, ExpressionBuilder $expr) {
                return $expr->like($field, $this->getParam());
            });

            $this->addConditions($conditions, true);
        }

        return $this;
    }

    public function join($fromAlias, $join, $alias, $condition = null): self
    {
        if ($this->hasValue()) {
            $this->query->join($fromAlias, $join, $alias, $condition);
        }

        return $this;
    }

    public function leftJoin($fromAlias, $join, $alias, $condition = null): self {
        if ($this->hasValue()) {
            $this->query->leftJoin($fromAlias, $join, $alias, $condition);
        }

        return $this;
    }

    public function setParameter($key, $value, $type = null): self
    {
        if ($this->hasValue()) {
            $this->query->setParameter($key, $value, $type);
        }

        return $this;
    }

    private function addConditions(array $conditions, bool $like = false): void
    {
        $this->query->andWhere($this->query->expr()->orX(...$conditions));
        $this->addParam($like);
    }

    private function hasValue(): bool
    {
        if (null !== $this->hasValue) {
            return $this->hasValue;
        }

        if (is_array($this->value)) {
            return $this->hasValue = count(array_filter($this->value)) > 0;
        }

        return $this->hasValue = null !== $this->value && strlen(trim((string)$this->value)) > 0;
    }

    public function buildConditions(string|array $conditions, callable $func): array
    {
        $expressions = [];
        $expr = $this->query->expr();

        foreach ((array)$conditions as $field) {
            $expressions[] = $func($field, $expr);
        }

        return $expressions;
    }

    private function getParam(): string
    {
        return ':auto_filter_' . self::$paramIndex;
    }

    private function addParam(bool $like = false): void
    {
        if ($like) {
            $value = '%' . $this->value . '%';
        } else {
            $value = $this->value;
        }
        $this->query->setParameter(trim($this->getParam(), ':'), $value);
        self::$paramIndex++;
    }
}