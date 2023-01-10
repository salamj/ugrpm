<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Jsalam\UGRPM\Group;

/**
 * @covers Group
 */
class GroupTest extends TestCase{
    
    /** @test */
    public function test_build_get_set_group():void{
        $group = new Group(id:5,groupName:"Editors",description:"Users with editing ability");

        $expected = [5, "Editors", "Users with editing ability"];
        $this->assertEquals($expected, [$group->getId(), $group->getGroupName(), $group->getDescription()]);

        $group->setId(10);
        $group->setGroupName("Articles Editors");
        $group->setDescription("Users with articles editing ability");
        $expected2 = [10, "Articles Editors", "Users with articles editing ability"];
        $this->assertEquals($expected2, [$group->getId(), $group->getGroupName(), $group->getDescription()]);
    }
}