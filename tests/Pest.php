<?php

declare(strict_types=1);
use Tests\TestCase;

pest()->extend(TestCase::class)->in(
    'Feature/Forms',
    'Feature/Honeypot',
    'Feature/Layout',
    'Feature/Modal',
    'Feature/Models',
    'Feature/Security',
);
