<?php
/**
 * UGRPM Class
 * 
 * @author      Salam Aljehni <salamj@gmail.com>
 * @copyright   Copyright (c), 2023 Salam Aljehni
 * @license     MIT public license
 * @package     UGRPM
 * 
 */
namespace Jsalam\UGRPM;
/**
 * Using Group Exceptions 
 */

use Jsalam\UGRPM\Exceptions\GroupExceptions\{
	DuplicatedGroupException,
	GroupTypeException,
	GroupNotFoundException
};

/**
 * Using GroupRole Exceptions 
 */
use Jsalam\UGRPM\Exceptions\GroupRoleExceptions\{
	GroupAlreadyHasRoleException,
};

/**
 * Using UserGroup Exceptions 
 */
use Jsalam\UGRPM\Exceptions\UserGroupExceptions\{
	UserAlreadyInGroupException
};

/**
 * Using Role Exceptions
 */
use Jsalam\UGRPM\Exceptions\RoleExceptions\{
	RoleNotFoundException,
	DuplicatedRoleException,
	RoleTypeException
};

/**
 * Using UserRole Exceptions 
 */
use Jsalam\UGRPM\Exceptions\UserRoleExceptions\{
	UserAlreadyHasRoleException
};

/**
 * Main class UGRPM 
 */
class UGRPM{
	/**
	 * Constructor
	 * 
	 * @param \PDO $connection
	 * @return void
	 */
	public function __construct(
		protected $connection,
	)
	{}
	/* Roles methods section */

	/**
	 * Method createRole
	 * 
	 * Accept Role object to insert it to database and return it with its primary id.
	 * 
	 * @param Role $role
	 * @throws DuplicatedRoleException
	 * @throws \PDOException
	 * @return Role
	 */
	public function createRole(Role $role):Role{
		
		if($this->getRoleByClassMethod($role->getRole())!=null){
			throw new DuplicatedRoleException("Duplicated Role ".$role->getRole());
		}else{
			try{
	            $this->connection->prepare(
	            	"INSERT INTO `roles` 
					(`role_class`,`role_method`) 
					VALUES 
					(:role_class,:role_method)")->execute(
	            	[
	            	'role_class' => $role->getRoleClass(),
	            	'role_method' => $role->getRoleMethod()
	            	]); 

	            $role->setId($this->connection->lastInsertId());
	            return $role;
	           
	        }catch(\PDOException $e){
	            throw new \PDOException("Create Role Error !");
	        }
	    }
	}

	/**
	 * Method removeRole remove the role from database
	 * 
	 * @param Role $role
	 * @throws \PDOException
	 * @return bool
	 */
	public function removeRole(Role $role):bool{
		try{
			$this->connection->prepare(
				"DELETE FROM `roles` 
				WHERE `id` = :role_id")->execute(
				[
				'role_id' => $role->getId()
				]);
			$this->connection->prepare(
				"DELETE FROM `user_roles` 
				WHERE `role_id` = :role_id")->execute(
				[
				'role_id' => $role->getId()
				]);
			$this->connection->prepare(
				"DELETE FROM `group_roles` 
				WHERE `role_id` = :role_id")->execute(
				[
				'role_id' => $role->getId()
				]);
			return true;
		   
		}catch(\PDOException $e){
			throw new \PDOException("Remove Role Error !");
		}
	}

	/**
	 * Method getAllRoles 
	 * 
	 * Retrieve All roles from database and return array of <Role> objects.
	 * 
	 * @throws \PDOException
	 * @return array <Role>
	 */
	public function getAllRoles():array{
		try{
            $stm = $this->connection->prepare("SELECT * FROM `roles`");
            $stm->execute();
            $result = $stm->fetchAll();
           
			$roles = [];
			foreach($result as $role){
				$roles[] = new Role(id:$role['id'],role:$role['role_class']."@".$role['role_method']);
			}
			return $roles;

        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}

	/**
	 * Method  getRoleById 
	 * 
	 * Retrieve role from database depending to its (integer) id
	 * 
	 * @param int $roleId
	 * @throws RoleNotFoundException
	 * @throws \PDOException
	 * @return Role
	 */
	public function getRoleById(int $roleId):Role{
		try{
            $stm = $this->connection->prepare("SELECT * FROM `roles` WHERE `id`=:role_id");
            $stm->execute(["role_id"=>$roleId]);
            $result = $stm->fetch();
            if(empty($result)){
            	throw new RoleNotFoundException("Role#$roleId Not Found !");
            }
            return new Role(id:$result['id'],role:$result['role_class']."@".$result['role_method']);

        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}

	/**
	 * Method getRolesByClass
	 * 
	 * @param string $roleClass
	 * @throws RoleNotFoundException
	 * @throws \PDOException
	 * @return array <Role>
	 */
	public function getRolesByClass(string $roleClass):array{
		try{
            $stm = $this->connection->prepare("SELECT * FROM `roles` WHERE `role_class`=:role_class");
            $stm->execute(["role_class"=>$roleClass]);
            $result = $stm->fetchAll();
            if(empty($result)){
            	throw new RoleNotFoundException("RoleClass $roleClass Not Found !");
            }
			$roles = [];
			foreach ($result as $roleItem) {
				$roles[] = new Role(id: $result['id'], role: $result['role_class'] . "@" . $result['role_method']);
			}
			return $roles;
        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}

	/**
	 * Method getRolesByMethod
	 * 
	 * @param string $roleMethod
	 * @throws RoleNotFoundException
	 * @throws \PDOException
	 * @return array <Role>
	 */
	public function getRolesByMethod(string $roleMethod):array{
		try{
            $stm = $this->connection->prepare("SELECT * FROM `roles` WHERE `role_method`=:role_method");
            $stm->execute(["role_method"=>$roleMethod]);
            $result = $stm->fetchAll();
            if(empty($result)){
            	throw new RoleNotFoundException("RoleClass $roleMethod Not Found !");
            }
			$roles = [];
			foreach ($result as $roleItem) {
				$roles[] = new Role(id: $result['id'], role: $result['role_method'] . "@" . $result['role_method']);
			}
			return $roles;
        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}
	
	/**
	 * Method getRoleByClassMethod
	 * 
	 * Accept parameter in form "class"@"method"
	 * 
	 * Return the retrieved role as Role object or false if not founded.
	 * 
	 * @param string $roleClassMethod
	 * @throws \PDOException
	 * @return mixed Role|bool
	 */
	public function getRoleByClassMethod(string $roleClassMethod):mixed{
		$roleParts = explode("@",$roleClassMethod);
		try{
            $stm = $this->connection->prepare("SELECT * FROM `roles` WHERE `role_class`=:role_class AND `role_method`=:role_method");
            $stm->execute(
				[
					"role_class"=>$roleParts[0],
					"role_method"=>$roleParts[1],
				]);
            $result = $stm->fetch();
            if(empty($result)){
				return false;
            }
            return new Role(id:$result['id'],role:$result['role_class']."@".$result['role_method']);

        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}


	/* Role section end */

	/* Group section start*/

	/**
	 * Method createGroup
	 * 
	 * Accept Group object parameter then insert it to database and return it with primary id.
	 * 
	 * @param Group $group
	 * @throws DuplicatedGroupException
	 * @throws \PDOException
	 * @return Group
	 */
	public function createGroup(Group $group):Group{
		try{
			$this->getGroupByGroupName($group->getGroupName());
			throw new DuplicatedGroupException("Duplicated Group Name ".$group->getGroupName());
		}catch(GroupNotFoundException $e){
			try{
	            $this->connection->prepare(
	            	"INSERT INTO `groups` 
					(`group_name`,`description`) 
					VALUES 
					(:group,:description)")->execute(
	            	[
	            	'group' => $group->getGroupName(),
	            	'description' => $group->getDescription()
	            	]); 

	            $group->setId($this->connection->lastInsertId());
	            return $group;
	           
	        }catch(\PDOException $e){
	            throw new \PDOException("Create Group Error !");
	        }
		}
	}

	/**
	 * Method updateGroup
	 * 
	 * Update the given group object in database and return the updated object.
	 * 
	 * @param Group $group
	 * @throws \PDOException
	 * @return Group
	 */

	public function updateGroup(Group $group):Group{
		try{
			$this->connection->prepare(
				"UPDATE `groups` 
				set `group_name`=:group_name, `description`=:description WHERE `id` = :group_id")->execute(
				[
				'group_id' => $group->getId(),
				'group_name' => $group->getGroupName(),
				'description' => $group->getDescription()
				]);
			return $group;
		   
		}catch(\PDOException $e){
			throw new \PDOException("Update Group Error !");
		}
	}

	/**
	 * Method removeGroup
	 * 
	 * Remove the group from database,remove all users in that group and remove roles belongs to that group.
	 *
	 * @param Group $group
	 * @throws \PDOException
	 * @return bool
	 */
	public function removeGroup(Group $group):bool{
		try{
			$this->connection->prepare(
				"DELETE FROM `groups` 
				WHERE `id` = :group_id")->execute(
				[
				'group_id' => $group->getId()
				]);
			$this->connection->prepare(
				"DELETE FROM `user_group` 
				WHERE `group_id` = :group_id")->execute(
				[
				'group_id' => $group->getId()
				]);
			$this->connection->prepare(
				"DELETE FROM `group_roles` 
				WHERE `group_id` = :group_id")->execute(
				[
				'group_id' => $group->getId()
				]); 
			return true;
		   
		}catch(\PDOException $e){
			throw new \PDOException("Remove Group Error !");
		}
	}

	/**
	 * Method getAllGroups
	 * 
	 * @throws \PDOException
	 * @return array <Group>
	 */
	public function getAllGroups():array{
		try{
            $stm = $this->connection->prepare("SELECT * FROM `groups`");
            $stm->execute();
            $result = $stm->fetchAll();
           
			$groups = [];
			foreach($result as $group){
				$groups[] = new Group(id: $group['id'], groupName: $group['group_name'], description: $group['description']);
			}
			return $groups;

        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}

	/**
	 * Method getGroupById
	 * 
	 * @param int $groupId
	 * @throws GroupNotFoundException
	 * @throws \PDOException
	 * @return Group
	 */
	public function getGroupById(int $groupId):Group{
		try{
            $stm = $this->connection->prepare("SELECT * FROM `groups` WHERE `id`=:group_id");
            $stm->execute(["group_id"=>$groupId]);
            $result = $stm->fetch();
            if(empty($result)){
            	throw new GroupNotFoundException("Group #$groupId Not Found !");
            }
            return new Group(id:$result['id'],groupName:$result['group_name'],description:$result['description']);

        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}

	/**
	 * Method getGroupByGroupName
	 * 
	 * @param string $group
	 * @throws GroupNotFoundException
	 * @throws \PDOException
	 * @return Group
	 */
	public function getGroupByGroupName(string $group):Group{
		try{
            $stm = $this->connection->prepare("SELECT * FROM `groups` WHERE `group_name`=:group");
            $stm->execute(["group"=>$group]);
            $result = $stm->fetch();
            if(empty($result)){
				throw new GroupNotFoundException("Group ($group) Not Found !");
            }
            return new Group(id:$result['id'],groupName:$result['group_name'],description:$result['description']);

        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}

	/* Group section end */

	/* GroupRole section start */

	/**
	 * Method verifyRoleGroupArray (private)
	 * 
	 * Accept array of mixed values and type parameter
	 * Check each array elements type then make a new array of type of $type parameter (Role|Group) and return it.
	 * This give the ability to pass mixed array of (Role|int) OR (Group|int)
	 *  
	 * @param array $grs
	 * @param string $type
	 * @throws RoleTypeException
	 * @throws GroupTypeException
	 * @return array <Role|Group>
	 */
	private function verifyRoleGroupArray(array $grs,string $type):array{
		$returnArray = [];
		foreach($grs as $gr){
			if (gettype($gr) == "integer") {
				$returnArray[] = ($type=="Role")?$this->getRoleById($gr):$this->getGroupById($gr);
			}elseif(gettype($gr)=="object" && get_class($gr)=="Jsalam\UGRPM\\".$type){
				$returnArray[] = $gr;
			}else{
				if($type=="Role"){
					throw new RoleTypeException("Roles must be an array of Roles (Jsalam\UGRPM\Role) OR integers (Roles IDs).");

				}elseif($type=="Group"){
					throw new GroupTypeException("Groups must be an array of Groups(Jsalam\UGRPM\Group) OR integers (Groups IDs).");
				}
			}
		}
		
		return $returnArray;
	}

	/**
	 * Method createGroupRole
	 * 
	 * Give a group the role passed.
	 * 
	 * @param Group $group
	 * @param Role $role
	 * @throws GroupAlreadyHasRoleException
	 * @throws \PDOException
	 * @return bool
	 */
	public function createGroupRole(Group $group,Role $role):bool{
		if($this->groupHaveRole($group,$role)){
			throw new GroupAlreadyHasRoleException("Group ".$group->getGroupName()." already has role: ".$role->getRole());
		}else{
			try{
	            $this->connection->prepare(
	            	"INSERT INTO `group_roles` 
					(`group_id`,`role_id`) 
					VALUES 
					(:group_id,:role_id)")->execute(
	            	[
	            	'group_id' => $group->getId(),
	            	'role_id' => $role->getId()
	            	]); 

	            return true;
	           
	        }catch(\PDOException $e){
	            throw new \PDOException("Create Group Role Error !");
	        }
	    }
	}

	/**
	 * Method createGroupRoles
	 * 
	 * Give a group many roles
	 * 
	 * @param Group $group
	 * @param array $roles <Role|int>
	 * @return bool
	 */
	public function createGroupRoles(Group $group, array $roles):bool{
		$groupRoles = $this->verifyRoleGroupArray($roles, "Role");
		foreach($groupRoles as $role){
			$this->createGroupRole($group, $role);
		}
		return true;
	}

	/**
	 * Method createRoleGroups
	 * 
	 * Add role to  many groups
	 * 
	 * @param Role $role
	 * @param array $groups <Group|int>
	 * @return bool
	 */
	public function createRoleGroups(Role $role,array $groups):bool{
		$roleGroups = $this->verifyRoleGroupArray($groups, "Group");
		foreach($roleGroups as $group){
			$this->createGroupRole($group, $role);
		}
		return true;
	}

	/**
	 * Method removeGroupRole
	 * 
	 * Remove the role from group
	 * 
	 * @param Group $group
	 * @param Role $role
	 * @throws \PDOException
	 * @return bool
	 */
	public function removeGroupRole(Group $group,Role $role):bool{
		try{
			$this->connection->prepare(
				"DELETE FROM `group_roles` 
				WHERE `group_id` = :group_id AND `role_id`=:role_id")->execute(
				[
				'group_id' => $group->getId(),
				'role_id' => $role->getId()
				]); 
			return true;
		   
		}catch(\PDOException $e){
			throw new \PDOException("Remove Group Role Error !");
		}
	}

	/**
	 * Method removeGroupRoles
	 * 
	 * Remove roles from the given group.
	 * 
	 * @param Group $group
	 * @param array $roles <Role|int>
	 * @return bool
	 */

	public function removeGroupRoles(Group $group,array $roles):bool{
		$groupRoles = $this->verifyRoleGroupArray($roles, "Role");
		foreach($groupRoles as $role){
			$this->removeGroupRole($group, $role);
		}
		return true;
	}

	/**
	 * Method removeRoleGroups
	 * 
	 * Remove the role from all given groups.
	 * 
	 * @param Role $role
	 * @param array $groups <Group|int>
	 * @return bool
	 */
	public function removeRoleGroups(Role $role,array $groups):bool{
		$roleGroups = $this->verifyRoleGroupArray($groups, "Group");
		foreach($roleGroups as $group){
			$this->removeGroupRole($group, $role);
		}
		return true;
	}

	/**
	 * Method groupHaveRole
	 * 
	 * Check if the group have the role.
	 * 
	 * @param Group $group
	 * @param Role $role
	 * @throws \PDOException
	 * @return bool
	 */
	public function groupHaveRole(Group $group,Role $role):bool{
		try{
            $stm = $this->connection->prepare("SELECT * FROM `group_roles` 
			WHERE `group_id`=:group_id AND `role_id`=:role_id");
            $stm->execute(
				[
					"group_id"=>$group->getId(),
					"role_id"=>$role->getId(),
				]);
            $result = $stm->fetch();
            return !empty($result);


        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}

	/**
	 * Method getGroupRoles
	 * 
	 * Retrieve all group's roles.
	 * 
	 * @param Group $group
	 * @throws \PDOException
	 * @return array <Role>
	 */
	public function getGroupRoles(Group $group):array{
		try{
            $stm = $this->connection->prepare("SELECT role_id,role_class,role_method  FROM `group_roles`,`roles` 
			WHERE `group_id`=:group_id AND roles.id=group_roles.role_id");
            $stm->execute(["group_id"=>$group->getId()]);
            $result = $stm->fetchAll();
            if(empty($result)){
				return [];
            }else{
				$grouoRoles = [];
				foreach($result as $role){
					$grouoRoles[]= new Role(id:$role['role_id'],role:$role['role_class']."@".$role['role_method']);
				}
				return $grouoRoles;
			}

        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}

	/**
	 * Method getRoleGroups
	 * @param Role $role
	 * @throws \PDOException
	 * @return array <Group>
	 */

	 public function getRoleGroups(Role $role):array{
		try{
            $stm = $this->connection->prepare("SELECT group_id,group_name,description FROM `group_roles`,`groups`
			WHERE `role_id`=:role_id AND groups.id=group_roles.group_id");
            $stm->execute(
				[
					"role_id"=>$role->getId()
				]);
            $result = $stm->fetchAll();
			$groups = [];
			foreach($result as $group){
				$groups[ ]= new Group(id:$group['group_id'],groupName:$group['group_name'],description:$group['description']);
			}
			return $groups;

        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}
	/* GroupRole  section end */

	/* UserRole section start */

	/**
	 * Method createUserRole
	 * 
	 * @param int $userId
	 * @param Role $role
	 * @throws UserAlreadyHasRoleException
	 * @throws \PDOException
	 * @return bool
	 */
	public function createUserRole(int $userId,Role $role):bool{
		if($this->userHaveRole($userId,$role)){
			throw new UserAlreadyHasRoleException("User ID#".$userId." already has role: ".$role->getRole());
		}else{
			try{
	            $this->connection->prepare(
	            	"INSERT INTO `user_roles` 
					(`user_id`,`role_id`) 
					VALUES 
					(:user_id,:role_id)")->execute(
	            	[
	            	'user_id' => $userId,
	            	'role_id' => $role->getId()
	            	]); 

	            return true;
	           
	        }catch(\PDOException $e){
	            throw new \PDOException("Create User Role Error !");
	        }
	    }
	}

	/**
	 * Method createUserRoles
	 * 
	 * Add many roles to the user
	 * 
	 * @param int $userId
	 * @param array $roles <Role|int>
	 * @return bool
	 */
	public function createUserRoles(int $userId,array $roles):bool{
		$roleUsers = $this->verifyRoleGroupArray($roles, "Role");
		foreach($roleUsers as $role){
			$this->createUserRole($userId, $role);
		}
		return true;
	}

	/**
	 * Method createRoleUsers
	 * 
	 * add role to many users
	 * 
	 * @param Role $role
	 * @param array $users <int>
	 * @return bool
	 */
	public function createRoleUsers(Role $role,array $users):bool{
		foreach($users as $userId){
			$this->createUserRole($userId, $role);
		}
		return true;
	}

	/**
	 * Methodof removeUserRole
	 * 
	 * @param int $userId
	 * @param Role $role
	 * @throws \PDOException
	 * @return bool
	 */

	public function removeUserRole(int $userId,Role $role):bool{
		try{
			$this->connection->prepare(
				"DELETE FROM `user_roles` 
				WHERE `user_id` = :userId_id AND `role_id`=:role_id")->execute(
				[
				'userId_id' => $userId,
				'role_id' => $role->getId()
				]); 
			return true;
		   
		}catch(\PDOException $e){
			throw new \PDOException("Remove User Role Error !");
		}
	}

	/**
	 * Method removeUserRoles
	 * 
	 * @param int $userId
	 * @param array $roles <Role|int>
	 * @return bool
	 */
	public function removeUserRoles(int $userId,array $roles):bool{
		$userRoles = $this->verifyRoleGroupArray($roles, "Role");
		foreach($userRoles as $role){
			$this->removeUserRole($userId, $role);
		}
		return true;
	}

	/**
	 * Method removeRoleUsers
	 * 
	 * Remove many roles from user roles.
	 * 
	 * @param Role $role
	 * @param array $users
	 * @return bool
	 */
	public function removeRoleUsers(Role $role,array $users):bool{
		foreach($users as $userId){
			$this->removeUserRole($userId, $role);
		}
		return true;
	}

	/**
	 * Method userHaveRole
	 * 
	 * Check if user has role.
	 * 
	 * @param int $userId
	 * @param Role $role
	 * @return bool
	 */
	public function userHaveRole(int $userId,Role $role):bool{
		$userAllRoles = $this->getAllUserRoles($userId);
		foreach ($userAllRoles as $uar) {
			foreach ($uar as $r) {
				if($r->getId()==$role->getId()){
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Method getUserRoles
	 * 
	 * Retrieve all user roles, returned roles array don't contains user groups roles.
	 * 
	 * @param int $userId
	 * @throws \PDOException
	 * @return array <Role>
	 */
	public  function getUserRoles(int $userId):array{
		try{
            $stm = $this->connection->prepare("SELECT role_id,role_class,role_method FROM `user_roles`,`roles` 
			WHERE `user_id`=:user_id AND roles.id=user_roles.role_id");
            $stm->execute(
				[
					"user_id"=>$userId
				]);
            $result = $stm->fetchAll();
			$userRoles = [];
            foreach($result as $role){
				$userRoles[] = new Role(id:$role['role_id'],role:$role['role_class']."@".$role['role_method']);
			}

			return $userRoles;

        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}

	/**
	 * Method getAllUserRoles
	 * 
	 * Retrrive all user roles and his groups roles.
	 * 
	 * @param int $userId
	 * @return array <Role>
	 */
	public function getAllUserRoles(int $userId):array{
		$userGroups = $this->getUserGroups($userId);
		$userRoles = $this->getUserRoles($userId);
		$roles = [];
		$roles['self']=$userRoles;
		foreach($userGroups as $ug){
			$roles[$ug->getGroupName()] = $this->getGroupRoles($ug);
		}
		return $roles;
	}

	/**
	 * Method getRoleUsers
	 * @param Role $role
	 * @throws \PDOException
	 * @return array <int>
	 */
	public function getRoleUsers(Role $role):array{
		try{
            $stm = $this->connection->prepare("SELECT user_id as id FROM `user_roles`
			WHERE `role_id`=:role_id");
            $stm->execute(
				[
					"role_id"=>$role->getId()
				]);
			$result  =  $stm->fetchAll();
			$users = [];
			foreach($result as $user){
				array_push($users, $user['id']);
			}
			return $users;
        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}

	

	/* UserRole section end */

	/* UserGroup section start */

	/**
	 * Method addUserToGroup
	 * 
	 * Add the user to the group.
	 * 
	 * @param int $userId
	 * @param Group $group
	 * @throws UserAlreadyInGroupException
	 * @throws \PDOException
	 * @return bool
	 */
	public function addUserToGroup(int $userId,Group $group):bool{
		if($this->userInGroup($userId,$group)){
			throw new UserAlreadyInGroupException("User ID#".$userId." is already in group : ".$group->getGroupName());
		}else{
			try{
	            $this->connection->prepare(
	            	"INSERT INTO `user_group` 
					(`user_id`,`group_id`) 
					VALUES 
					(:user_id,:group_id)")->execute(
	            	[
	            	'user_id' => $userId,
	            	'group_id' => $group->getId()
	            	]); 

	            return true;
	           
	        }catch(\PDOException $e){
	            throw new \PDOException("Create User Role Error !");
	        }
	    }
	}

	/**
	 * Method addUsersToGroups
	 * 
	 * Add user to many groups.
	 * 
	 * @param int $userId
	 * @param array $groups <Group|int>
	 * @return bool
	 */
	public function addUserToGroups(int $userId,array $groups):bool{
		$userGroups = $this->verifyRoleGroupArray($groups, "Group");
		foreach($userGroups as $group){
			$this->addUserToGroup($userId, $group);
		}
		return true;
	}

	/**
	 * Method addUsersToGroups
	 * 
	 * Add many users to group.
	 * 
	 * @param array $users <int>
	 * @param Group $group
	 * @return bool
	 */
	public function addUsersToGroup(array $users,Group $group):bool{
		foreach($users as $userId){
			$this->addUserToGroup($userId, $group);
		}
		return true;
	}

	/**
	 * Method addUsersToGroups
	 * 
	 * AddMany users to many groups.
	 * 
	 * @param array $users <int>
	 * @param array $groups <Group|int>
	 * @return bool
	 */
	public function addUsersToGroups(array $users,array $groups):bool{
		foreach($users as $userId){
			$this->addUserToGroups($userId, $groups);
		}
		return true;
	}

	/**
	 * Method removeUserFromGroup
	 * 
	 * @param int $userId
	 * @param mixed $group
	 * @throws \PDOException
	 * @return bool
	 */
	public function removeUserFromGroup(int $userId,$group):bool{
		try{
			$this->connection->prepare(
				"DELETE FROM `user_group` 
				WHERE  `user_id`=:user_id AND `group_id`=:group_id")->execute(
				[
					'user_id' => $userId,
					'group_id' => $group->getId()
				]); 
			return true;
		   
		}catch(\PDOException $e){
			throw new \PDOException("Remove Group Role Error !");
		}
	}

	/**
	 * Method removeUserFromGroups
	 * 
	 * Remove user from many groups.
	 * 
	 * @param int $userId
	 * @param array $groups
	 * @return bool
	 */
	public function removeUserFromGroups(int $userId,array $groups):bool{
		$userGroups = $this->verifyRoleGroupArray($groups, "Group");
		foreach($userGroups as $group){
			$this->removeUserfromGroup($userId, $group);
		}
		return true;
	}

	/**
	 * Method removeUsersfromGroup
	 * 
	 * Remove many users from a group.
	 * 
	 * @param array $users <int>
	 * @param Group $group
	 * @return bool
	 */
	public function removeUsersfromGroup(array $users,Group $group):bool{
		foreach($users as $userId){
			$this->removeUserFromGroup($userId, $group);
		}
		return true;
	}
	
	/**
	 * Method of getUserGroups
	 * 
	 * Retrieve user's groups.
	 * 
	 * @param int $userId
	 * @throws \PDOException
	 * @return array <Group>
	 */
	public function getUserGroups(int $userId):array{
		try{
            $stm = $this->connection->prepare("SELECT group_id,group_name,description FROM `user_group`, `groups`
			WHERE `user_id`=:user_id AND groups.id=user_group.group_id");
            $stm->execute(
				[
					"user_id"=>$userId
				]);
            $result = $stm->fetchAll();
           
			$groups = [];
			foreach($result as $group){
				$groups[] = new Group(id: $group['group_id'], groupName: $group['group_name'], description: $group['description']);
			}
			return $groups;

        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}

	/**
	 * Method getGroupUsers
	 * 
	 * Retrieve users in the group.
	 * 
	 * @param Group $group
	 * @throws \PDOException
	 * @return mixed
	 */
	public function getGroupUsers(Group $group):mixed{
		try{
            $stm = $this->connection->prepare("SELECT user_id as id FROM `user_group`, `groups`
			WHERE `group_id`=:group_id AND groups.id=user_group.group_id");
            $stm->execute(
				[
					"group_id"=>$group->getId()
				]);
            $result =  $stm->fetchAll();
			$users = [];
			foreach($result as $user){
				array_push($users, $user['id']);
			}
			return $users;
        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}

	/**
	 * Method userInGroup
	 * 
	 * Check if user in group.
	 * 
	 * @param int $userId
	 * @param Group $group
	 * @throws \PDOException
	 * @return bool
	 */
	public function userInGroup(int $userId,Group $group):bool{
		try{
            $stm = $this->connection->prepare("SELECT * FROM `user_group` 
			WHERE `user_id`=:user_id AND `group_id`=:group_id");
            $stm->execute(
				[
					"user_id"=>$userId,
					"group_id"=>$group->getId(),
				]);
            $result = $stm->fetch();
            return !empty($result);


        }catch(\PDOException $e){
            throw new \PDOException("Query Error !".$e->getMessage());
        }
	}
	
	/* UserGroup section end */
	
} 