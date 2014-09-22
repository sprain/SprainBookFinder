<?php

namespace Sprain\BookFinder\Response;

use Sprain\BookFinder\Providers\Interfaces\ProviderInterface;

class BookFinderResponse
{
    protected $provider;
    protected $providerName;
    protected $result;

    public function setProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;
    }

    public function getProviderName()
    {
        return $this->providerName;
    }
}