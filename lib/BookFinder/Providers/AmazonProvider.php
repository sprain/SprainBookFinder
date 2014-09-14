<?php

namespace Sprain\BookFinder\Providers;

use Buzz\Browser;
use Buzz\Client\Curl;
use Sprain\BookFinder\Providers\BaseProvider\BaseProvider;
use Sprain\BookFinder\Providers\Interfaces\ProviderInterface;
use Symfony\Component\Intl\Intl;

class AmazonProvider extends BaseProvider implements ProviderInterface
{
    protected $response = array();
    protected $accessKey;
    protected $secret;
    protected $associateTag;

    protected $store;
    protected $stores = array(
        'us' => array(
            'language' => 'en',
            'url' => 'http://webservices.amazon.com/onca/xml',
        ),
        'de' => array(
            'language' => 'de',
            'url' => 'http://webservices.amazon.de/onca/xml',
        ),
    );

    /**
     * Constructor
     *
     * @param string $accessKey
     * @param string $secret
     * @param string $associateTag
     */
    public function __construct($accessKey, $secret, $associateTag, $storeKey = 'us')
    {
        $this->accessKey    = $accessKey;
        $this->secret       = $secret;
        $this->associateTag = $associateTag;

        $this->setStore($storeKey);
    }

    /**
     * @inheritdoc
     */
    public function searchByIsbn($isbn)
    {
        $params = array(
            'Service' => 'AWSECommerceService',
            'Operation' => 'ItemLookup',
            'ResponseGroup' => 'Large',
            'SearchIndex' => 'All',
            'IdType' => 'EAN',
            'ItemId' => $isbn
        );

        $this->response = $this->executeRequest($params);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getResults()
    {
        if (!isset($this->response['Items']['Item'])) {
            return array();
        }

        $map = array(
            'title'          => array('ItemAttributes', 'Title'),
            'isbn10'         => array('ItemAttributes', 'ISBN'),
            'isbn13'         => array('ItemAttributes', 'EAN'),
            'subtitle'       => array('ItemAttributes', 'Subtitle'),
            'pages'          => array('ItemAttributes', 'NumberOfPages'),
            'description'    => array('ItemAttributes', 'Description'),
        );

        $normalizedResults = array();
        if (is_array($this->response['Items']['Item'])) {
            $item = $this->response['Items']['Item'][0];
        } else {
            $item = $this->response['Items']['Item'];
        }

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
        $normalizedResults[] = $normalizedResult;

        //Add image
        $normalizedResult['image'] = null;
        if (isset($item['LargeImage'])) {
            $normalizedResult['image'] = $item['LargeImage']['URL'];
        } elseif (isset($item['MediumImage'])) {
            $normalizedResult['image'] = $item['MediumImage']['URL'];
        } elseif (isset($item['SmallImage'])) {
            $normalizedResult['image'] = $item['SmallImage']['URL'];
        }

        //Add authors
        $normalizedResult['authors'] = null;
        if (isset($item['ItemAttributes']['Author'])) {
            if (is_array($item['ItemAttributes']['Author'])) {
                $normalizedResult['authors'] = $item['ItemAttributes']['Author'];
            } else {
                $normalizedResult['authors'] = array($item['ItemAttributes']['Author']);
            }
        }

        //Add language
        $normalizedResult['language'] = null;
        if (isset($item['ItemAttributes']['Languages']['Language'][0])) {
            $normalizedResult['language'] = $this->getLanguageCode($item['ItemAttributes']['Languages']['Language'][0]['Name']);
        }

        return $normalizedResult;
    }

    /**
     * Set the desired store
     *
     * @param string $storeKey
     */
    public function setStore($storeKey)
    {
        if (!array_key_exists($storeKey, $this->stores)) {

            $listOfValidKeys = '"'. implode('", "', array_keys($this->stores)) .'"';

            throw new \Exception(sprintf('There is no store with key "%s" available. Valid store keys are %s.', $storeKey, $listOfValidKeys));
        }

        $this->store = $this->stores[$storeKey];
    }

    /**
     * Get the current store
     *
     * @return array
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Get base api url
     *
     * @return mixed
     */
    protected function getStoreUrl()
    {
        return $this->getStore()['url'];
    }

    /**
     * Get base api url
     *
     * @return mixed
     */
    protected function getStoreLanguage()
    {
        return $this->getStore()['language'];
    }

    /**
     * Execute request
     *
     * @param $params
     * @return array
     */
    protected function executeRequest($params)
    {
        $url = $this->getRequestUrl($params);

        $browser = new Browser(new Curl());
        $response = $browser->get($url);

        if (200 == $response->getStatusCode()) {
            $xml = simplexml_load_string($response->getContent());
            $json = json_encode($xml);

            return json_decode($json, true);
        }

        return array();
    }

    /**
     * Create full request url
     *
     * @param $params
     * @return string
     */
    protected function getRequestUrl($params)
    {
        $baseParams = array(
            'AWSAccessKeyId' => $this->accessKey,
            'AssociateTag' => $this->associateTag
        );

        $params = array_merge($params, $baseParams);

        return $this->signAmazonUrl($this->getStoreUrl().'?'.http_build_query($params), $this->secret);
    }

    /**
     * Add signature to url
     * 
     * @link http://www.brandonchecketts.com/archives/php-code-to-sign-any-amazon-api-requests
     * @param string $url
     * @param string $secret
     * @return string
     */
    protected function signAmazonUrl($url, $secret)
    {
        $urlparts = parse_url($url);

        // Build $params with each name/value pair
        foreach (explode('&', $urlparts['query']) as $part) {
            if (strpos($part, '=')) {
                list($name, $value) = explode('=', $part, 2);
            } else {
                $name = $part;
                $value = '';
            }
            $params[$name] = $value;
        }

        // Include a timestamp if none was provided
        if (empty($params['Timestamp'])) {
            $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
        }

        // Sort the array by key
        ksort($params);

        // Build the canonical query string
        $canonical       = '';
        foreach ($params as $key => $val) {
            $canonical  .= "$key=".rawurlencode(utf8_encode($val))."&";
        }

        // Remove the trailing ampersand
        $canonical = preg_replace("/&$/", '', $canonical);

        // Some common replacements and ones that Amazon specifically mentions
        $canonical = str_replace(array(' ', '+', ',', ';'), array('%20', '%20', urlencode(','), urlencode(':')), $canonical);

        // Build the sign
        $stringToSign = "GET\n{$urlparts['host']}\n{$urlparts['path']}\n$canonical";

        // Calculate our actual signature and base64 encode it
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $secret, true));

        // Finally re-build the URL with the proper string and include the Signature
        $url = "{$urlparts['scheme']}://{$urlparts['host']}{$urlparts['path']}?$canonical&Signature=".rawurlencode($signature);

        return $url;
    }

    protected function getLanguageCode($languageString)
    {
        // Temporarily save current locale
        $default = \Locale::getDefault();

        // Set locale according to store, retrieve language code
        \Locale::setDefault($this->getStoreLanguage());
        $languages = Intl::getLanguageBundle()->getLanguageNames();

        // Set locale back to default
        \Locale::setDefault($default);

        return array_search($languageString, $languages);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultName()
    {
        return 'Amazon Product Advertising API';
    }
}