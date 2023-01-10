<?php
/**
 * Role Class
 * 
 * Define Role class which describe role as ClassNamespace@Method
 * 
 * @author      Salam Aljehni <salamj@gmail.com>
 * @copyright   Copyright (c), 2023 Salam Aljehni
 * @license     MIT public license
 * @package     UGRPM
 */
namespace Jsalam\UGRPM;

use Jsalam\UGRPM\Exceptions\RoleExceptions\{
	RoleNameException,
	InvalidRoleClassNameException
};

class Role{
	/** 
	 * @var string  contains the class part of role name 
	 */
	private string $roleClass;

	/** 
	 * @var string  contains the method part of role name  
	 */
	private string $roleMethod;

	/**
	 * Constructor __construct
	 * 
	 * Convert full role name "Class@Method" to two parts
	 * then assign class part to $roleClass and method part to $roleMethod
	 * @param int $id
	 * @param string $role contains role name in the form "classNameSpace"@"method"
	 * @return void
	 * @throws RoleNameException
	 * @throws InvalidRoleClassNameException
	 */
	public function __construct(
		public int $id = 0,
		public string $role = ""
	){
		/** 
		 * @var array <string> $roleParts   
		 */
		$roleParts = explode("@",$this->role);
		/** 
		 * check if $roleParts contain 2 elements and throw RoleNameException if not.
		 */
		if(count($roleParts)!=2){
			throw new RoleNameException("Role name must contains full class namespace then '@' then method name");
		}
		/** 
		 * Check if class name is valid then throw  InvalidRoleClassNameException if not 
		*/
		if(preg_match("@^(?:\\\{1,2}\\w+|\\w+\\\{1,2})(?:\\w+\\\{0,2}\\w+)+$@",$roleParts[0])!==1){
			throw new InvalidRoleClassNameException("Invalid Role Class Name");
		}
		$this->roleClass = $roleParts[0];
		$this->roleMethod = $roleParts[1]; 
	}

	/**
	 * Method getId
	 * Return role id
	 * 
	 * @return int
	 */
	public function getId():int{
		return $this->id;
	}

	/**
	 * Method setId
	 * 
	 * set role id
	 * 
	 * @param int $id
	 * @return void
	 */
	public function setId(int $id):void{
		$this->id=$id;
	}

	/**
	 * Method getRole
	 * 
	 * Get Role name like Class@Method
	 * 
	 * @return string
	 */
	public function getRole():string{
		return $this->role;
	}
	/**
	 * Method setRole
	 * 
	 * Set role as Class@Method
	 * 
	 * @param string $role
	 * @return void
	 */
	public function setRole(string $role):void{
		$this->role=$role;
	}

	/**
	 * Method getRoleClass
	 * 
	 * Get class part from role
	 * 
	 * @return string
	 */
	public function getRoleClass():string{
		return $this->roleClass;
	}

	/**
	 * Method getRoleMethod
	 * 
	 * Get method  part from role
	 * 
	 * @return string
	 */
	public function getRoleMethod():string{
		return $this->roleMethod;
	}

	/**
	 * Method setRoleClass
	 * 
	 * Set class part for role
	 * 
	 * @return void
	 */
	public function setRoleClass(string $roleClass):void{
		$this->roleClass = $roleClass;
	}
	
	/**
	 * Method setRoleMethod
	 * 
	 * Set method part for role
	 * 
	 * @return void
	 */
	public function setRoleMethod(string $roleMethod):void{
		$this->roleMethod = $roleMethod;
	}


}