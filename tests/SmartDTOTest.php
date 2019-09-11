<?php

declare(strict_types=1);

namespace IW;

use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Tests class IW\SmartDTO
 */
final class SmartDTOTest extends TestCase
{
    /** @var UserDTO */
    private $userDTO;

    /**
     * @before
     */
    public function createDTO() : void
    {
        $this->userDTO = new UserDTO();
    }

    public function testSmartDTO() : void
    {
        $this->userDTO->id       = 123;
        $this->userDTO->username = 'joe.doe';

        $this->assertObjectHasAttribute('id', $this->userDTO);
        $this->assertSame(123, $this->userDTO->id);
        $this->assertObjectHasAttribute('username', $this->userDTO);
        $this->assertSame('joe.doe', $this->userDTO->username);
    }

    /**
     * @depends testSmartDTO
     */
    public function testFailWhenUndefinedPropertyRead() : void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Trying to read unknown property "im_nobody"');
        $this->userDTO->im_nobody;
    }

    /**
     * @depends testSmartDTO
     */
    public function testFailWhenUndefinedPropertyWrite() : void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Trying write to unknown property "im_nobody"');
        $this->userDTO->im_nobody = 'Arya';
    }

    /**
     * @depends testSmartDTO
     */
    public function testPrivateProperty() : void
    {
        $this->assertTrue(isset($this->userDTO->role));
        $this->assertSame('member', $this->userDTO->role);
        $this->userDTO->role = 'admin';
        $this->assertSame('admin', $this->userDTO->role);
    }

    /**
     * @depends testSmartDTO
     */
    public function testWriteTroughTheHandler() : void
    {
        $this->userDTO->config = ['foo' => 'bar'];
        $this->assertSame(['foo' => 'bar'], $this->userDTO->config);

        $this->userDTO->config = '{"hello":"world"}';
        $this->assertIsArray($this->userDTO->config);
        $this->assertSame(['hello' => 'world'], $this->userDTO->config);
    }

    /**
     * @depends testSmartDTO
     */
    public function testFetchingFromPDO() : void
    {
        $pdo  = new PDO('sqlite:db');
        $stmt = $pdo->query('SELECT * FROM user', PDO::FETCH_CLASS, UserDTO::class);

        $results = $stmt->fetchAll();
        $this->assertIsArray($results);

        $userDTO = $results[0];
        $this->assertInstanceOf(UserDTO::class, $userDTO);
        $this->assertIsArray($userDTO->config); // json_decode when loading
    }

    public function testHydratation() : void
    {
        $userDTO = new UserDTO();
        $this->assertNull($userDTO->id);
        $userDTO->hydrate(['id' => 951]);
        $this->assertSame(951, $userDTO->id);
    }

    /**
     * @depends testHydratation
     */
    public function testExtraction() : void
    {
        $userDTO = new UserDTO();
        $userDTO->hydrate(['id' => 666, 'username' => 'Spiderman', 'config' => ['foo' => 'bar']]);

        $expected = ['id' => 666, 'username' => 'Spiderman', 'role' => 'member', 'config' => ['foo' => 'bar']];
        $this->assertSame($expected, $userDTO->extract());
    }

    /**
     * @depends testExtraction
     */
    public function testSnakeCase() : void
    {
        // phpcs:disable
        $dto = new class {
            use SmartDTO;

            public $access_rights = [];
            private $_contact_email;
            private $my_buddies = [];

            public function setMyBuddies(array $myBuddies) : void
            {
                $this->my_buddies = $myBuddies;
            }
        };
        // phpcs:enable

        $dto->access_rights = ['guest'];
        $dto->contact_email = 'simba@example.com';
        $dto->my_buddies    = ['jim', 'bones'];

        $expected = [
            'access_rights' => ['guest'],
            'contact_email' => 'simba@example.com',
            'my_buddies'    => ['jim', 'bones'],
        ];

        $this->assertSame($expected, $dto->extract());
    }

    /**
     * @depends testExtraction
     */
    public function testCamelCase() : void
    {
        // phpcs:disable
        $dto = new class {
            use SmartDTO;

            public $accessRights = [];
            private $_contactEmail;
            private $myBuddies = [];

            public function setMyBuddies(array $myBuddies) : void
            {
                $this->myBuddies = $myBuddies;
            }
        };
        // phpcs:enable

        $dto->accessRights = ['guest'];
        $dto->contactEmail = 'simba@example.com';
        $dto->myBuddies    = ['jim', 'bones'];

        $expected = [
            'accessRights' => ['guest'],
            'contactEmail' => 'simba@example.com',
            'myBuddies'    => ['jim', 'bones'],
        ];

        $this->assertSame($expected, $dto->extract());
    }
}
