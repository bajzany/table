<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Bajzany\Table;

use Nette\Localization\ITranslator;

class Config
{

	/**
	 * @var ITranslator|null
	 */
	private $translator;

	/**
	 * @return ITranslator|null
	 */
	public function getTranslator(): ?ITranslator
	{
		return $this->translator;
	}

	/**
	 * @param ITranslator|null $translator
	 * @return $this
	 */
	public function setTranslator(?ITranslator $translator)
	{
		$this->translator = $translator;
		return $this;
	}

}
