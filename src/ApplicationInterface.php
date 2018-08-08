<?php

namespace Blossom\BackendDeveloperTest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ApplicationInterface
{
    public function handleRequest(Request $request): Response;
}
