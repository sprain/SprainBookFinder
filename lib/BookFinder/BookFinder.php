<?php

namespace Sprain\BookFinder;
use Sprain\BookFinder\Response\BookFinderResponse;

/**
 * BookFinder
 *
 * @author Manuel Reinhard <manu@sprain.ch>
 */
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
        $isbn = trim($isbn);
        $isbn = str_replace('-', '', $isbn);

        foreach($this->providers as $provider){
            $result = $provider['provider']->searchByIsbn($isbn)->getResult();

            if ($result) {

                if (isset($provider['name']) && null !== $provider['name']) {
                    $providerName = $provider['name'];
                } else {
                    $providerName = $provider['provider']->getDefaultName();
                }

                $response = new BookFinderResponse();
                $response->setProvider($provider['provider']);
                $response->setResult($result);
                $response->setProviderName($providerName);

                return $response;
            }
        }

        return false;
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