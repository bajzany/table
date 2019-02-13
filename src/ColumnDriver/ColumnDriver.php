<?php

/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 */

namespace Bajzany\Table\ColumnDriver;

use Bajzany\Table\EntityTable\Column;
use Bajzany\Table\EntityTable\IColumn;
use Bajzany\Table\Exceptions\TableException;

class ColumnDriver
{
	/**
	 * @var IColumn[]
	 */
	private $availableColumns = [];

	/**
	 * @var IColumn[]
	 */
	private $enabledColumns = [];

	/**
	 * @var IColumn[]
	 */
	private $disabledColumns = [];

	public function enableAvailableColumn()
	{
		$this->enabledColumns = $this->availableColumns;
	}

	/**
	 * @return IColumn[]
	 */
	public function getAvailableColumns(): array
	{
		return $this->availableColumns;
	}

	/**
	 * @internal
	 *
	 * @param string $key
	 * @param IColumn $column
	 * @return $this
	 */
	public function addAvailableColumn(string $key, IColumn $column)
	{
		$this->availableColumns[$key] = $column;
		return $this;
	}

	/**
	 * @return IColumn[]
	 */
	public function getEnabledColumns(): array
	{
		return $this->enabledColumns;
	}

	/**
	 * @param string $key
	 * @return $this
	 * @throws TableException
	 */
	public function addEnabledColumn(string $key)
	{
		$column = $this->getColumn($key);
		if (!$column) {
			throw TableException::columnDriverKeyDontExist($key, 'enabledColumns');
		}
		$this->enabledColumns[$key] = $column;
		return $this;
	}

	/**
	 * @return Column[]
	 */
	public function getDisabledColumns(): array
	{
		return $this->disabledColumns;
	}

	/**
	 * @param string $key
	 * @return $this
	 * @throws TableException
	 */
	public function addDisabledColumn(string $key)
	{
		$column = $this->getColumn($key);
		if (!$column) {
			throw TableException::columnDriverKeyDontExist($key, 'disabledColumns');
		}
		$this->disabledColumns[$key] = $column;
		return $this;
	}

	/**
	 * @param string $key
	 */
	public function removeAvailableColumn(string $key)
	{
		unset($this->availableColumns[$key]);
		unset($this->enabledColumns[$key]);
		unset($this->disabledColumns[$key]);
	}

	/**
	 * @param string $key
	 */
	public function removeEnabledColumn(string $key)
	{
		unset($this->availableColumns[$key]);
	}

	/**
	 * @param string $key
	 */
	public function removeDisabledColumn(string $key)
	{
		unset($this->availableColumns[$key]);
	}

	/**
	 * @param string $key
	 * @return IColumn|null
	 */
	public function getColumn(string $key)
	{
		if ($this->issetColumnKey($key)) {
			return $this->availableColumns[$key];
		}
		return NULL;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function issetColumnKey(string $key): bool
	{
		if (array_key_exists($key, $this->availableColumns)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @param array $enabledColumns
	 * @return $this
	 * @throws TableException
	 */
	public function setEnabledColumns(array $enabledColumns)
	{
		foreach ($enabledColumns as $key) {
			$column = $this->getColumn($key);
			if (!$column) {
				throw TableException::columnDriverKeyDontExist($key, 'enabledColumns');
			}
			$this->enabledColumns[$key] = $column;
		}
		return $this;
	}

	/**
	 * @param array $disabledColumns
	 * @return $this
	 * @throws TableException
	 */
	public function setDisabledColumns(array $disabledColumns)
	{
		foreach ($disabledColumns as $key) {
			$column = $this->getColumn($key);
			if (!$column) {
				throw TableException::columnDriverKeyDontExist($key, 'disabledColumns');
			}
			$this->disabledColumns[$key] = $column;
		}
		return $this;
	}

}
