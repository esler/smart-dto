<?php

namespace IW;

/**
 * Tests class IW\SmartDTO
 *
 * @author     Ondřej Ešler <ondrej.esler@intraworlds.com>
 * @version    SVN: $Id: $
 */
final class SmartDTOTest extends \PHPUnit\Framework\TestCase
{
    private $_userDTO;

    /**
     * @before
     */
    function createDTO() {
        $this->_userDTO = new UserDTO;
    }

    function testSmartDTO() {
        $this->_userDTO->id = 123;
        $this->_userDTO->username = 'joe.doe';

        $this->assertObjectHasAttribute('id', $this->_userDTO);
        $this->assertSame(123, $this->_userDTO->id);
        $this->assertObjectHasAttribute('username', $this->_userDTO);
        $this->assertSame('joe.doe', $this->_userDTO->username);
    }

    /**
     * @depends testSmartDTO
     */
    function testFailWhenUndefinedPropertyRead() {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Trying to read unknown property "im_nobody"');
        $this->_userDTO->im_nobody;
    }

    /**
     * @depends testSmartDTO
     */
    function testFailWhenUndefinedPropertyWrite() {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Trying write to unknown property "im_nobody"');
        $this->_userDTO->im_nobody = 'Arya';
    }

    /**
     * @depends testSmartDTO
     */
    function testPrivateProperty() {
        $this->assertTrue(isset($this->_userDTO->role));
        $this->assertSame('member', $this->_userDTO->role);
        $this->_userDTO->role = 'admin';
        $this->assertSame('admin', $this->_userDTO->role);
    }

    /**
     * @depends testSmartDTO
     */
    function testWriteTroughTheHandler() {
        $this->_userDTO->config = ['foo' => 'bar'];
        $this->assertSame(['foo' => 'bar'], $this->_userDTO->config);

        $this->_userDTO->config = '{"hello":"world"}';
        $this->assertIsArray($this->_userDTO->config);
        $this->assertSame(['hello' => 'world'], $this->_userDTO->config);
    }

    /**
     * @depends testSmartDTO
     */
    function testFetchingFromPDO() {
        $pdo = new \PDO('sqlite:db');
        $stmt = $pdo->query('SELECT * FROM user', \PDO::FETCH_CLASS, UserDTO::class);

        $results = $stmt->fetchAll();
        $this->assertIsArray($results);

        $userDTO = $results[0];
        $this->assertInstanceOf(UserDTO::class, $userDTO);
        $this->assertIsArray($userDTO->config); // json_decode when loading
    }

    function testHydratation() {
        $userDTO = new UserDTO;
        $this->assertNull($userDTO->id);
        $userDTO->hydrate(['id' => 951]);
        $this->assertSame(951, $userDTO->id);
    }

    /**
     * @depends testHydratation
     */
    function testExtraction() {
        $userDTO = new UserDTO;
        $userDTO->hydrate(['id' => 666, 'username' => 'Spiderman', 'config' => ['foo' => 'bar']]);

        $expected = ['id' => 666, 'username' => 'Spiderman', 'role' => 'member', 'config' => ['foo' => 'bar']];
        $this->assertSame($expected, $userDTO->extract());
    }

    /**
     * @depends testExtraction
     */
    function testSnakeCase() {
        $dto = new class {
            use SmartDTO;

            public $access_rights = [];
            private $_contact_email;
            private $my_buddies = [];

            public function setMyBuddies(array $myBuddies): void {
                $this->my_buddies = $myBuddies;
            }
        };

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
    function testCamelCase() {
        $dto = new class {
            use SmartDTO;

            public $accessRights = [];
            private $_contactEmail;
            private $myBuddies = [];

            public function setMyBuddies(array $myBuddies): void {
                $this->myBuddies = $myBuddies;
            }
        };

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

final class UserDTO
{
    use SmartDTO;

    public $id;
    public $username;
    private $_role = 'member';
    private $_config;

    private function setConfig($value) {
        if (is_string($value)) {
            $this->_config = json_decode($value, true);
        } else {
            $this->_config = $value;
        }
    }
}
