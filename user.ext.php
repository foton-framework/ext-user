<?php



class EXT_User
{

	//--------------------------------------------------------------------------
	
	public $id       = 0;
	public $group_id = 0;
	public $data;
	
	public $_sid     = '';
	public $_expire  = 31104000; // year
	public $model   = NULL;
	public $options = array(
		'login_field'      => 'login',
		'password_field'   => 'password',
		'autologin_field'  => 'autologin',
		'autologout_field' => 'autologout',

		'salt'               => 'ak&23d0Xq1',
		'password_hash_type' => 'md5',
	);
	
	public $groups = array(
		1 => 'Администратор',
		2 => 'Врачь',
		3 => 'Пользователь',
	);
	
	//--------------------------------------------------------------------------
	
	public function __construct()
	{
		sys::set_config_items(&$this, 'user');
		
		$this->set_session_id();
		
		require dirname(__FILE__) . '/user' . MODEL_EXT;
		$this->model = new EXT_MODEL_User(&$this);
		
		if ( ! empty($_POST[$this->option('autologin_field')]))
		{
			$login    = isset($_POST[$this->option('login_field')])    ? $_POST[$this->option('login_field')]    : NULL;
			$password = isset($_POST[$this->option('password_field')]) ? $_POST[$this->option('password_field')] : NULL;
			
			if ($login && $password)
			{
				$this->login($login, $password);
			}
		}
		elseif ( ! empty($_POST[$this->option('autologout_field')])) {
			$this->logout();
		}
		else
		{
			$this->validation();
		}
		
		require dirname(__FILE__) . '/user_permission' . EXT;
		$this->permission = new EXT_User_Permission(&$this);
	}
	
	//--------------------------------------------------------------------------
	
	public function set_option($name, $value)
	{
		$this->options[$name] = $value;
	}
	
	//--------------------------------------------------------------------------
	
	public function option($name)
	{
		return isset($this->options[$name]) ? $this->options[$name] : NULL;
	}
	
	//--------------------------------------------------------------------------
	
	public function validation()
	{
		if ( ! $this->_sid) return FALSE;
		
		$session = $this->model->db->where('sid=? AND last_visit > ?', $this->_sid, time() - $this->_expire)
			->get('users_sessions')->row();
			
		if ($session && $session->uid)
		{
			return $this->auth_by('users.id=?', $session->uid);
		}
		
		//return $this->auth_by('users.id=?', $_SESSION['id']);
	}
	
	//--------------------------------------------------------------------------
	
	public function login($login, $password)
	{
		$password = $this->model->password_hash($password);
		return $this->auth_by('users.' . $this->option('login_field') . ' = ? AND users.' . $this->option('password_field') . ' = ?', $login, $password);
	}
	
	//--------------------------------------------------------------------------
	
	public function logout()
	{
		//unset($_SESSION['id']);
		//unset($_SESSION['group_id']);
		
		if ( ! $this->_sid) return;
		
		setcookie($this->session_name(), '', 0);
		unset($_COOKIE[$this->session_name()]);
		$this->model->db->where('sid=?', $this->_sid)->delete('users_sessions');
		
		$this->_sid     = '';
		$this->id       = 0;
		$this->group_id = 0;
	}
	
	//--------------------------------------------------------------------------
	
	public function new_user()
	{
		if (empty($_POST['email'])) return FALSE;
		
		if (empty($_POST['password']))
		{
			$password = substr(md5(rand(0,9999) . microtime()), 0, 5);
			$_POST['new_password'] = $password;
			$_POST['password'] = $this->model->password_hash($password);
		}
		
		if (empty($_POST['login']))
		{
			$_POST['login'] = $_POST['email'];
		}
		
		$data = $_POST;
		$data['group_id'] = $this->model->fields['users']['group_id']['default'];

		$uid = $this->model->insert(NULL, $data);
		$this->auth_by('users.id=?', $uid);
		
		$email_data = $_POST;
		
		if (isset($email_data))
		{
			$email_data['uid'] = $uid;
			sys::$lib->load->library('Mail')->send_to_user($uid, 'auto_registration', $email_data);
		}
		
		return $uid;
	}
	
	//--------------------------------------------------------------------------
	
	public function auth_by()
	{
		$args = func_get_args();
		call_user_func_array(array(&$this->model->db, 'where'), $args);
		$row = $this->model->get_row();

		if ( ! $row)
		{
			$this->logout();
			return FALSE;
		}
		
		$this->model->db->where('id=?', $row->id)->update('users', array('last_visit'=>time()));
		
		$this->update_session(&$row);
		
		//$_SESSION['id']       = $row->id;
		//$_SESSION['group_id'] = $row->group_id;
		
		foreach ($row as $key => $val)
		{
			$this->data->$key = $val;
			
			if (empty($this->$key))
			{
				$this->$key =& $this->data->$key;
			}
		}
		
		return $row->id;
	}
	
	//--------------------------------------------------------------------------
	
	function session_name()
	{
		return 'user_sid';
	}
	
	//--------------------------------------------------------------------------
	
	function update_session($row)
	{
		if ( ! $this->_sid) $this->set_session_id(md5(uniqid() . microtime()));

		$this->model->db->where('sid=?', $this->_sid);
		$this->model->db->update('users_sessions', array('last_visit'=>time()));
		$this->model->db->affected_rows();
		
		if ( ! $this->model->db->affected_rows())
		{
			$this->model->db->query('INSERT IGNORE INTO users_sessions (sid,uid,last_visit) VALUES(?,?,?)', $this->_sid, $row->id, time());
		}
	}
	
	//--------------------------------------------------------------------------
	
	function set_session_id($id = NULL)
	{
		if ($id)
		{
			$this->_sid = $id;
		}
		elseif ( ! empty($_COOKIE[$this->session_name()]))
		{
			$this->_sid = $_COOKIE[$this->session_name()];
		}
		else
		{
			//$this->_sid = md5(uniqid() . microtime());
		}
		
		if ( ! $this->_sid) return;
		
		$host = '.' . preg_replace('/^(v2\.|www\.)?/i', '', $_SERVER['HTTP_HOST']);
		setcookie($this->session_name(), $this->_sid, time() + $this->_expire, '/', $host, false, true);
		$_COOKIE[$this->session_name()] = $this->_sid;
	}
	
	//--------------------------------------------------------------------------
	
}