<?php

	class IconDefaultPermissions extends Doctrine_Template {

		public function setTableDefinition()
		{
			$this->addListener(new IconDefaultPermissionsListener());
		}
	}

?>
