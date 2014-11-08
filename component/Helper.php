<?php

namespace hikari\component;

class Helper extends Component {

	function __get($key) {
		return $this->component($key);
	}

}
