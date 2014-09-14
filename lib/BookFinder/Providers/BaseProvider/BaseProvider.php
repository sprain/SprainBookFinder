<?php

namespace Sprain\BookFinder\Providers\BaseProvider;

abstract class BaseProvider
{
    protected $name;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        if (null == $this->name) {

            return $this->getDefaultName();
        }

        return $this->name;
    }
}