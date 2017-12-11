<?php
namespace SnowIO\AkeneoMagento2\Test;

use SnowIO\Magento2DataModel\Command\Command;

final class CommandSet
{
    public static function create(): self
    {
        return self::of();
    }

    public static function of(Command ...$commands): self
    {
        $set = new self;
        $set->commands = $commands;
        return $set;
    }

    public function equals($object): bool
    {
        if (!$object instanceof self) {
            return false;
        }

        if (\count($this->commands) !== \count($object->commands)) {
            return false;
        }

        $otherCommands = $object->commands;
        foreach ($this->commands as $command) {
            foreach ($otherCommands as $key => $otherCommand) {
                if ($otherCommand->equals($command)) {
                    unset($otherCommands[$key]);
                    continue 2;
                }
            }
            return false;
        }

        return true;
    }

    /** @var Command[] */
    private $commands;

    private function __construct()
    {

    }
}
