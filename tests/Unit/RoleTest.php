<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Jsalam\UGRPM\Role;

/**
 * @covers Role
 */
class RoleTest extends TestCase{
    
    /** @test */
    public function test_build_get_set_role():void{
        $role = new Role(id:2,role:"App\Content@get");

        $expected = [2, "App\Content", "get"];
        $this->assertEquals($expected, [$role->getId(), $role->getRoleClass(), $role->getRoleMethod()]);

        $role->setId(10);
        $role->setRoleClass("Apps\Author");
        $role->setRoleMethod("create");
        $expected2 = [10, "Apps\Author", "create"];

        $this->assertEquals($expected2, [$role->getId(), $role->getRoleClass(), $role->getRoleMethod()]);
    }
}