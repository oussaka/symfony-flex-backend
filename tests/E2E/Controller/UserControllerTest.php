<?php
declare(strict_types = 1);
/**
 * /tests/E2E/Controller/UserControllerTest.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

namespace App\Tests\E2E\Controller;

use App\Entity\User;
use App\Resource\UserGroupResource;
use App\Resource\UserResource;
use App\Security\RolesService;
use App\Utils\JSON;
use App\Utils\Tests\WebTestCase;
use Generator;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use function array_filter;
use function array_map;
use function array_values;
use function count;
use function sprintf;

/**
 * Class UserControllerTest
 *
 * @package App\Tests\E2E\Controller
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class UserControllerTest extends WebTestCase
{
    private $baseUrl = '/user';

    /**
     * @throws Throwable
     */
    public function testThatGetBaseRouteReturn401(): void
    {
        $client = $this->getTestClient();
        $client->request('GET', $this->baseUrl);

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(401, $response->getStatusCode(), (string)$response);

        static::assertJsonStringEqualsJsonString(
            '{"message":"JWT Token not found","code":401}',
            $response->getContent(),
            "Response:\n" . $response
        );

        unset($response, $client);
    }

    /**
     * @dataProvider dataProviderValidUsers
     *
     * @param string $username
     * @param string $password
     *
     * @throws Throwable
     */
    public function testThatCountActionReturnsExpected(string $username, string $password): void
    {
        $client = $this->getTestClient($username, $password);
        $client->request('GET', $this->baseUrl . '/count');

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(200, $response->getStatusCode(), "Response:\n" . $response);
        static::assertJsonStringEqualsJsonString('{"count":6}', $response->getContent(), "Response:\n" . $response);

        unset($response, $client);
    }

    /**
     * @dataProvider dataProviderValidApiKeyUsers
     *
     * @param string $role
     */
    public function testThatCountActionReturnsExpectedForApiKeyUser(string $role): void
    {
        $client = $this->getApiKeyClient($role);
        $client->request('GET', $this->baseUrl . '/count');

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(200, $response->getStatusCode(), "Response:\n" . $response);
        static::assertJsonStringEqualsJsonString('{"count":6}', $response->getContent(), "Response:\n" . $response);

        unset($response, $client);
    }

    /**
     * @dataProvider dataProviderInvalidUsers
     *
     * @param string $username
     * @param string $password
     *
     * @throws Throwable
     */
    public function testThatCountActionReturns403ForInvalidUser(string $username, string $password): void
    {
        $client = $this->getTestClient($username, $password);
        $client->request('GET', $this->baseUrl . '/count');

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(403, $response->getStatusCode(), "Response:\n" . $response);
        static::assertJsonStringEqualsJsonString(
            '{"message":"Access denied.","code":0,"status":403}',
            $response->getContent(),
            "Response:\n" . $response
        );

        unset($response, $client);
    }

    /**
     * @dataProvider dataProviderInvalidApiKeyUsers
     *
     * @param string $role
     */
    public function testThatCountActionReturns403ForInvalidApiKeyUser(string $role): void
    {
        $client = $this->getApiKeyClient($role);
        $client->request('GET', $this->baseUrl . '/count');

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(403, $response->getStatusCode(), "Response:\n" . $response);
        static::assertJsonStringEqualsJsonString(
            '{"message":"Access denied.","code":0,"status":403}',
            $response->getContent(),
            "Response:\n" . $response
        );

        unset($response, $client);
    }

    /**
     * @dataProvider dataProviderValidUsers
     *
     * @param string $username
     * @param string $password
     *
     * @throws Throwable
     */
    public function testThatFindActionReturnsExpected(string $username, string $password): void
    {
        $client = $this->getTestClient($username, $password);
        $client->request('GET', $this->baseUrl);

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(200, $response->getStatusCode(), "Response:\n" . $response);
        static::assertCount(6, JSON::decode($response->getContent()), "Response:\n" . $response);

        unset($response, $client);
    }

    /**
     * @dataProvider dataProviderInvalidUsers
     *
     * @param string $username
     * @param string $password
     *
     * @throws Throwable
     */
    public function testThatFindActionReturns403ForInvalidUser(string $username, string $password): void
    {
        $client = $this->getTestClient($username, $password);
        $client->request('GET', $this->baseUrl);

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(403, $response->getStatusCode(), $response->getContent() . "\nResponse:\n" . $response);
        static::assertJsonStringEqualsJsonString(
            '{"message":"Access denied.","code":0,"status":403}',
            $response->getContent(),
            "Response:\n" . $response
        );

        unset($response, $client);
    }

    /**
     * @dataProvider dataProviderValidUsers
     *
     * @param string $username
     * @param string $password
     *
     * @throws Throwable
     */
    public function testThatIdsActionReturnExpected(string $username, string $password): void
    {
        $client = $this->getTestClient($username, $password);
        $client->request('GET', $this->baseUrl . '/ids');

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(200, $response->getStatusCode(), "Response:\n" . $response);
        static::assertCount(6, JSON::decode($response->getContent()), "Response:\n" . $response);

        unset($response, $client);
    }

    /**
     * @dataProvider dataProviderInvalidUsers
     *
     * @param string $username
     * @param string $password
     *
     * @throws Throwable
     */
    public function testThatIdsActionReturns403ForInvalidUser(string $username, string $password): void
    {
        $client = $this->getTestClient($username, $password);
        $client->request('GET', $this->baseUrl . '/ids');

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(403, $response->getStatusCode(), "Response:\n" . $response);
        static::assertJsonStringEqualsJsonString(
            '{"message":"Access denied.","code":0,"status":403}',
            $response->getContent(),
            "Response:\n" . $response
        );

        unset($response, $client);
    }

    /**
     * @return string
     *
     * @throws Throwable
     */
    public function testThatCreateActionWorksLikeExpected(): string
    {
        $data = [
            'username'  => 'test-user',
            'firstName' => 'test',
            'lastName'  => 'user',
            'email'     => 'test-user@test.com',
            'password'  => 'some password',
        ];

        $client = $this->getTestClient('john-root', 'password-root');
        $client->request('POST', $this->baseUrl, [], [], [], JSON::encode($data));

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(201, $response->getStatusCode(), $response->getContent() . "\nResponse:\n" . $response);

        $responseData = $response->getContent();

        $data['id'] = JSON::decode($responseData)->id;

        unset($data['password']);

        static::assertJsonStringEqualsJsonString(JSON::encode($data), $responseData);

        unset($responseData, $response, $client);

        return $data['id'];
    }

    /**
     * @dataProvider dataProviderInvalidUsersCreate
     *
     * @param string $username
     * @param string $password
     *
     * @throws Throwable
     */
    public function testThatCreateActionReturns403ForInvalidUser(string $username, string $password): void
    {
        $data = [
            'username'  => 'test-user',
            'firstName' => 'test',
            'lastName'  => 'user',
            'email'     => 'test-user@test.com',
        ];

        $client = $this->getTestClient($username, $password);
        $client->request('POST', $this->baseUrl, [], [], [], JSON::encode($data));

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(403, $response->getStatusCode(), "Response:\n" . $response);
        static::assertJsonStringEqualsJsonString(
            '{"message":"Access denied.","code":0,"status":403}',
            $response->getContent(),
            "Response:\n" . $response
        );

        unset($response, $client);
    }

    /**
     * @depends testThatCreateActionWorksLikeExpected
     *
     * @param string $userId
     *
     * @return string
     *
     * @throws Throwable
     */
    public function testThatUpdateActionWorksLikeExpected(string $userId): string
    {
        $data = [
            'id'        => $userId,
            'username'  => 'test-user',
            'firstName' => 'test-1',
            'lastName'  => 'user-2',
            'email'     => 'test-user@test.com',
        ];

        $client = $this->getTestClient('john-root', 'password-root');
        $client->request('PUT', $this->baseUrl . '/' . $userId, [], [], [], JSON::encode($data));

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(200, $response->getStatusCode(), $response->getContent() . "\nResponse:\n" . $response);

        $data['id'] = $userId;

        static::assertJsonStringEqualsJsonString(JSON::encode($data), $response->getContent());

        unset($response, $client);

        return $userId;
    }

    /**
     * @depends testThatCreateActionWorksLikeExpected
     *
     * @param string $userId
     *
     * @return string
     *
     * @throws Throwable
     */
    public function testThatUpdateActionDoesNotWorkWithPartialData(string $userId): string
    {
        $data = [
            'id'        => $userId,
            'email'     => 'test-user@test.com',
        ];

        $client = $this->getTestClient('john-root', 'password-root');
        $client->request('PUT', $this->baseUrl . '/' . $userId, [], [], [], JSON::encode($data));

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(400, $response->getStatusCode(), $response->getContent() . "\nResponse:\n" . $response);

        unset($response, $client);

        return $userId;
    }

    /**
     * @depends      testThatUpdateActionWorksLikeExpected
     * @dataProvider dataProviderInvalidUsersCreate
     *
     * @param string $username
     * @param string $password
     * @param string $userId
     *
     * @return string
     *
     * @throws Throwable
     */
    public function testThatUpdateActionReturns403ForInvalidUser(
        string $username,
        string $password,
        string $userId
    ): string {
        $data = [
            'username'  => 'test-user',
            'firstName' => 'test-1',
            'lastName'  => 'user-2',
            'email'     => 'test-user@test.com',
        ];

        $client = $this->getTestClient($username, $password);
        $client->request('PUT', $this->baseUrl . '/' . $userId, [], [], [], JSON::encode($data));

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(403, $response->getStatusCode(), "Response:\n" . $response);
        static::assertJsonStringEqualsJsonString(
            '{"message":"Access denied.","code":0,"status":403}',
            $response->getContent(),
            "Response:\n" . $response
        );

        unset($response, $client);

        return $userId;
    }

    /**
     * @depends testThatCreateActionWorksLikeExpected
     *
     * @param string $userId
     *
     * @return string
     *
     * @throws Throwable
     */
    public function testThatPatchActionWorskWithPartialData(string $userId): string
    {
        $data = [
            'id'        => $userId,
            'email'     => 'test-user2@test.com',
        ];

        $expectedData = [
            'id'        => $userId,
            'username'  => 'test-user',
            'firstName' => 'test-1',
            'lastName'  => 'user-2',
            'email'     => 'test-user2@test.com',
        ];

        $client = $this->getTestClient('john-root', 'password-root');
        $client->request('PATCH', $this->baseUrl . '/' . $userId, [], [], [], JSON::encode($data));

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(200, $response->getStatusCode(), $response->getContent() . "\nResponse:\n" . $response);
        static::assertJsonStringEqualsJsonString(JSON::encode($expectedData), $response->getContent());

        unset($response, $client);

        return $userId;
    }

    /**
     * @depends      testThatUpdateActionWorksLikeExpected
     * @dataProvider dataProviderInvalidUsersCreate
     *
     * @param string $username
     * @param string $password
     * @param string $userId
     *
     * @throws Throwable
     */
    public function testThatDeleteActionReturns403ForInvalidUser(
        string $username,
        string $password,
        string $userId
    ): void {
        $client = $this->getTestClient($username, $password);
        $client->request('DELETE', $this->baseUrl . '/' . $userId);

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(403, $response->getStatusCode(), "Response:\n" . $response);
        static::assertJsonStringEqualsJsonString(
            '{"message":"Access denied.","code":0,"status":403}',
            $response->getContent(),
            "Response:\n" . $response
        );

        unset($response, $client);
    }

    /**
     * @depends testThatUpdateActionWorksLikeExpected
     *
     * @param string $userId
     *
     * @throws Throwable
     */
    public function testThatDeleteActionWorksLikeExpected(string $userId): void
    {
        $client = $this->getTestClient('john-root', 'password-root');
        $client->request('DELETE', $this->baseUrl . '/' . $userId);

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(200, $response->getStatusCode(), $response->getContent() . "Response:\n" . $response);

        unset($response, $client);
    }

    /**
     * @throws Throwable
     */
    public function testThatDeleteActionThrowsAnExceptionIfUserTriesToRemoveHimself(): void
    {
        /** @var UserResource $userResource */
        $userResource = static::$container->get(UserResource::class);

        $user = $userResource->findOneBy(['username' => 'john-root'], null, true);

        $client = $this->getTestClient('john-root', 'password-root');
        $client->request('DELETE', $this->baseUrl . '/' . $user->getId());

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(400, $response->getStatusCode(), $response->getContent() . "\nResponse:\n" . $response);
        static::assertJsonStringEqualsJsonString(
            '{"message":"You cannot remove yourself...","code":0,"status":400}',
            $response->getContent()
        );

        unset($response, $client, $user, $userResource);
    }

    /**
     * @dataProvider dataProviderTestThatGetRolesActionsReturns403ForInvalidUser
     *
     * @param string $username
     * @param string $password
     * @param array  $userIds
     *
     * @throws Throwable
     */
    public function testThatGetUserRolesActionsReturns403ForInvalidUser(
        string $username,
        string $password,
        array $userIds
    ): void {
        $client = $this->getTestClient($username, $password);

        foreach ($userIds as $userId) {
            $client->request('GET', $this->baseUrl . '/' . $userId . '/roles');

            /** @var Response $response */
            $response = $client->getResponse();

            static::assertInstanceOf(Response::class, $response);
            static::assertSame(403, $response->getStatusCode(), $response->getContent() . "\nResponse:\n" . $response);

            unset($response);
        }

        unset($client);
    }

    /**
     * @dataProvider dataProviderTestThatGetRolesActionsReturns200ForUserHimself
     *
     * @param string $username
     * @param string $password
     * @param string $userId
     * @param string $expectedResponse
     *
     * @throws Throwable
     */
    public function testThatGetUserRolesActionsReturns200ForUserHimself(
        string $username,
        string $password,
        string $userId,
        string $expectedResponse
    ): void {
        $client = $this->getTestClient($username, $password);
        $client->request('GET', $this->baseUrl . '/' . $userId . '/roles');

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(200, $response->getStatusCode(), $response->getContent() . "\nResponse:\n" . $response);
        static::assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());

        unset($response, $client);
    }

    /**
     * @dataProvider dataProviderTestThatGetRolesActionReturns200ForRootRoleUser
     *
     * @param string $userId
     * @param string $expectedResponse
     *
     * @throws Throwable
     */
    public function testThatGetUserRolesActionReturns200ForRootRoleUser(string $userId, string $expectedResponse): void
    {
        $client = $this->getTestClient('john-root', 'password-root');
        $client->request('GET', $this->baseUrl . '/' . $userId . '/roles');

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(200, $response->getStatusCode(), $response->getContent() . "\nResponse:\n" . $response);
        static::assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());

        unset($response, $client);
    }

    /**
     * @dataProvider dataProviderTestThatGetUserGroupsActionsReturns403ForInvalidUser
     *
     * @param string $username
     * @param string $password
     * @param array  $userIds
     *
     * @throws Throwable
     */
    public function testThatGetUserGroupsActionsReturns403ForInvalidUser(
        string $username,
        string $password,
        array $userIds
    ): void {
        $client = $this->getTestClient($username, $password);

        foreach ($userIds as $userId) {
            $client->request('GET', $this->baseUrl . '/' . $userId . '/groups');

            /** @var Response $response */
            $response = $client->getResponse();

            static::assertInstanceOf(Response::class, $response);
            static::assertSame(403, $response->getStatusCode(), $response->getContent() . "\nResponse:\n" . $response);

            unset($response);
        }

        unset($client);
    }

    /**
     * @dataProvider dataProviderTestThatGetUserGroupsActionsReturns200ForUserHimself
     *
     * @param string $username
     * @param string $password
     * @param string $expectedResponse
     * @param string $userId
     *
     * @throws Throwable
     */
    public function testThatGetUserGroupsActionsReturns200ForUserHimself(
        string $username,
        string $password,
        string $expectedResponse,
        string $userId
    ): void {
        $client = $this->getTestClient($username, $password);
        $client->request('GET', $this->baseUrl . '/' . $userId . '/groups');

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(200, $response->getStatusCode(), $response->getContent() . "\nResponse:\n" . $response);

        $data = JSON::decode($response->getContent());

        if ($expectedResponse === '') {
            static::assertEmpty($data);
        } else {
            static::assertSame($expectedResponse, $data[0]->role->id);
        }

        unset($data, $response, $client);
    }

    /**
     * @dataProvider dataProviderInvalidUsersCreate
     *
     * @param string $username
     * @param string $password
     *
     * @throws Throwable
     */
    public function testThatAttachUserGroupActionReturns403ForInvalidUser(string $username, string $password): void
    {
        /**
         * @var UserResource      $userResource
         * @var UserGroupResource $userGroupResource
         */
        $userResource = static::$container->get(UserResource::class);
        $userGroupResource = static::$container->get(UserGroupResource::class);

        $user = $userResource->findOneBy(['username' => $username]);
        $userGroup = $userGroupResource->findOneBy(['name' => 'Root users']);
        $url = sprintf(
            '%s/%s/group/%s',
            $this->baseUrl,
            $user->getId(),
            $userGroup->getId()
        );

        $client = $this->getTestClient($username, $password);
        $client->request('POST', $url);

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(403, $response->getStatusCode(), "Response:\n" . $response);
        static::assertJsonStringEqualsJsonString(
            '{"message":"Access denied.","code":0,"status":403}',
            $response->getContent(),
            "Response:\n" . $response
        );

        unset($response, $client, $userGroup, $user, $userGroupResource, $userResource);
    }

    /**
     * @dataProvider dataProviderTestThatAttachUserGroupActionWorksAsExpected
     *
     * @param int $expectedStatus
     *
     * @throws Throwable
     */
    public function testThatAttachUserGroupActionWorksAsExpected(int $expectedStatus): void
    {
        /**
         * @var UserResource      $userResource
         * @var UserGroupResource $userGroupResource
         */
        $userResource = static::$container->get(UserResource::class);
        $userGroupResource = static::$container->get(UserGroupResource::class);

        $user = $userResource->findOneBy(['username' => 'john']);
        $userGroup = $userGroupResource->findOneBy(['name' => 'Root users']);
        $url = sprintf(
            '%s/%s/group/%s',
            $this->baseUrl,
            $user->getId(),
            $userGroup->getId()
        );

        $client = $this->getTestClient('john-root', 'password-root');
        $client->request('POST', $url);

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame($expectedStatus, $response->getStatusCode(), "Response:\n" . $response);
        static::assertCount(1, JSON::decode($response->getContent()));

        unset($response, $client, $userGroup, $user, $userGroupResource, $userResource);
    }

    /**
     * @depends testThatAttachUserGroupActionWorksAsExpected
     *
     * @throws Throwable
     */
    public function testThatDetachUserGroupActionWorksAsExpected(): void
    {
        /**
         * @var UserResource      $userResource
         * @var UserGroupResource $userGroupResource
         */
        $userResource = static::$container->get(UserResource::class);
        $userGroupResource = static::$container->get(UserGroupResource::class);

        $user = $userResource->findOneBy(['username' => 'john']);
        $userGroup = $userGroupResource->findOneBy(['name' => 'Root users']);
        $url = sprintf(
            '%s/%s/group/%s',
            $this->baseUrl,
            $user->getId(),
            $userGroup->getId()
        );

        $client = $this->getTestClient('john-root', 'password-root');
        $client->request('DELETE', $url);

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(200, $response->getStatusCode(), "Response:\n" . $response);
        static::assertCount(0, JSON::decode($response->getContent()));

        unset($response, $client, $userGroup, $user, $userGroupResource, $userResource);
    }

    /**
     * @depends      testThatDetachUserGroupActionWorksAsExpected
     *
     * @dataProvider dataProviderInvalidUsersCreate
     *
     * @param string $username
     * @param string $password
     *
     * @throws Throwable
     */
    public function testThatDetachUserGroupActionReturns403ForInvalidUser(string $username, string $password): void
    {
        /**
         * @var UserResource      $userResource
         * @var UserGroupResource $userGroupResource
         */
        $userResource = static::$container->get(UserResource::class);
        $userGroupResource = static::$container->get(UserGroupResource::class);

        $user = $userResource->findOneBy(['username' => $username]);
        $userGroup = $userGroupResource->findOneBy(['name' => 'Root users']);
        $url = sprintf(
            '%s/%s/group/%s',
            $this->baseUrl,
            $user->getId(),
            $userGroup->getId()
        );

        $client = $this->getTestClient($username, $password);
        $client->request('DELETE', $url);

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(403, $response->getStatusCode(), "Response:\n" . $response);
        static::assertJsonStringEqualsJsonString(
            '{"message":"Access denied.","code":0,"status":403}',
            $response->getContent(),
            "Response:\n" . $response
        );

        unset($response, $client, $userGroup, $user, $userGroupResource, $userResource);
    }

    /**
     * @dataProvider dataProviderTestThatGetUserGroupsActionReturns200ForRootRoleUser
     *
     * depends testThatDetachUserGroupActionWorksAsExpected
     *
     * @param string $userId
     * @param string $expectedResponse
     *
     * @throws Throwable
     */
    public function testThatGetUserGroupsActionReturns200ForRootRoleUser(
        string $userId,
        string $expectedResponse = null
    ): void {
        $client = $this->getTestClient('john-root', 'password-root');
        $client->request('GET', $this->baseUrl . '/' . $userId . '/groups');

        /** @var Response $response */
        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);
        static::assertSame(200, $response->getStatusCode(), $response->getContent() . "\nResponse:\n" . $response);

        $data = JSON::decode($response->getContent());

        if ($expectedResponse === null) {
            static::assertEmpty($data);
        } else {
            static::assertSame($expectedResponse, $data[0]->role->id, $response->getContent());
        }

        unset($data, $response, $client);
    }

    /**
     * @return Generator
     */
    public function dataProviderValidUsers(): Generator
    {
        yield ['john-admin',  'password-admin'];
        yield ['john-root',   'password-root'];
    }

    /**
     * @return Generator
     */
    public function dataProviderValidApiKeyUsers(): Generator
    {
        yield ['admin'];
        yield ['root'];
    }

    /**
     * @return Generator
     */
    public function dataProviderInvalidUsers(): Generator
    {
        yield ['john',        'password'];
        yield ['john-api',    'password-api'];
        yield ['john-logged', 'password-logged'];
        yield ['john-user',   'password-user'];
    }

    /**
     * @return Generator
     */
    public function dataProviderInvalidApiKeyUsers(): Generator
    {
        yield ['api'];
        yield ['logged'];
        yield ['user'];
    }

    /**
     * @return Generator
     */
    public function dataProviderTestThatAttachUserGroupActionWorksAsExpected(): Generator
    {
        yield [201];
        yield [200];
    }

    /**
     * @return Generator
     */
    public function dataProviderInvalidUsersCreate(): Generator
    {
        yield ['john',        'password'];
        yield ['john-api',    'password-api'];
        yield ['john-logged', 'password-logged'];
        yield ['john-user',   'password-user'];
        yield ['john-admin',  'password-admin'];
    }

    /**
     * @return Generator
     *
     * @throws Throwable
     */
    public function dataProviderTestThatGetRolesActionsReturns403ForInvalidUser(): Generator
    {
        static::bootKernel();

        /** @var UserResource $userResource */
        $userResource = static::$container->get(UserResource::class);

        $users = $userResource->find();

        foreach ($this->dataProviderInvalidUsersCreate() as $userData) {
            $ids = [];

            foreach ($users as $user) {
                if ($user->getUsername() === $userData[0]) {
                    continue;
                }

                $ids[] = $user->getId();
            }

            $userData[] = $ids;

            yield $userData;
        }
    }

    /**
     * @return array
     *
     * @throws Throwable
     */
    public function dataProviderTestThatGetRolesActionsReturns200ForUserHimself(): array
    {
        static::bootKernel();

        /** @var UserResource $userResource */
        $userResource = static::$container->get(UserResource::class);

        /** @var RolesService $rolesService */
        $rolesService = static::$container->get(RolesService::class);

        $users = $userResource->find();

        $iterator = static function (array $userData) use ($users, $rolesService): array {
            /** @var User $user */
            $user = array_values(
                array_filter(
                    $users,
                    static function (User $user) use ($userData) {
                        return $userData[0] === $user->getUsername();
                    }
                )
            )[0];

            $user->setRolesService($rolesService);

            $userData[] = $user->getId();
            $userData[] = JSON::encode($user->getRoles());

            return $userData;
        };

        $credentials = [
            ['john',        'password'],
            ['john-api',    'password-api'],
            ['john-logged', 'password-logged'],
            ['john-user',   'password-user'],
            ['john-admin',  'password-admin'],
            ['john-root',   'password-root'],
        ];

        return array_map($iterator, $credentials);
    }

    /**
     * @return array
     *
     * @throws Throwable
     */
    public function dataProviderTestThatGetRolesActionReturns200ForRootRoleUser(): array
    {
        static::bootKernel();

        /** @var UserResource $userResource */
        $userResource = static::$container->get(UserResource::class);

        /** @var RolesService $rolesService */
        $rolesService = static::$container->get(RolesService::class);

        $output = [];

        foreach ($userResource->find() as $user) {
            $user->setRolesService($rolesService);

            $output[] = [$user->getId(), JSON::encode($user->getRoles())];
        }

        return $output;
    }

    /**
     * @return Generator
     *
     * @throws Throwable
     */
    public function dataProviderTestThatGetUserGroupsActionsReturns403ForInvalidUser(): Generator
    {
        return $this->dataProviderTestThatGetRolesActionsReturns403ForInvalidUser();
    }

    /**
     * @return array
     *
     * @throws Throwable
     */
    public function dataProviderTestThatGetUserGroupsActionsReturns200ForUserHimself(): array
    {
        static::bootKernel();

        /** @var UserResource $userResource */
        $userResource = static::$container->get(UserResource::class);

        $users = $userResource->find();

        $iterator = static function (array $userData) use ($users): array {
            /** @var User $user */
            $user = array_values(
                array_filter(
                    $users,
                    static function (User $user) use ($userData) {
                        return $userData[0] === $user->getUsername();
                    }
                )
            )[0];

            $userData[] = $user->getId();

            return $userData;
        };

        $credentials = [
            ['john',        'password',         ''],
            ['john-api',    'password-api',     'ROLE_API'],
            ['john-logged', 'password-logged',  'ROLE_LOGGED'],
            ['john-user',   'password-user',    'ROLE_USER'],
            ['john-admin',  'password-admin',   'ROLE_ADMIN'],
            ['john-root',   'password-root',    'ROLE_ROOT'],
        ];

        return array_map($iterator, $credentials);
    }

    /**
     * @return Generator
     *
     * @throws Throwable
     */
    public function dataProviderTestThatGetUserGroupsActionReturns200ForRootRoleUser(): Generator
    {
        static::bootKernel();

        /** @var UserResource $userResource */
        $userResource = static::$container->get(UserResource::class);

        foreach ($userResource->find() as $user) {
            yield [$user->getId(), count($user->getRoles()) ? $user->getRoles()[0] : null];
        }
    }

    /**
     * @return Generator
     *
     * @throws Throwable
     */
    public function dataProviderTestThatUpdateActionDoesNotWorkWithTakenUsername(): Generator
    {
        static::bootKernel();

        /** @var UserResource $userResource */
        $userResource = static::$container->get(UserResource::class);

        yield $userResource->findOneBy(['username' => 'john']);
    }
}
