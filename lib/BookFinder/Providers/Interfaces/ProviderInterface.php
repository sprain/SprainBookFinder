<?php

namespace Sprain\BookFinder\Providers\Interfaces;

interface ProviderInterface
{
    /**
     * Search for books by any isbn number
     *
     * @param  string $isbn
     * @return ProviderInterface
     */
    public function searchByIsbn($isbn);

    /**
     * Returns an array of found books.
     *
     * Must contain at least the following fields:
     *
     * array (
     *   'title'       => 'The Art of the Start',
     *   'subtitle'    => 'The Time-tested, Battle-hardened Guide for Anyone Starting Anything',
     *   'authors'     => array (
     *       0 => 'Guy Kawasaki',
     *   ),
     *   'pages'       => 226,
     *   'language'    => 'en',
     *   'description' => 'A new product, a new service, a new company, a new division, a new anything - where there\'s a will, Kawasaki shows the way with his essential steps to launching one\'s dreams.',
     *   'image'       => 'http://bks0.books.google.ch/books?id=-gXlwJnnNoEC&printsec=frontcover&img=1&zoom=1&source=gbs_api',
     *   'isbn10'      => '1591840562',
     *   'isbn13'      => '9781591840565',
     * )
     *
     *
     * @return array
     */
    public function getResult();

    /**
     * Get default name of provider
     *
     * @return string
     */
    public function getDefaultName();
}