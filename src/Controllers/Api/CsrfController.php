<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\Response as ResponseHelper;
use App\Middleware\CsrfMiddleware;

class CsrfController
{
    public function getToken(Request $request, Response $response, array $params): void
    {
        $token = CsrfMiddleware::generateToken();
        
        ResponseHelper::success([
            'csrf_token' => $token,
        ]);
    }
}
