<?php

namespace Zoop\Shard\Test\User;

use Zoop\Shard\Manifest;
use Zoop\Shard\Test\BaseTest;
use Zoop\Shard\Test\TestAsset\RoleAwareUser;

class RoleAwareUserTest extends BaseTest {

    public function setUp(){

        $manifest = new Manifest([
            'service_manager_config' => [
                'factories' => [
                    'user' => function(){
                        $user = new RoleAwareUser();
                        $user->setUsername('toby');
                        return $user;
                    }
                ]
            ]
        ]);

        $this->user = $manifest->getServiceManager()->get('user');
    }

    public function testRoleAddandRemove(){

        $user = $this->user;
        $user->setRoles(array('1', '2'));

        $this->assertEquals(array('1', '2'), $user->getRoles());

        $user->removeRole('2');

        $this->assertEquals(array('1'), $user->getRoles());
    }
}