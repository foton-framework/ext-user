<?php



class EXT_MODEL_User extends SYS_Model_Database
{
	//--------------------------------------------------------------------------

	public $table      = 'users';

	public $name        = 'Пользователи';
	public $add_action  = TRUE;
	public $list_action = TRUE;

	public $no_photo   = 'no_avatar.jpg';
	public $thumbs = array(
		'avatar' => array(
			'size' => array(100, 100),
			'dist' => 'files/avatars/',
			'crop' => TRUE
		)
	);

	//--------------------------------------------------------------------------

	// public function __construct(&$ext)
	// {
	// 	parent::__construct();
	// 	$this->user =& is_array($ext) ? $ext[0] : $ext;
	// }

	//--------------------------------------------------------------------------

	public function init()
	{
		$this->fields['users'] = array(
			'id' => array(
				'label' => 'ID',
			),
			'group_id' => array(
				'label'   => 'Группа',
				'default' => 3,
				'field'   => 'select',
				'options' => 'group_list',
				'user_group' => array(1),
			),
			'permission' => NULL,
			'status' => array(
				'label'   => 'Статус',
				'default' => 1,
				'field'   => 'select',
				'options' => 'status_list',
				'user_group' => array(1),
			),
			'login' => array(
				'label'   => 'Имя пользователя (логин)',
				'field'   => 'input',
				'rules'   => 'trim|strip_tags|min_length[3]|max_length[15]|callback[ext.user.model.check_login]',
			),
			'email' => array(
				'label' => 'E-Mail',
				'field' => 'input',
				'rules' => 'trim|valid_email|callback[ext.user.model.check_email]',
			),
			'password' => array(
				'label' => 'Пароль',
				'field' => 'password',
				'rules' => 'trim|length[5,30]|callback[ext.user.model.password_hash]',
			),
			'regdate' => array(
				'label'   => 'Дата регистрации',
				'default' => time(),
			),
			'last_visit' => array(
				'label'   => 'Последнее посещение',
				'default' => time(),
			),
			'photo' => array(
				'label'   => 'Фото',
				'field'   => 'file',
				'rules'   => 'callback[ext.user.model.upload,photo]',
				'default' => $this->no_photo
			),
			'name' => array(
				'label'   => 'Имя',
				'field'   => 'input',
				'rules'   => 'trim|strip_tags|min_length[3]',
			),
			'country_id' => array(
				'label'   => 'Страна',
				'field'   => 'select',
				'options' => 'country_list',
				'rules'   => 'trim|numeric',
			),
			'city_id' => array(
				'label'   => 'Город',
				'field'   => 'select',
				'options' => 'city_list',
				'rules'   => 'trim|numeric'
			),
			'score' => array(
				'label'   => 'Баллы',
				'field'   => 'input',
				'default' => 0,
				'rules'   => 'trim|numeric',
			),

			'surname' => array(
				'label'   => 'Фамилия',
				'field'   => 'input',
				'rules'   => 'trim|strip_tags|length[2,32]',
			),
			'patronymic' => array(
				'label'   => 'Отчество',
				'field'   => 'input',
				'rules'   => 'trim|strip_tags|length[2,32]',
			),
			'speciality' => array(
				'label'   => 'Специальность',
				'field'   => 'input',
				'rules'   => 'trim|strip_tags|length[2,128]',
			),
			'degree' => array(
				'label'   => 'Ученая Степень',
				'field'   => 'input',
				'rules'   => 'trim|strip_tags|length[2,128]',
			),
			'experience' => array(
				'label'   => 'Опыт работы',
				'field'   => 'input',
				'rules'   => 'trim|strip_tags|length[2,128]',
			),
			'phone' => array(
				'label'   => 'Телефон',
				'field'   => 'input',
				'rules'   => 'trim|strip_tags|length[2,128]',
			),
			'work' => array(
				'label'   => 'Место работы',
				'field'   => 'input',
				'rules'   => 'trim|strip_tags|length[2,255]',
			),
			'url' => array(
				'label'   => 'Сайт (работы, личный)',
				'field'   => 'input',
				'rules'   => 'trim|strip_tags|valid_url|length[2,255]',
			),

			'u_thanks' => NULL,
			'u_ratio'  => NULL,

		);

		sys::set_config_items($this, 'user_model');

		if ( ! empty(sys::$config->user_model_fields))
		{
			$this->fields['users'] = array_merge($this->fields['users'], sys::$config->user_model_fields);
		}
	}

	//--------------------------------------------------------------------------

	public function init_quickreg_form()
	{
		$this->load->library('form');

		$login_field = $this->user->option('login_field');
		$login_opt   = $this->fields['users'][$this->user->option('login_field')];
		$this->form->set_field($login_field, $login_opt['field'], $login_opt['label'], $login_opt['rules']);
		$this->form->set_required($login_field);

		if ($login_field != 'email')
		{
			$email_opt = $this->fields['users']['email'];
			$this->form->set_field('email', $email_opt['field'], $email_opt['label'], $email_opt['rules']);
			$this->form->set_required('email');
		}
	}

	//--------------------------------------------------------------------------

	function count_users()
	{
		static $count;

		if ($count === NULL)
		{
			$this->db->where('users.group_id=3');
			$this->db->where('users.status=1');
			$count = $this->db->count_all($this->table());
		}

		return $count;
	}

	//--------------------------------------------------------------------------

	function count_doctors()
	{
		static $count;

		if ($count === NULL)
		{
			$this->db->where('users.group_id=2');
			$this->db->where('users.status=1');
			$count = $this->db->count_all($this->table());
		}

		return $count;
	}

	//--------------------------------------------------------------------------

	function get_doctors()
	{
		$this->db->where('users.group_id = 2');
		$result = $this->get_result();

		$trends_id = array();
		$trends = $this->db->get('trends')->result();
		foreach ($trends as $row) $trends_id[$row->id] = $row;

		$dtrends_id = array();
		$dtrends = $this->db->get('doctor_trends')->result();
		foreach ($dtrends as $row) $dtrends_id[$row->doctor_id][] = $trends_id[$row->trend_id];

		foreach ($result as &$row)
		{
			if (isset($dtrends_id[$row->id]));
			$row->trends = $dtrends_id[$row->id];
		}

		return $result;
	}

	//--------------------------------------------------------------------------

	function upload($value, $callback, $field)
	{
		$key_value = $this->form->value('id');

		if ( ! $value) return TRUE;

		$this->load->library('upload');
		if ($key_value) $this->load->library('image');





		if ($result = $this->upload->run($field))
		{
			if ($key_value)
			{
				$file_name = $key_value . '.jpg';
				$this->image->set_file_name($file_name);
				$this->image->process($result->full_path, $this->thumbs);
				return $file_name;
			}

			return TRUE;
		}

		$this->form->set_error_message("callback[ext.user.model.upload,photo]", $this->upload->error($field));
		return FALSE;
	}

	//--------------------------------------------------------------------------

	public function prepare_row_result(&$row)
	{
		if (isset($row->regdate))    $row->regdate    = hlp::date($row->regdate);
		if (isset($row->last_visit)) $row->last_visit = $row->last_visit ? hlp::date($row->last_visit) : 'никогда';

		if (isset($row->status))   $row->status_name = $this->status_list($row->status);
		if (isset($row->group_id)) $row->group_name  = $this->group_list($row->group_id);

		if (isset($row->score) && $row->score > 0) $row->score = '+' . $row->score;

		if (isset($row->photo))
		{
			foreach($this->thumbs as $name => $opt)
			{
				$row->$name = '/' . $opt['dist'] . ($row->photo ? $row->photo : $this->no_photo);
			}
		}

		if ((isset($row->name) || isset($row->login)) && isset($row->group_id))
		{
			$row->full_name = $row->group_id == 2 ? $row->surname . ' ' . $row->name . ' ' . $row->patronymic : (!empty($row->login) ? $row->login : $row->name);
		}

		if ($row->group_id == 1)
		{
			$row->speciality = '<em>Администратор портала</em>';
		}

		$row->password = '***';

		if (isset($row->u_ratio)) $row->u_ratio = ($row->u_ratio > 0) ? '+' . $row->u_ratio : $row->u_ratio;

		if ($this->user->profile_link_tpl)
		{
			$row->profile_url = '';//$this->user->profile_link[0]; '/users/' . (isset($row->uid) ? $row->uid : $row->id) . '/';
			foreach ($this->user->profile_link_tpl as $i => $val)
			{
				if ( ! isset($row->$val))
					$row->profile_url .= $val;
				else
				{
					if ($val == 'id') $val = (isset($row->uid) ? 'uid' : 'id');
					$row->profile_url .= $row->$val;
				}
			}
		}
		else
		{
			$row->profile_url = '/users/' . (isset($row->uid) ? $row->uid : $row->id) . '/';
		}

		$row->full_link = $row->profile_url;

		return parent::prepare_row_result($row);
	}

	//--------------------------------------------------------------------------

	public function check_login($login)
	{
		if ( ! $login || $this->user->group_id == 1 || $this->user->id && $this->user->login == $login) return TRUE;
		$this->form->set_error_message('callback[ext.user.model.check_login]', 'Пользователь с таким именем уже зарегистрирован!');

		return ! $this->db->where('login = ?', $login)->count_all($this->table());
	}

	//--------------------------------------------------------------------------

	public function check_email($email)
	{
		if ( ! $email || $this->user->group_id == 1 || $this->user->id && $this->user->email == $email) return TRUE;

		$this->form->set_error_message('callback[ext.user.model.check_email]', 'Пользователь с таким e-mail адресом уже зарегистрирован!');

		return ! $this->db->where('email = ?', $email)->count_all($this->table());
	}

	//--------------------------------------------------------------------------

	public function password_hash($password)
	{
		if ( ! $password)
		{
			unset($_POST['password']);
			return TRUE;
		}

		switch ($this->user->option('password_hash_type'))
		{
			case 'old':
				return md5($this->user->option('salt') . $password);

			case 'md5':
			default:
				return md5($password . $this->user->option('salt'));
		}
	}

	//--------------------------------------------------------------------------

	public function group_list($val = NULL)
	{
		static $list;

		if ($list === NULL)
		{
			foreach ($this->user->groups as $id=>$group) $list[$id] = $group['name'];
		}

		if ($val !== NULL) return isset($list[$val]) ? $list[$val] : FALSE;

		return $list;
	}

	//--------------------------------------------------------------------------

	public function status_list($val = NULL)
	{
		static $list = array(
			-1 => 'На премодерации',
			0 => 'Отключен',
			1 => 'Включен',
		);

		if ($val !== NULL) return $list[$val];

		return $list;
	}

	//--------------------------------------------------------------------------

	public function expert_list($val = NULL)
	{
		static $list = array(
			0 => 'Нет',
			1 => 'Да',
		);

		if ($val !== NULL) return $list[$val];

		return $list;
	}

	//--------------------------------------------------------------------------

	public function country_list()
	{
		$this->db->order_by('country.priority DESC, country.title');
		$this->db->where('country.status = 1');
		$country = $this->db->get('country')->result();
		$result[''] = '';
		foreach ($country as $c) $result[$c->id] = $c->title;

		return $result;
	}

	//--------------------------------------------------------------------------

	public function city_list()
	{
		return array();

		$this->db->order_by('city.priority, city.title');
		$this->db->where('city.status = 1');
		$city = $this->db->get('city')->result();
		$result[''] = '';
		foreach ($city as $c) $result[$c->id] = $c->title;

		return $result;
	}

	//--------------------------------------------------------------------------

	public function trend_list()
	{
		$this->db->order_by('trends.doc_name');
		$this->db->where('trends.status = 1');
		$trends = $this->db->get('trends')->result();
		foreach ($trends as $t) $result[$t->id] = $t->doc_name;

		return $result;
	}

	//--------------------------------------------------------------------------

	public function get($table = NULL)
	{
		if ($this->user->group_id != 1)
		{
			$this->db->where('users.status=1');
		}

		$this->db->select('users.*, city.title AS city, country.title AS country');
		$this->db->join('city'   , 'city.id = users.city_id');
		$this->db->join('country', 'country.id = users.country_id');

		return parent::get($table = NULL);
	}

	//--------------------------------------------------------------------------

	public function update($table = NULL, $data = NULL)
	{
		$result = parent::update($table, $data);

		if ( ! $data) $data = $_POST;

		if (isset($data['doctor_trends']))
		{
			$this->doctor_trends_model()->set_trends($this->user->id, (array)$data['doctor_trends']);
			$result ++;
		}

		return $result;
	}


	//--------------------------------------------------------------------------

	public function delete($table = NULL, $data = NULL)
	{
		$resutl = parent::delete($table, $data);

		$this->db->delete('comments', array('uid=?'=>current($data)));
//		$this->db->delete('topics', array('uid=?'=>current($data)));
		$this->db->delete('ratio', array('uid=?'=>current($data)));
//		$this->db->delete('thanks', array('uid=?'=>current($data)));

		return $resutl;
	}

	//--------------------------------------------------------------------------

	public function insert($table = NULL, $data = NULL)
	{
		if ( ! $data)
		{
			$data = $_POST;
		}

		$id = parent::insert($table, $data);

		if (isset($data['doctor_trends']))
		{
			$this->doctor_trends_model()->set_trends($id, (array)$data['doctor_trends']);
		}

		if ( ! empty($data['photo']))
		{
			$this->load->library('image');
			$file_name = $id . '.jpg';
			$this->image->set_file_name($file_name);
			$this->image->process($data['photo'], $this->thumbs);

			$this->db->where('id=?', $id);
			$this->update(NULL, array('photo'=>$file_name));
		}

		return $id;
	}

	//--------------------------------------------------------------------------

	public function &doctor_trends_model()
	{
		static $model;

		if ($model === NULL)
		{
			require_once dirname(__FILE__) . '/doctor_trends' . MODEL_EXT;
			$model = new MODEL_Doctor_trends();
		}

		return $model;
	}

	//--------------------------------------------------------------------------
}