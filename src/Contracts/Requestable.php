<?php

namespace DreamHack\SDK\Contracts;

interface Requestable {
	/**
	 * Formatting rules for responses of this Model
	 * @return array
	 */
	public static function getFields();

	/**
	 * @return array An array of field keys that must be filled in for the model to be created.
	 */
	public static function getRequiredFields();

	/**
	 * @return array An array of validation rules that goes into a Illuminate Validator to sanitize input when the model is created or updated.
	 */
	public static function getFieldValidators();

	/**
	 * @return  array An array of the relation keys to fetch by default when the model is viewed.
	 */
	public static function getDefaultRelations();

	/**
	 * @return string The field to use as a key when viewing collections of the model.
	 */
    public static function getKeyByField();

    /**
     * @return string A field to group up collections of the model for viewing.
     * @return boolean Return false to turn off grouping.
     */
    public static function getGroupByField();
}