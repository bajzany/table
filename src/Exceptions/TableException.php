<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 02.01.2019
 */

namespace Bajzany\Table\Exceptions;

class TableException extends \Exception
{

	/**
	 * @return TableException
	 */
	public static function notAttached()
	{
		return new self("Item is not attached to Table");
	}

	/**
	 * @param $className
	 * @return TableException
	 */
	public static function notBuild($className)
	{
		return new self("'{$className}' is not build");
	}

	/**
	 * @param $className
	 * @return TableException
	 */
	public static function paginatorIsNotSet($className)
	{
		return new self("Paginator is not set in Table '{$className}'");
	}

	public static function doesNotExistField($field, $className)
	{
		return new self("Field {$field} doesn't exist in {$className}");
	}
}
