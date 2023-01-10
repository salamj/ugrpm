<?php
/**
 * Group Class
 * 
 * @author      Salam Aljehni <salamj@gmail.com>
 * @copyright   Copyright (c), 2023 Salam Aljehni
 * @license     MIT public license
 * @package     UGRPM
 */
namespace Jsalam\UGRPM;

class Group{
	/**
	 * Constructor __construct
	 * 
	 * @param int $id
	 * @param string $groupName
	 * @param string $description
	 */
	public function __construct(
		private int $id=0,
		private string $groupName="",
		private string $description=""
	){}

	/**
	 * Method of getId
	 * @return int
	 */
	public function getId():int{
		return $this->id;
	}

	/**
	 * Method setId
	 * @param int $id
	 * @return void
	 */
	public function setId(int $id):void{
		$this->id=$id;
	}

	/**
	 * Method getGroupName
	 * @return string
	 */
	public function getGroupName():string{
		return $this->groupName;
	}

	/**
	 * Method setGroupName
	 * @param string $group
	 * @return void
	 */
	public function setGroupName(string $group):void{
		$this->groupName=$group;
	}

	/**
	 * Method getDescription
	 * @return string
	 */
	public function getDescription():string{
		return $this->description;
	}

	/**
	 * Method setDescription
	 * @param string $description
	 * @return void
	 */
	public function setDescription(string $description):void{
		$this->description=$description;
	}

}