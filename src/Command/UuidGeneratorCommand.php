<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;
use function filter_var;
use const FILTER_VALIDATE_INT;

/**
 * @author Manuel Aguirre
 */
class UuidGeneratorCommand extends Command
{
    protected static $defaultName = 'uuid:generate';

    protected function configure()
    {
        $this->addArgument('quantity', InputArgument::OPTIONAL, 'Cantidad de uuids a generar', 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $quantity = $input->getArgument('quantity');

        if (false === filter_var($quantity, FILTER_VALIDATE_INT)) {
            $io->error('"quantity" debe ser un número entero');

            return self::FAILURE;
        }


        if (1 == $quantity) {
            $io->block(Uuid::v4());

            $io->success('Item generado con exito.!');
        } else {
            for ($x = 0; $x < $quantity; $x++) {
                $io->text(Uuid::v4());
            }

            $io->success('Items generados con exito.!');
        }


        return self::SUCCESS;
    }
}