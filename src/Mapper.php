<?php
namespace SnowIO\AkeneoMagento2;

use SnowIO\AkeneoDataModel\SetTrait;

abstract class Mapper
{
    /**
     * @return static
     */
    public function withInputFilter(callable $predicate): self
    {
        $result = clone $this;
        $result->inputFilters[] = $predicate;
        return $result;
    }

    /**
     * @return static
     */
    public function withOutputFilter(callable $predicate): self
    {
        $result = clone $this;
        $result->outputFilters[] = $predicate;
        return $result;
    }

    /**
     * @param mixed $input
     */
    protected function inputIsIgnored($input): bool
    {
        foreach ($this->inputFilters as $filter) {
            if (!$input->filter($filter)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param mixed $output
     * @return mixed
     */
    protected function filterOutput($output)
    {
        foreach ($this->outputFilters as $filter) {
            if (!$output->filter($filter)) {
                return null;
            }
        }
        return $output;
    }

    /**
     * @return mixed
     */
    protected function filterInputSet($input)
    {
        foreach ($this->inputFilters as $filter) {
            $input = $input->filter($filter);
        }
        return $input;
    }

    /**
     * @return mixed
     */
    protected function filterOutputSet($output)
    {
        foreach ($this->outputFilters as $filter) {
            $output = $output->filter($filter);
        }
        return $output;
    }

    private $inputFilters = [];
    private $outputFilters = [];
}
