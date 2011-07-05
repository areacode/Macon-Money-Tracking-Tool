<?php 
class Sorter {
	private $order_by_opts;
	private $order_by;
	private $direction;
	public function __construct($order_by_opts) {
		$this->order_by_opts = $order_by_opts;
		$this->order_by = isset($_GET['order_by']) ? $this->order_by_opts[$_GET['order_by']]['order_by_field'] : $this->order_by_opts['default']['order_by_field'];
		$this->direction = isset($_GET['direction']) ? $_GET['direction'] : (isset($_GET['order_by']) ? $this->order_by_opts[$_GET['order_by']]['direction'] : $this->order_by_opts['default']['direction']);
	}
	public function getOrderBy() {
		return $this->order_by;
	}
	public function getDirection() {
		return $this->direction;
	}
	public function getSortableHeaderCells($html = null) {
		foreach($this->order_by_opts as $opt_name => $opt_data) 
			if($opt_name != "default")
				$html .= '<td><a href="' . $_SERVER['PHP_SELF'] . 
					'?order_by=' . $opt_name . (self::isOrderBy($opt_name) ? $this->getDirectionParam() : "") . 
					'">' . $opt_data['display'] . '</a></td>';
		return $html;	
	}
	private static function isOrderBy($opt) {
		return isset($_GET['order_by']) && $_GET['order_by'] == $opt;
	}
	private function getDirectionParam() {	
		return "&direction=" . (
			isset($_GET['direction']) ? ($_GET['direction'] == "asc" ? "desc" : "asc") : 
				(isset($_GET['order_by']) ? ($this->order_by_opts[$_GET['order_by']]['direction'] == "asc" ? "desc" : "asc") : "")
		);
	}
}

function displayErrors($errors) {
	if(count($errors) > 0) { ?><font color="red"><?php echo implode($errors, "<br/>")?></font><p /><?php } 
}

function getZipList($result, $val, $prefix, $first_option = false, $i = null) { ?>
		<?php echo "$prefix$i"?>: <select name="zip<?php echo $i?>">
			<?php if($first_option) { ?><option value="0">any zip</option><?php } ?>
			<option value="-1" <?php echo $val==-1 ? " selected" : ""?>>unknown zip</option>
			<?php foreach($result as $zip) { ?>
				<option value="<?php echo $zip['id']?>" <?php echo $zip['id']==$val ? " selected" : ""?>><?php echo $zip['zip']?></option>
			<?php } ?>
		</select>
		<BR /> 
<?php 
} 
?>