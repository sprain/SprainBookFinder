<?php

namespace Sprain\BookFinder;

class BookFinder
{
    /**
     * Constructor
     *
     * @param array $providers
     */
    public function __construct($providers = array())
    {
        $this->providers = $this->sortProviders($providers);
    }

    /**
     * Search by isbn
     *
     * @param  string $isbn
     * @return array
     */
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

    /**
     * Sort providers by order field
     *
     * @param array $providers
     * @return mixed
     */
    protected function sortProviders($providers)
    {
        $sortFunc = function ($a, $b)
        {
            if (!isset($a['order']) && isset($b['order'])) {
                return 1;
            }

            if (!isset($b['order']) && isset($a['order'])) {
                return -1;
            }

            if (!isset($a['order']) && !isset($b['order'])) {
                return 0;
            }

            if ($a['order'] == $b['order']) {
                return 0;
            }
            return ($a['order'] < $b['order']) ? -1 : 1;
        };

        usort($providers, $sortFunc);

        return $providers;
    }
}