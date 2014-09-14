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
            $results = $provider['object']->searchByIsbn($isbn)->getResults();
            if (count($results) > 0) {
                break;
            }
        }

        return $provider['object'];
    }
}