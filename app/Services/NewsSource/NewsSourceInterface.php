<?php

namespace App\Services\NewsSource;

use GuzzleHttp\Client;

interface NewsSourceInterface
{
    public function fetch(Client $client): bool;
} 