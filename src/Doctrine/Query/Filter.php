<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Doctrine\Query;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use function array_filter;
use function count;
use function is_array;

/**
 * @author Manuel Aguirre
 */
class Filter
{
    private static int $paramIndex = 0;

    public function __construct(
        private readonly QueryBuilder $query,
        private readonly mixed $value,
    ) {
    }

    public static function build(QueryBuilder $query, mixed $value): self
    {
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
            $conditions = $this->buildConditions($fields, function ($field, $expr) {
                return $expr->like($field, $this->getParam());
            });

            $this->addConditions($conditions, true);
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

    private function addConditions(array $conditions, bool $like = false): void
    {
        $this->query->andWhere($this->query->expr()->orX(...$conditions));
        $this->addParam($like);
    }

    private function hasValue(): bool
    {
        if (is_array($this->value)) {
            return count(array_filter($this->value)) > 0;
        }

        return $this->value && trim((string)$this->value) > 0;
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