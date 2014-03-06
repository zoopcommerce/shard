unit tests
==========

Running `phpunit` will run all tests twice. Once with an array metadata cache, and a second time with a juggernaut metadata cache.

Alternatively, use:

`phpunit --bootstrap bootstrapArrayCacheOnly.php`
`phpunit --bootstrap bootstrapJuggernautCacheOnly.php`
