<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Bajzany\Table;

use Nette\Utils\Html;

/**
 * Trait TableUtils
 */
trait TableUtils
{

	/**
	 * @param string $destination
	 * @param array $arguments
	 * @param string $tooltip
	 * @return Html
	 */
	protected function removeLink($destination, array $arguments = [], $tooltip = "Remove"): Html
	{
		$link = $this->link($destination, $arguments);
		return Html::el("a", [
			"class" => "btn btn-danger ajax",
			"data-toggle" => "tooltip",
			"title" => $this->translate($tooltip),
			"href" => $link,
		])->setHtml("<i class=\"fa fa-trash\" aria-hidden=\"true\"></i>");
	}

	/**
	 * @param string $destination
	 * @param array $arguments
	 * @param string $icon
	 * @param string $tooltip
	 * @return Html
	 */
	protected function actionLink($destination, array $arguments = [], $icon = "fas fa-certificate", $tooltip = "Action"): Html
	{
		$link = $this->link($destination, $arguments);
		return Html::el("a", [
			"class" => "btn btn-default ajax",
			"data-toggle" => "tooltip",
			"title" => $this->translate($tooltip),
			"href" => $link,
		])->setHtml("<i class=\"{$icon}\" aria-hidden=\"true\"></i>");
	}

	/**
	 * @param string $destination
	 * @param array $arguments
	 * @param string $icon
	 * @param string $tooltip
	 * @return Html
	 */
	protected function actionPresenterLink($destination, array $arguments = [], $icon = "fas fa-certificate", $tooltip = "Action"): Html
	{
		$link = $this->presenter->link($destination, $arguments);
		return Html::el("a", [
			"class" => "btn btn-default ajax",
			"data-toggle" => "tooltip",
			"title" => $this->translate($tooltip),
			"href" => $link,
		])->setHtml("<i class=\"{$icon}\" aria-hidden=\"true\"></i>");
	}

}
