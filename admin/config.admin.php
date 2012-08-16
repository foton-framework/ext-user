<?php



$this->admin->add_main_menu_item(array(
	'title' => 'Пользователи',
	'key'   => 'users',
	'icon'  => '/' . EXT_FOLDER . '/user/admin/group-16.png',
	'com'   => EXT_PATH . 'user/admin/ Users',
	'priority' => '10',
));