<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 04.01.2019
 */

namespace Bajzany\Table\HtmlElements;

interface HtmlElements
{
	public function build();

	public function getChildren();
}
