# SprainBookFinder

A php library to find books via different apis

## Functionalities
* Find books by ISBN over different providers
* Included providers:
    * Google Books API
    * Amazon Product Advertising API
* Add custom providers to find books

## Installation

Add TicketparkFileBundle in your composer.json:

```js
{
    "require": {
        "sprain/bookfinder": "~0.1"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update sprain/bookfinder
```

## Usage
See [example.php](example.php) for usage.

## Add custom provider
Adding your own provider is simple:

### Create provider

```
<?php

namespace Acme\Your\Namespace;

use Sprain\BookFinder\Providers\BaseProvider\BaseProvider;
use Sprain\BookFinder\Providers\Interfaces\ProviderInterface;

class MyCustomProvider extends BaseProvider implements ProviderInterface
{
    public function searchByIsbn($isbn)
    {
        // perform search, e.q. in a local database
    }

    public function getResults()
    {
        // return array of normalized results
    }

    public function getDefaultName()
    {
        return 'My Own Awesome Book Provider';
    }
}
```

### Add provider to BookFinder
Add your provider to the providers array as seen in [example.php](example.php).


## License


This bundle is under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE
