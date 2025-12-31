<?php

namespace App\UI\AgGrid;

use App\Common\str;

/**
 * Class AgGrid
 *
 * Generates an Ag-Grid HTML container with specified columns, data, and height.
 */
class AgGrid {
	const DEFAULT_THEME = "ag-theme-quartz";

	private array $columns = [];

	private array $data = [];

	/**
	 * The height of the grid in pixels.
	 * Default is 400px.
	 *
	 * @var int
	 */
	private int $height = 400;
	public function __construct(?string $id = NULL)
	{
		$this->setId($id);
	}

	/**
	 * Sets the ID of the grid. If no ID is provided, a unique one is generated.
	 *
	 * @param string|null $id
	 *
	 * @return void
	 */
	private function setId(?string $id)
	{
		if($id){
			$this->id = $id;
		} else {
			$this->id = "ag-grid-".str::uuid();
		}
	}

	/**
	 * Gets the ID of the grid.
	 *
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * Sets the columns of the grid.
	 *
	 * @param array $columns
	 *
	 * @return void
	 */
	public function setColumns(array $columns): void
	{
		$this->columns = $columns;
	}

	/**
	 * Sets the height of the grid in pixels.
	 *
	 * @param int $height
	 *
	 * @return void
	 */
	public function setHeight(int $height): void
	{
		$this->height = $height;
	}

	/**
	 * Must include hash keys so that data can be requested.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function setDataArray(array $data): void
	{
		$this->data = $data;
	}

	/**
	 * Generates the HTML for the Ag-Grid container.
	 *
	 * @return string
	 */
	public function getHtml(): string
	{
		# Convert the grid columns to base64 json and add it as a data attribute
		$this->data['grid-columns'] = base64_encode(json_encode(array_values($this->columns)));
		// We're doing this to avoid any issues with special characters in the JSON breaking the HTML

		$id = str::getAttrTag("id", $this->getId());
		$class = str::getAttrTag("class", [self::DEFAULT_THEME]);
		$style = str::getAttrTag("style", ["height: ".$this->height."px;"]);
		$data = str::getDataAttr($this->data);

		return "<div{$id}{$class}{$style}{$data}></div>";
	}
}