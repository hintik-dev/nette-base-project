<?php declare(strict_types=1);
namespace App\Presentation\Modules\Api\Security;

use Apitte\Core\Decorator\IRequestDecorator;
use Apitte\Core\Exception\Runtime\EarlyReturnResponseException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Apitte\Core\Http\RequestAttributes;
use App\Domain\ApiUser\ApiUserNotFoundException;
use App\Domain\ApiUser\ExplorerApiUserRepository;

class AuthenticationDecorator implements IRequestDecorator
{
    public const string ATTR_API_USER = 'apiUser';

    public function __construct(
        private readonly ExplorerApiUserRepository $apiUserRepository,
    ) {
    }


    public function decorateRequest(ApiRequest $request, ApiResponse $response): ApiRequest
    {
        $endpoint = $request->getAttribute(RequestAttributes::ATTR_ENDPOINT);

        if ($endpoint !== null && $endpoint->hasTag('public')) {
            return $request;
        }

        $authHeader = $request->getHeader('Authorization')[0] ?? null;

        if ($authHeader === null || !str_starts_with($authHeader, 'Bearer ')) {
            throw new EarlyReturnResponseException(
                $response->withStatus(ApiResponse::S401_UNAUTHORIZED)
                    ->writeJsonBody(['status' => 'error', 'message' => 'Missing or invalid Authorization header.'])
            );
        }

        $token = substr($authHeader, 7);

        try {
            $apiUser = $this->apiUserRepository->getByToken($token);
        } catch (ApiUserNotFoundException) {
            throw new EarlyReturnResponseException(
                $response->withStatus(ApiResponse::S401_UNAUTHORIZED)
                    ->writeJsonBody(['status' => 'error', 'message' => 'Invalid or inactive token.'])
            );
        }

        return $request->withAttribute(self::ATTR_API_USER, $apiUser);
    }
}
