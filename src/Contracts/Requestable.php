<?php

namespace DreamHack\SDK\Contracts;

interface Requestable {
	/**
	 * Formatting rules for responses of this Model
	 * @return array
	 */
	public static function getFields();

	/**
	 * Fields required for creating a new instance of this Model.
	 * @return array
	 */
	public static function getRequiredFields();

	/**
	 * Validation rules for input fields.
	 * @return array
	 */
	public static function getFieldValidators();

	/**
	 * Exposed relations
	 */
	public static function getDefaultRelations();
}