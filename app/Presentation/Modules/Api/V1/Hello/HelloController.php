<?php

namespace App\Presentation\Modules\Api\V1\Hello;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\Presentation\Modules\Api\V1\BaseV1Controller;

#[Path("/hello")]
class HelloController extends BaseV1Controller
{
    #[Path("/")]
    #[Method("GET")]
    public function index(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        $response = $response->writeJsonBody([
            'status' => 'success',
            'message' => 'Hello World'
        ]);

        return $response;
    }
}
