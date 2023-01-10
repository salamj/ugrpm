<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Jsalam\UGRPM\{Role, Group, UGRPM};
use \PDO;
/**
 * @covers UGRPM
 */
class UGRPMTest extends TestCase{
    private UGRPM $ugrpm;
    public function setUp():void{
        parent::setUp();
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
  
            $connect = new PDO('mysql:host=localhost;dbname=ugrpm','root','',$options);
        
        $this->ugrpm = new UGRPM($connect);

    }
    public function test_create_retrive_remove_role(): void
    {
        $role = new Role(role: "App\Library\Book@create");
        
        $this->ugrpm->createRole($role);
    

        $getRole = $this->ugrpm->getRoleByClassMethod("App\Library\Book@create");

        $expected = [
            "App\Library\Book",
            "create"
        ];
        $this->assertEquals($expected, [$getRole->getRoleClass(), $getRole->getRoleMethod()]);
        $roleId = $getRole->getId();

        $removeRole = $this->ugrpm->removeRole($this->ugrpm->getRoleById($roleId));
        $this->assertSame($removeRole, true);


        $this->expectException(\Jsalam\UGRPM\Exceptions\RoleExceptions\RoleNotFoundException::class);
        $this->ugrpm->getRoleById(100);
    }

    public function test_create_retrive_update_remove_group(): void
    {
        // create  retrive
        $group = new Group(groupName: "Editors",description:"Users Editing Group");
        $this->ugrpm->createGroup($group);
        $getGroup = $this->ugrpm->getGroupByGroupName("Editors");
        $this->assertEquals($getGroup->getGroupName(), $getGroup->getGroupName());

        // update
        $group->setGroupName("Articles Editors");
        $group->setDescription("Users articles editing group");
        $this->ugrpm->updateGroup($group);
        $getGroup = $this->ugrpm->getGroupById($group->getId());

        $this->assertEquals("Articles Editors", $getGroup->getGroupName());

        // remove
        $deleteGroup = $this->ugrpm->removeGroup($group);
        $this->assertEquals($deleteGroup, true);
    }

    public function test_create_retrive_remove_group_role(): void
    {
        // create group
        $group = $this->ugrpm->createGroup(new Group(groupName: "Photo Editors", description: "Photos Editing Group"));
        
        // create Role 
        $role = $this->ugrpm->createRole(new Role(role:"\Apps\Gallery\Photo@edit"));

        //assign role to group
        $this->ugrpm->createGroupRole($group, $role);
        
        // check group has role
        $this->assertSame(true,$this->ugrpm->groupHaveRole($group, $role));

        // Remove group & role
        $this->ugrpm->removeGroup($group);
        $this->ugrpm->removeRole($role);

    }
    public function test_create_retrive_remove_user_role(): void
    {
        $userId = 10;
        $role = $this->ugrpm->createRole(new Role(role:"\Apps\Gallery\Photo@edit"));
        $this->ugrpm->createUserRole($userId, $role);

        $this->assertSame(true, $this->ugrpm->userHaveRole($userId, $role));

        $this->ugrpm->removeRole($role);
    }

    public function test_create_retrive_remove_group_roles(): void
    {
        $roles = [
            $this->ugrpm->createRole(new Role(role:"Apps\Gallery@manage")),
            $this->ugrpm->createRole(new Role(role:"Apps\Gallery@edit")),
            $this->ugrpm->createRole(new Role(role:"Apps\Gallery@delete")),
            $this->ugrpm->createRole(new Role(role:"Apps\Gallery\Photo@manage"))
        ];

        $group = $this->ugrpm->createGroup(new Group(groupName: "Galary Managers", description: "Group have to manage gallary and photos"));
        $this->ugrpm->createGroupRoles($group, $roles);
        $groupRoles = $this->ugrpm->getGroupRoles($group);
        $this->assertEquals($roles, $groupRoles);

        $this->ugrpm->removeGroupRoles($group, array_slice($roles, 0, 3));
        $groupRoles = $this->ugrpm->getGroupRoles($group);
        $this->assertEquals([$roles[3]], $groupRoles);

        foreach($roles as $role){
            $this->ugrpm->removeRole($role);
        }
        $this->ugrpm->removeGroup($group);

    }

    public function test_create_retrive_remove_user_roles(): void
    {
        $roles = [
            $this->ugrpm->createRole(new Role(role:"Apps\Gallery@manage")),
            $this->ugrpm->createRole(new Role(role:"Apps\Gallery@edit")),
            $this->ugrpm->createRole(new Role(role:"Apps\Gallery@delete")),
            $this->ugrpm->createRole(new Role(role:"Apps\Gallery\Photo@manage"))
        ];
        $userId = 10;
        $this->ugrpm->createUserRoles($userId, $roles);
        $this->assertEquals($roles, $this->ugrpm->getUserRoles($userId));

        $this->ugrpm->removeUserRoles($userId, array_slice($roles, 1, 3));
        $this->assertEquals([$roles[0]], $this->ugrpm->getUserRoles($userId));

        foreach($roles as $role){
            $this->ugrpm->removeRole($role);
        }
    }

    public function test_create_retrive_remove_groups_role(): void
    {
        $role = $this->ugrpm->createRole(new Role(role:"Apps\Gallery@manage"));
        $groups = [
            $this->ugrpm->createGroup(new Group(groupName:"Moderators",description:"Moderators Group")),
            $this->ugrpm->createGroup(new Group(groupName:"Editors",description:"Editors Group")),
        ];

        $this->ugrpm->createRoleGroups($role, $groups);

        $this->assertEquals([$role], $this->ugrpm->getGroupRoles($groups[0]));
        $this->assertEquals([$role], $this->ugrpm->getGroupRoles($groups[1]));

        $this->ugrpm->removeRoleGroups($role,$groups);
        $this->assertEquals([], $this->ugrpm->getRoleGroups($role));
        foreach($groups as $group){
            $this->ugrpm->removeGroup($group);
        }
        $this->ugrpm->removeRole($role);

    }

    public function test_create_retrive_remove_users_role(): void
    {
        $role = $this->ugrpm->createRole(new Role(role:"Apps\Gallery@manage"));
        $users = [10,13,33,122];
        $this->ugrpm->createRoleUsers($role,$users);
        $this->assertEquals($users,$this->ugrpm->getRoleUsers($role));

        $this->ugrpm->removeRoleUsers($role, $users);
        $this->assertEquals([], $this->ugrpm->getRoleUsers($role));

        $this->ugrpm->removeRole($role);

    }

    public function test_add_retrive_remove_user_tofrom_group(): void
    {
        $group = $this->ugrpm->createGroup(new Group(groupName:"Editors",description:"Editors Group"));
        $user = 23;

        $this->ugrpm->addUserToGroup($user, $group);

        $this->assertEquals(true, $this->ugrpm->userInGroup($user, $group));
        $this->assertEquals(false, $this->ugrpm->userInGroup(12, $group)); // other user id
        $this->ugrpm->removeGroup($group);
    }
    public function test_get_user_groups(): void
    {
        $groups = [
            $this->ugrpm->createGroup(new Group(groupName:"Moderators",description:"Moderators Group")),
            $this->ugrpm->createGroup(new Group(groupName:"Editors",description:"Editors Group")),
        ];
        $user = 45;
        $this->ugrpm->addUserToGroups($user, $groups);
        $this->assertEquals([45], $this->ugrpm->getGroupUsers($groups[0]));
        $this->assertEquals([45], $this->ugrpm->getGroupUsers($groups[1]));

        foreach($groups as $group){
            $this->ugrpm->removeGroup($group);
        }
    }



    public function test_user_in_group(): void
    {
        $user = 92;
        $group = $this->ugrpm->createGroup(new Group(groupName:"Editors",description:"Editors Group"));

        $this->ugrpm->addUserToGroup($user, $group);

        $this->assertEquals(true, $this->ugrpm->userInGroup($user, $group));

        $this->ugrpm->removeGroup($group);
    }

    public function test_user_has_role(): void
    {
        $user = 233;
        $role = $this->ugrpm->createRole(new Role(role: "Apps\Gallery@manage"));
        $this->ugrpm->createUserRole($user, $role);
        $this->assertEquals(true, $this->ugrpm->userHaveRole($user, $role));
        $this->assertEquals(false, $this->ugrpm->userHaveRole(10, $role));

        $this->ugrpm->removeRole($role);
    }

    public function test_get_all_user_roles(): void
    {
        $roles = [
            $this->ugrpm->createRole(new Role(role:"Apps\Gallery@manage")),
            $this->ugrpm->createRole(new Role(role:"Apps\Gallery@edit")),
            $this->ugrpm->createRole(new Role(role:"Apps\Gallery@delete")),
            $this->ugrpm->createRole(new Role(role:"Apps\Gallery\Photo@manage"))
        ];
        $singleRole = $this->ugrpm->createRole(new Role(role: "Apps\Users@manage"));
        $group = $this->ugrpm->createGroup(new Group(groupName: "Galary Managers", description: "Group have to manage gallary and photos"));

        $user = 9;
        $this->ugrpm->addUserToGroup($user, $group);
        $this->ugrpm->createGroupRoles($group, $roles);
        $this->ugrpm->createUserRole($user, $singleRole);
        
        $this->assertEquals([$singleRole], $this->ugrpm->getAllUserRoles($user)['self']);
        $this->assertEquals(array_column($roles,"role"), array_column($this->ugrpm->getAllUserRoles($user)[$group->getGroupName()],"role"));


        foreach($roles as $role){
            $this->ugrpm->removeRole($role);
        }
        $this->ugrpm->removeRole($singleRole);
        $this->ugrpm->removeGroup($group);
    }
}