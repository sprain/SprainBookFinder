<?php

include 'vendor/autoload.php';

$providers = array(
    array(
        'provider' => new \Sprain\BookFinder\Providers\GoogleProvider('YOUR_GOOGLE_API_KEY'),
        'name' => 'Google', // optional
        'order' => 2 // optional, but recommended
    ),
    array(
        'provider' => new \Sprain\BookFinder\Providers\AmazonProvider('YOUR_AMAZON_ACCESS_KEY', 'YOUR_AMAZON_SECRET', 'YOUR_AMAZON_ASSOCIATE_TAG'),
        'name' => 'Amazon', // optional
        'order' => 1 // optional, but recommended
    ),
);

$bookFinder = new \Sprain\BookFinder\BookFinder($providers);

// Look for "The Art of the Start"
$response = $bookFinder->searchByIsbn('9781591840565');
var_dump(array(
    $response->getResult(),
    $response->getProviderName(),
    get_class($response->getProvider())
));

// Look for "Darm mit Charme"
$response = $bookFinder->searchByIsbn('3843707111');
var_dump(array(
    $response->getResult(),
    $response->getProviderName(),
    get_class($response->getProvider())
));

// Look for a failing result
$response = $bookFinder->searchByIsbn('foo');
var_dump($response);