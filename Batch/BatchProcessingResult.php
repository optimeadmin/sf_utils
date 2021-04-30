<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Batch;

/**
 * @author Manuel Aguirre
 */
class BatchProcessingResult implements \IteratorAggregate
{
    private $processedItems = [];
    private $unprocessedItems = [];

    public function addProcessed($item): void
    {
        $id = $this->getId($item);

        if (isset($this->unprocessedItems[$id])) {
            throw new \LogicException(
                "No se puede agregar un item como procesado si ya esta en los no procesados"
            );
        }

        if (!isset($this->processedItems[$id])) {
            $this->processedItems[] = $item;
        }
    }

    public function addUnprocessed($item, $error): void
    {
        $id = $this->getId($item);

        if (isset($this->processedItems[$id])) {
            throw new \LogicException(
                "No se puede agregar un item como no procesado si ya esta en los procesados"
            );
        }

        if (!isset($this->unprocessedItems[$id])) {
            $this->unprocessedItems[$id] = ['value' => $item, 'error' => $error];
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator(array_merge($this->getProcessed(), $this->getUnprocessed()));
    }

    public function count()
    {
        return count($this->getProcessed()) + count($this->getUnprocessed());
    }

    public function getProcessed(): array
    {
        return $this->processedItems;
    }

    public function getUnprocessed(): array
    {
        return $this->unprocessedItems;
    }

    public function hasErrors(): bool
    {
        return 0 !== count($this->getUnprocessed());
    }

    private function getId($item): string
    {
        return is_object($item) ? spl_object_hash($item) : (string)$item;
    }
}