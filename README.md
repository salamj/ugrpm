
# UGRPM

  

A simple and easy to use PHP object oriented lirbary to manage (Users,Groups,Roles).

  

This library use MySql database and PDO Object to connect.

  

It's work with user as his **id** and don't care what the table you had made or its columns.

  

You **must** have a database connection and pass it as PDO connection object to the UGRPM constructor.

  

Built by Salam Aljehni _([https://aljehni.com](https://www.aljehni.com))_

  

Library home: _([https://aljehni.com/ugrpm/](https://www.aljehni.com/ugrpm))_

  

Github link _([https://aljehni.com/ugrpm/](https://www.aljehni.com/ugrpm))_

  

  

## Installation

  

  

*  _Import tables from sql directory to your database_

*  _Add namespace where you want to use UGRPM, Group or Role classes **only** what you need._

```php
use Jsalam\UGRPM\UGRPM;
use Jsalam\UGRPM\Role;
use Jsalam\UGRPM\Group;
```

OR
```php
use Jsalam\UGRPM\{UGRPM,Group,Role};
```

If you would use <mark>_try ... catch_</mark> you must use exceptions classes.
##### Exceptions using will be discuss below. 

## Usage

* First, you _**must**_ create a new instance of UGRPM class by passing your PDO connection object.

Suppose you create PDO object like this:

```php
$connect = new  PDO('mysql:host=localhost;dbname=DB_NAME','DB_USER','DB_PASSWORD');
```

Then, create UGRPM object:

```php
$ugrpm = new  UGRPM($connect);
```

#### Roles

##### Create Role

Role class constructor accept two parameters, `id` and `role` , to create a new Role instance use:

```php
use Jsalam\UGRPM\Role;
$role = new  Role(id:3,role:"Apps\Library@create");
```

Default value to `id` is `0` and `""` to role.

If Role not exists in the database and you want to create one **do not pass**  `id`.

After that you can work with `$role` object by it's methods:

```php
$id = $role->getId(); // id in database
$roleRole = $role->getRole(); //"App\Content@create"
$class = $role->getRoleClass(); // "App\Content"
$method = $role->getRoleMethod();// "create"
// Change role properties
$role->setId(44);
$role->setRoleClass("Apps\Content");
$role->setRoleMethod("add");
// or both in one
$role->setRole("Apps\Content@add");
```

##### Work With Roles

Insert role to database, retrieve role(s) by (id,role,class,method,class and method) ,get all roles and remove role.

```php
use Jsalam\URRPM\UGRPM;
use Jsalam\UGRPM\Role;
$role = $ugrpm->createRole(new  Role(role:"App\Content@create"));
$getRole = $ugrpm->getRoleById(#ID); // int ID
$getRoles1 =$ugrpm->getRolesByClass("Class\Namespace"); // array of Roles
$getRoles2 = $ugrpm->getRolesByMethod("create");// array of roles
$getRoles3 = $ugrpm->getRoleByClassMethod("App\Content@create");// One role or empty
$allRoles = $ugrpm->getAllRoles(); // array of roles
// Remove Role
$ugrpm->removeRole($role);//true
```

### Groups

UGRPM enable you to create groups with (id,name,description) properties, then, you may work with them like add role to groups , add users to group, update groups ...

#### Create Group

Group class constructor accept three parameters, `id` and `groupName`, `description` , to create a new Group instance use:

```php
use Jsalam\UGRPM\Group;

$group = new  Group(id:11,groupName:"Editors",description:"Editors Group");
```

Default value to `id` is `0` and `""` to `groupName` and `description`.
If group not exists in the database and you want to create one **do not pass**  `id`.
After that you can work with `$group` object by it's methods:

```php
$id = $group->getId(); // id in database
$name = $grouo->getGroupName(); //"Editors"
$desc = $group->getDescription(); // "Editors Group"
// Change group properties
$group>setId(4);
$group->setGroupName("Articles Editors");
$group->setDescription("Articles Editors Group");
```

##### Work With Groups

Insert group to database, retrieve group(s) by (`id`,`groupName`) ,get all groups and remove group.

```php
use Jsalam\UGRPM\Group;
use Jsalam\UGRPM\UGRPM;

$group = $ugrpm->createGroup(new  Group(groupName:"Editors",description:"Editors Group"));
$group->setDescription("Group of Editors");
$ugrpm->updateGroup($group);

$group1 = $ugrpm->getGroupById(33);
$group2 = $ugrpm->getGroupByGroupName("Gallary Managers");

$allGroups = $ugrpm->getAllGroups();// array of groups.

$ugrpm->removeGroup($group);
```

### Group Roles

In this section you will learn how to work with (Role-Group) methods.
We will make example contains groups and roles and make relations between them.

```php
use Jsalam\UGRPM\Group;
use Jsalam\UGRPM\Role;
use Jsalam\UGRPM\UGRPM;
// ... $ugrpm initialized before, see installation above

//Roles

$roleCreateArticle = $ugrpm->createRole(new  Role(role:"App\Article\create"));
$roleEditArticle = $ugrpm->createRole(new  Role(role:"App\Article\edit"));
$roleCreateContent = $ugrpm->createRole(new  Role(role:"App\Content\create"));
$roleEditContent = $ugrpm->createRole(new  Role(role:"App\Content\edit"));

// Groups
$createGroup = $ugrpm->createGroup(new  Group(groupName:"Creators",description:"Creators Group"));
$editGroup = $ugrpm->createGroup(new  Group(groupName:"Editors",description:"Editors Group"));
$manageGroup = $ugrpm->createGroup(new  Group(groupName:"Managers",description:"Managers Group"));

$ugrpm->createGroupRole($createGroup,$roleCreateArticle);
$ugrpm->createGroupRole($createGroup,$roleCreateContent);

// We can do in one:
$ugrpm->createGroupRoles($editGroup,[$roleEditArticle,$roleEditContent]);

// getting group's roles
$ugrpm->createGroupRoles($manageGroup,array_merge($ugrpm->getGroupRoles($createGroup),$ugrpm->getGroupRoles($editGroup)));

// get Groups have edit artices role.
$groupsEditing = $ugrpm->getRoleGroups($roleEditArticle); // [$createGroup , $manageGroup]

// remove group's editing roles
$ugrpm->removeGroupRoles($manageGroup,[$roleEditArticle,$roleEditContent]);
```

### User Roles

Like (Group-Roles) we can add ,retrieve and remove roles to users or users to roles.
Without many examples, these are the methods you can use:

```php
$user1Id = 10;
$user2Id = 32

$role1 = // ...
$role2 = // ...

$roles = [$role10,$role20,$role30//,...];
// Add $role1 to the user1Id
$ugrpm->createUserRole($user1Id,$role1);

//Add $roles to the $user2Id
$ugrpm->createUserRoles($user2Id,$roles);

// Add The $role2 to array of users IDs
$ugrpm->createRoleUsers($role2,[19,$user2Id,9]);

// Remove $role2 from $user1Id
$ugrpm->removeUserRole($user1Id,$role2);

// Remove all $roles from the user whos id is 19
$ugrpm->removeUserRoles(19,$roles);

// Remove the users in the array from the $role2
$ugrpm->removeRoleUsers($role2,[22,$use1Id,199]);

// Check if $user1Id have the role $role2
$ugrpm->userHaveRole($user1Id,$role2);

// Get roles belongs to the user $user1Id, His groups'role not included
$ugrpm->getUserRoles($user1Id);

// Get All roles belongs to the user $user1Id and his groups'role
$ugrpm->getAllUserRoles($user2Id);

// Retrieve users who have the role $role1
$ugrpm->getRoleUsers($role1);
```

### User Group

Since users may belongs to groups, we can add user(s) to group(s) and retrieve group's users or user's group , and removing in the way.

These method are available to use.

```php
// Add the user with $userId to $group
$ugrpm->addUserToGroup($userId,$group);

// AddMany users to $group
$ugrpm->addUsersToGroup([$userId1,$userId2,...],$group);

// Add user to many groups
$ugrpm->addUserToGroups($userId,[$group1,$group2,...]);

// Add many users to many groups
$ugrpm->addUsersToGroups([$uid1,$uid2,$uid3],[$group1,$group2,$group3]);

// Remove user from Group
$ugrpm->removeUserFromGroup($uid,$group);

// Remove user from many groups
$ugrpm->removeUserFromGroups($uid,[$group1,$group2,...]);

// Remove many users from group
$ugrpm->removeUsersFromGroup([$uid1,$uid2,...],$group);

// Retrieve user's groups
$ugrpm->getUserGroups($userId);

// Retrieve group's users
$ugrpm->getGroupUsers($group);

// Check if user in group
$ugrpm->userInGroup($uid,$group);
```

There are Role several exceptions to use, namespaces for them are:

### Exceptions

* Group Exceptions
* Role Exceptions
* Group Role Exceptions
* User Group Exceptions
* User Role Exceptions

#### Group Exceptions

-  `Jsalam\UGRPM\Exceptions\GroupExceptions\DuplicatedGroupException`

Catched when trying to create a group with a name that already existing.

```php
$group = new Group(id:10;groupName:"Editors",description:"Users with editing ability");
$ugrpm->createGroup($group); // will throw DuplicatedGroupException if group with "Editors" existed before.
try{
	$ugrpm->createGroup($group);
}catch(DuplicatedGroupException $e){
	echo $e->getMessage(); // or what you want
}
```

-  `Jsalam\UGRPM\Exceptions\GroupExceptions\GroupNotFoundException`

Catched when trying to get group that's not found in database using `getGroupById` or `getGroupByGroupName`.

-  `Jsalam\UGRPM\Exceptions\GroupExceptions\GroupTypeException`

Catched when passing array of groups that contain item with invalid `Group Object` or Integer value (group id).

Like *createRoleGroups($role,[`ARRAY_OF_GROUPS`])*.

#### Role Exceptions

-  `Jsalam\UGRPM\Exceptions\RoleExceptions\DuplicatedRoleException`

Catched when create a role with `role` property that already exists in database using `createRole()` method.

-  `Jsalam\UGRPM\Exceptions\RoleExceptions\InvalidRoleClassNameException`

 Catched when create *new Role(role:`classNamespace`@method)* with invalid role class, that class must be a valid namespace like `Apps\Content` or `\Apps\Content\Article` ...

-  `Jsalam\UGRPM\Exceptions\RoleExceptions\RoleNameException`
  
Catched when create a role with `role` invalid property, `role`  **must** be `classNamespace` then `@` then `method name`.

-  `Jsalam\UGRPM\Exceptions\RoleExceptions\RoleNotFoundException`

Catched when trying to get role that's not found in database using `getRoleById` or `getRolesByClass`...

-  `Jsalam\UGRPM\Exceptions\RoleExceptions\RoleTypeException`'

Catched when passing array of roles that contain item with invalid `Role Object` or Integer value (role id).

Like createGroupRoles($group,[`ARRAY_OF_ROLES`])
 

#### Group Role Exceptions

-  `Jsalam\UGRPM\Exceptions\GroupRoleExceptions\GroupAlreadyHasRoleException`

Catched when trying to add role(s) to group(s) that already have that role(s).

#### User Group Exceptions

-  `Jsalam\UGRPM\Exceptions\UserGroupExceptions\UserAlreadyInGroupException`

Catched when trying to add user(s) to group(s) where already in that group role(s).

### User Role Exceptions

-  `Jsalam\UGRPM\Exceptions\UserRoleExceptions\UserAlreadyHasRoleException`

Catched when trying to add role(s) to user(s) where already have that role(s).

## Credit

Salam Aljehni, salam[at]gmail.com, link: (aljehni.com)[https://aljehni.com]
