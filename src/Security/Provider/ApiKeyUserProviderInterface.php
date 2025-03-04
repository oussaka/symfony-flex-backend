<?php
declare(strict_types = 1);
/**
 * /src/Security/Provider/ApiKeyUserProviderInterface.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

namespace App\Security\Provider;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use App\Security\RolesService;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Interface ApiKeyUserProviderInterface
 *
 * @package App\Security\Provider
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
interface ApiKeyUserProviderInterface extends UserProviderInterface
{
    /**
     * ApiKeyUserProvider constructor.
     *
     * @param ApiKeyRepository $apiKeyRepository
     * @param RolesService     $rolesService
     */
    public function __construct(ApiKeyRepository $apiKeyRepository, RolesService $rolesService);

    /**
     * Method to fetch ApiKey entity for specified token.
     *
     * @param string $token
     *
     * @return ApiKey|null
     */
    public function getApiKeyForToken(string $token): ?ApiKey;
}
