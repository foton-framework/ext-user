<?php


class EXT_User_Permission
{
	
	//--------------------------------------------------------------------------

	private $db;
	private $user;
	
	private $rules  = NULL;
	private $loaded = FALSE;
	
	//--------------------------------------------------------------------------
	
	public function __construct(&$user_ext)
	{
		$this->db   =& sys::$lib->db;
		$this->user =& $user_ext;
	}
	
	//--------------------------------------------------------------------------
	
	public function check_url($url = NULL)
	{
		if ($url === NULL)
		{
			die('View: Class: ' . __CLASS__ . '<br>' . __FILE__ . ' (line: ' . __LINE__ . ')');
		}
		
		$this->_load_rules();
		
		$allow = FALSE;
		foreach ($this->rules as $rule)
		{
			if ($rule->allow == $url) $allow = TRUE;
			if ($rule->deny  == $url) $allow = FALSE;
		}
		
		return $allow;
	}
	
	//--------------------------------------------------------------------------
	
	private function _load_rules()
	{
		if ($this->loaded) return;
		
		$this->rules = $this->db->order_by('priority')->where('uid=? || group_id=?', $this->user->id, $this->user->group_id)->get('users_permission')->result();
		
		$this->loaded = TRUE;
	}
	
	//--------------------------------------------------------------------------
}