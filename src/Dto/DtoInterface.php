<?php
/**
 * @author Manuel Aguirre
 */

declare(strict_types=1);

namespace Optime\Util\Dto;

/**
 * @author Manuel Aguirre
 */
interface DtoInterface
{
    public static function supportsSource(?object $data): bool;

    public static function createFromSource(?object $data, array $dependencies = []): static;

    public function writeSource(?object &$data, array $dependencies = []): void;
}