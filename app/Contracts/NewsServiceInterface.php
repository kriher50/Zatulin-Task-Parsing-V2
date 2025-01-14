<?php

namespace App\Contracts;

interface NewsServiceInterface
{
    public function fetchLatestNews(): bool;
    public function fetchLentaLatestNews(): bool;
    public function fetchRiaLatestNews(): bool;
    public function clearCache(): void;
} 