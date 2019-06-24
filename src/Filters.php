<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Bajzany\Table;

use Bajzany\Table\EntityTable\Column;

use Nette\Localization\ITranslator;
use Nette\Utils\Html;
use Nette\Utils\Strings;

class Filters
{

	/**
	 * @var Table
	 */
	private $table;

	/**
	 * @param Table $table
	 */
	public function __construct(Table $table)
	{
		$this->table = $table;
	}

	/**
	 * @param string $message
	 * @param null $count
	 * @return mixed
	 */
	public function translate($message, $count = NULL)
	{
		$translator = $this->table->getTranslator();
		if ($translator instanceof ITranslator) {
			return call_user_func_array([$translator, "translate"], func_get_args());
		}
		return $message;
	}

	/**
	 * @param \DateTime $value
	 * @param Column $column
	 * @return mixed
	 */
	public function date($value, Column $column)
	{
		if ($value instanceof \DateTime) {
			return $value->format("d.m.Y");
		}
		return $value;
	}

	/**
	 * @param \DateTime $value
	 * @param Column $column
	 * @return mixed
	 */
	public function dateTime($value, Column $column)
	{
		if ($value instanceof \DateTime) {
			return $value->format("d.m.Y H:i");
		}
		return $value;
	}

	/**
	 * @param mixed$value
	 * @param Column $column
	 * @param null|string|int $width
	 * @param null|string|int $height
	 * @return Html|mixed
	 */
	public function base64($value, Column $column, $width = NULL, $height = NULL)
	{
		if (is_string($value)) {
			$img = Html::el("img", [
				"src" => "data:image/png;base64," . $value,
			]);

			$style = "";
			if ($width) {
				$width = is_int($width) ? $width . "px" : $width;
				$style .= "width: {$width};";
			}
			if ($height) {
				$height = is_int($height) ? $height . "px" : $height;
				$style .= "width: {$height};";
			}

			if (!empty($style)) {
				$img->setAttribute("style", $style);
			}

			return $img;
		}
		return $value;
	}

	/**
	 * @param mixed $value
	 * @param Column $column
	 * @param string $success
	 * @param string $failure
	 * @return Html
	 * @throws \Exception
	 */
	public function boolLabel($value, Column $column, string $success = "label-success", string $failure = "label-warning")
	{
		if ($value) {
			return Html::el("span", [
				"class" => "label " . $success,
			])->setHtml($this->translate("yes", ["Ano"]));
		}
		return Html::el("span", [
			"class" => "label " . $failure,
		])->setHtml($this->translate("not", ["Ne"]));
	}

	/**
	 * @param string $value
	 * @param Column $column
	 * @param string $class
	 * @param null $translateFile
	 * @return Html|null
	 * @throws \Exception
	 */
	public function label($value, Column $column, string $class = "label-success", $translateFile = NULL): ?Html
	{
		if ($value) {
			return Html::el("span", [
				"class" => "label " . $class,
			])->setHtml($this->translate($value, NULL, $translateFile));
		}
		return NULL;
	}

	/**
	 * @param mixed $value
	 * @param Column $column
	 * @param int $length
	 * @return string
	 */
	public function maxLength($value, Column $column, int $length)
	{
		return Strings::truncate($value, $length);
	}

	/**
	 * @param \DateTime $value
	 * @param Column $column
	 * @param int $decimals
	 * @param string $decPoint
	 * @param string $thousandsSep
	 * @return mixed
	 */
	public function number($value, Column $column, int $decimals = 0, string $decPoint = '.', string $thousandsSep = ",")
	{
		if (is_numeric($value)) {
			return number_format($value, $decimals, $decPoint, $thousandsSep);
		}

		return $value;
	}

}
