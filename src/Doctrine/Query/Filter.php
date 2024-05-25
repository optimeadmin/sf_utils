<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Doctrine\Query;

use ArrayAccess;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use function array_filter;
use function array_map;
use function count;
use function gettype;
use function is_array;
use function strlen;
use function trim;

/**
 * @author Manuel Aguirre
 */
class Filter
{
    private static int $paramIndex = 0;
    private ?bool $hasValue = null;

    public function __construct(
        private readonly QueryBuilder $query,
        private mixed $value,
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
            $conditions = $this->buildConditions($fields, function ($field, Expr $expr) {
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
            if (is_array($this->value)) {
                $this->arrayLike($fields);
            } else {
                $conditions = $this->buildConditions($fields, function ($field, $expr) {
                    return $expr->like($field, $this->getParam());
                });

                $this->addConditions($conditions, true);
            }

        }

        return $this;
    }

    public function join(
        string $join,
        string $alias,
        $conditionType = null,
        $condition = null,
        $indexBy = null
    ): self {
        if ($this->hasValue()) {
            $this->query->join($join, $alias, $conditionType, $condition, $indexBy);
        }

        return $this;
    }

    public function leftJoin(
        string $join,
        string $alias,
        $conditionType = null,
        $condition = null,
        $indexBy = null
    ): self {
        if ($this->hasValue()) {
            $this->query->leftJoin($join, $alias, $conditionType, $condition, $indexBy);
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

    public function trim(): self
    {
        if ($this->hasValue()) {
            $this->hasValue = null;

            if (is_array($this->value)) {
                $this->value = array_map(trim(...), $this->value);
            } else {
                $this->value = trim($this->value);
            }
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

    private function arrayLike(string|array $fields): void
    {
        $conditions = [];

        foreach ($this->value as $value) {
            $conditions = [
                ...$conditions,
                ...$this->buildConditions($fields, function ($field, $expr) {
                    return $expr->like($field, $this->getParam());
                })
            ];

            $this->addValueParam('%' . $value . '%');
        }

        $this->query->andWhere($this->query->expr()->orX(...$conditions));
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

        $this->addValueParam($value);
    }

    private function addValueParam($value): void
    {
        $this->query->setParameter(trim($this->getParam(), ':'), $value);
        self::$paramIndex++;
    }
}