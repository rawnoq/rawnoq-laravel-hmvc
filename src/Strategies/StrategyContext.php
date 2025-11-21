<?php

namespace Rawnoq\HMVC\Strategies;

abstract class StrategyContext
{
    protected $strategy;

    /**
     * Create a new strategy context instance.
     *
     * @param  mixed  $strategy
     */
    public function __construct($strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * Set the strategy.
     *
     * @param  mixed  $strategy
     * @return $this
     */
    public function setStrategy($strategy): self
    {
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * Get the current strategy.
     *
     * @return mixed
     */
    public function getStrategy()
    {
        return $this->strategy;
    }
}

