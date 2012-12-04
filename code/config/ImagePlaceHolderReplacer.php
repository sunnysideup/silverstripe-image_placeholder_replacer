<?php

/**
 *@author nicolaas [at] sunnysideup.co.nz + toro[at] sunnysideup.co.nz
 *
 *
 **/

class ImagePlaceHolderReplacer extends DataObjectDecorator {

	//Notes e.g. "this image is shown on the homepage under products" - optional
	//CopyFromPath  -e .g. themes/mytheme/images/myImage.gif - optional
	protected static $images_to_replace = array();
		public static function get_images_to_replace() {return self::$images_to_replace;}
		public static function remove_image_to_replace($className, $fieldName) {unset(self::$images_to_replace[$className.'_'.$fieldName]);}
		public static function add_image_to_replace($className, $fieldName, $notes, $copyFromPath) {
			$key = $className.'_'.$fieldName;
			self::$images_to_replace[$key] = array(
				"ClassName" => $className,
				"FieldName" => $fieldName,
				"Notes" => $notes,
				"CopyFromPath" => $copyFromPath,
				"DBFieldName" => $key
			);
		}
	protected static $folder_name = "SampleImages";
		public static function get_folder_name() {return self::$folder_name;}
		public static function set_folder_name($v) {self::$folder_name = $v;}


	public function extraStatics(){
		/*
		DOES NOT WORK BECAUSE extraStatics runs before the settings are set...
		$hasOneArray = array();
		$fullArray = self::get_images_to_replace();
		if($fullArray) {
			foreach($fullArray as $key => $array) {
				$hasOneArray[$key] = "Image";
			}

			return array(
				'has_one' => $hasOneArray,
				"db" => array("TESTME")
			);
		}
		return array();
		*/
	}

	function updateCMSFields(FieldSet &$fields) {
		/*
		$fullArray = self::get_images_to_replace();
		if($fullArray) {
			$folder = Folder::findOrMake(self::get_folder_name());
			if(!$folder) {
				$fields->addFieldToTab('Root.Main', new LiteralField('folderError'.self::get_folder_name(), '<p>Can not create folder for place holder images(/assets/'.self::get_folder_name().'), please contact your administrator.</p>'));
			}
			foreach($fullArray as $key => $array) {
				$fields->addFieldToTab('Root.Main', new ImageField($key."ID", "Place holder for $key ... ".$array["Notes"], $value = null, $form = null, $rightTitle = null, $folder->Name));
			}
		}
		*/
		return $fields;
	}
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		$bt = defined('DB::USE_ANSI_SQL') ? '"' : '`';

		$update = array();
		$siteConfig = DataObject::get_one('SiteConfig');
		$folder = Folder::findOrMake(self::get_folder_name());
		if($siteConfig && $folder) {
			$fullArray = self::get_images_to_replace();
			//copying ....
			if($fullArray) {
				foreach($fullArray as $key => $array) {
					$className = $array["ClassName"];
					$fieldName = $array["FieldName"]."ID";
					if(class_exists($className)) {
						$dataObject = singleton($className);
						$dbFieldName = $array["DBFieldName"];
						$fileName = basename($array["CopyFromPath"]);
						$fromLocationLong = Director::baseFolder().'/'.$array["CopyFromPath"];
						$toLocationShort = "assets/".self::get_folder_name()."/{$fileName}";
						$toLocationLong = Director::baseFolder().'/'.$toLocationShort;
						$image = DataObject::get_one('Image', "Filename='$toLocationShort' AND ParentID = ".$folder->ID);
						if(!$image){
							if(!file_exists($toLocationLong)) {
								copy($fromLocationLong, $toLocationLong);
							}
							$image = new Image();
							$image->ParentID = $folder->ID;
							$image->FileName = $toLocationShort;
							$image->setName($fileName);
							$image->write();
						}
						elseif(!$image && file_exists($toLocationLong)) {
							debug::show("need to update files");
						}
						if($image && $image->ID) {
							if(!$siteConfig->$dbFieldName) {
								$siteConfig->$dbFieldName = $image->ID;
								$update[]= "created placeholder image for $key";
							}
							$updateSQL = " UPDATE {$bt}".$className."{$bt}";
							if(isset($_GET["removeplaceholderimages"])) {
								$setSQL = " SET {$bt}".$fieldName."{$bt} = 0";
								$whereSQL = " WHERE {$bt}".$fieldName."{$bt}  = ".$image->ID;
								DB::alteration_message("removing ".$className.".".$fieldName." placeholder images", 'deleted');
							}
							else{
								DB::alteration_message("adding ".$className.".".$fieldName." placeholder images", 'created');
								$setSQL = " SET {$bt}".$fieldName."{$bt} = ".$image->ID;
								if(!isset($_GET["forceplaceholder"])) {
									$whereSQL = " WHERE {$bt}".$fieldName."{$bt} IS NULL OR {$bt}".$fieldName."{$bt} = 0";
								}
								else {
									$whereSQL = '';
								}
							}
							$sql = $updateSQL.$setSQL.$whereSQL;
							DB::query($sql);
							$versioningPresent = false;
							$array = $dataObject->stat('extensions');
							if(is_array($array) && count($array)) {
								if(in_array("Versioned('Stage', 'Live')", $array)) {
									$versioningPresent = true;
								}
							}
							if($dataObject->stat('versioning')) {
								$versioningPresent = true;
							}
							if($versioningPresent) {
								$sql = str_replace("{$bt}$className{$bt}", "{$bt}{$className}_Live{$bt}", $sql);
								DB::query($sql);
							}
						}
						else {
							debug::show("could not create image!".print_r($array));
						}
					}
					else {
						debug::show("bad classname reference ".$className);
					}
				}
			}
			if(count($update)) {
				$siteConfig->write();
				DB::alteration_message($siteConfig->ClassName." created/updated: ".implode(" --- ",$update), 'created');
			}
		}
		elseif(!$folder) {
			debug::show("COULD NOT CREATE FOLDER: ".self::get_folder_name());
		}
	}
}
