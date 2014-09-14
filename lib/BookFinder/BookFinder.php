<?php

namespace Sprain\BookFinder;

class BookFinder
{
    public function __construct($providers = array())
    {
        $this->providers = $providers;
    }

    public function searchByIsbn($isbn)
    {
        foreach($this->providers as $provider){
            $results = $provider['provider']->searchByIsbn($isbn)->getResults();
            if (count($results) > 0) {

                if (isset($provider['name']) && null !== $provider['name']) {
                    $provider['provider']->setName($provider['name']);
                }

                break;
            }
        }

        return $provider['provider'];
    }
}