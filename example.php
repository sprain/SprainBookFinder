<?php

include 'vendor/autoload.php';

use Symfony\Component\Intl\Intl;

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
$successfulProvider = $bookFinder->searchByIsbn('9781591840565');
var_dump(array(
    $successfulProvider->getResults(),
    $successfulProvider->getName()
));

// Look for "Darm mit Charme"
$successfulProvider = $bookFinder->searchByIsbn('3843707111');
var_dump(array(
    $successfulProvider->getResults(),
    $successfulProvider->getName()
));