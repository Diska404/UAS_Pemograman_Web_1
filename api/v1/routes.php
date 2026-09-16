<?php

$router->add('GET', '/api/v1/csrf', fn () => $api->csrf());
$router->add('POST', '/api/v1/token', fn () => $api->token());
$router->add('DELETE', '/api/v1/token', fn () => $api->revoke());
$router->add('GET', '/api/v1/barang', fn () => $api->items());
$router->add('GET', '/api/v1/barang/{id}', fn ($id) => $api->items((int)$id));
$router->add('POST', '/api/v1/barang', fn () => $api->save());
$router->add('PUT', '/api/v1/barang/{id}', fn ($id) => $api->save((int)$id));
$router->add('DELETE', '/api/v1/barang/{id}', fn ($id) => $api->delete((int)$id));
$router->add('GET', '/api/v1/stok', fn () => $api->items());
$router->add('GET', '/api/v1/dashboard', fn () => $api->dashboard());
