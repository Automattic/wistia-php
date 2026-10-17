<?php

// Class definitions alone do not register bindings. Laravel loads this list to
// run our provider alongside the consumer's own AppServiceProvider.
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\WistiaServiceProvider::class,
];
