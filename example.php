<?php

include 'vendor/autoload.php';

$providers = array(
    array(
        'object' => new \Sprain\BookFinder\Providers\GoogleProvider('YOUR_GOOGLE_API_KEY'),
    ),
    array(
        'object' => new \Sprain\BookFinder\Providers\AmazonProvider('YOUR_AMAZON_ACCESS_KEY', 'YOUR_AMAZON_SECRET', 'YOUR_AMAZON_ASSOCIATE_TAG'),
    )
);

$bookFinder = new \Sprain\BookFinder\BookFinder($providers);

var_dump($bookFinder->searchByIsbn('9781591840565')->getResults());