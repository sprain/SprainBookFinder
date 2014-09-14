<?php

namespace Sprain\BookFinder\Providers;

use Sprain\BookFinder\Providers\BaseProvider\BaseProvider;
use Sprain\BookFinder\Providers\Interfaces\ProviderInterface;

set_include_path(__DIR__ . "/../../vendor/google/apiclient/src/");
require_once 'Google/Client.php';
require_once 'Google/Service/Books.php';

/**
 * GoogleProvider
 *
 * @author Manuel Reinhard <manu@sprain.ch>
 */
class GoogleProvider extends BaseProvider implements ProviderInterface
{
    protected $response = array();
    protected $service;

    /**
     * Constructor
     *
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $client = new \Google_Client();
        $client->setDeveloperKey($apiKey);

        $this->service = new \Google_Service_Books($client);
    }

    /**
     * @inheritdoc
     */
    public function searchByIsbn($isbn)
    {
        $this->response = $this->service->volumes->listVolumes('isbn:'.$isbn);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getResults()
    {
        if (!isset($this->response['data']['items'])) {
            return array();
        }

        $map = array(
            'title'          => array('volumeInfo', 'title'),
            'subtitle'       => array('volumeInfo', 'subtitle'),
            'authors'        => array('volumeInfo', 'authors'),
            'pages'          => array('volumeInfo', 'pageCount'),
            'language'       => array('volumeInfo', 'language'),
            'description'    => array('volumeInfo', 'description'),
        );

        $normalizedResults = array();
        foreach($this->response['data']['items'] as $item){

            // Find elements by map
            foreach($map as $mapKey => $mapItems) {
                $searchItem = $item;
                foreach($mapItems as $arrayElement) {
                    if (isset($searchItem[$arrayElement])) {
                        $searchItem = $searchItem[$arrayElement];
                    } else {
                        $normalizedResult[$mapKey] = null;
                        continue 2;
                    }
                }
                $normalizedResult[$mapKey] = $searchItem;
            }

            //Add image
            $normalizedResult['image'] = null;
            if (isset($item['volumeInfo']['imageLinks']['thumbnail'])) {
                $normalizedResult['image'] = $item['volumeInfo']['imageLinks']['thumbnail'];
            } elseif (isset($item['volumeInfo']['imageLinks']['smallThumbnail'])) {
                $normalizedResult['image'] = $item['volumeInfo']['imageLinks']['smallThumbnail'];
            }

            //Add ISBNs
            $normalizedResult['isbn10'] = null;
            $normalizedResult['isbn13'] = null;
            if (isset($item['volumeInfo']['industryIdentifiers'])) {
                foreach($item['volumeInfo']['industryIdentifiers'] as $industryIdentifier){
                    if ($industryIdentifier['type'] == 'ISBN_10') {
                        $normalizedResult['isbn10'] = $industryIdentifier['identifier'];
                    } elseif ($industryIdentifier['type'] == 'ISBN_13') {
                        $normalizedResult['isbn13'] = $industryIdentifier['identifier'];
                    }
                }
            }

            $normalizedResults[] = $normalizedResult;
        }

        return $normalizedResult;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultName()
    {
        return 'Google Books Api';
    }
}