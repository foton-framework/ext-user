<?php



class EXT_COM_User extends SYS_Component
{
	//--------------------------------------------------------------------------
	
	public $com_url = '/user/';
	
	//--------------------------------------------------------------------------
	
	function main_init() {}
	
	//--------------------------------------------------------------------------
	
	function init() {}
	
	//--------------------------------------------------------------------------
	
	function index()
	{
		$this->data['users'] = $this->user->model->get_result();
	}
	
	//--------------------------------------------------------------------------
	
	function router($uid, $e404 = NULL)
	{
		if ($e404 !== NULL) return FALSE;
		
		$this->view = 'profile';
		
		$this->db->where('users.id = ?', $uid);
		return $this->data['user'] = $this->user->model->get_row();
	}
	
	//--------------------------------------------------------------------------
	
	function act_login()
	{
		if ($this->user->id)
		{
			hlp::redirect_back();
			exit;
		}
		
		$this->user->model->init_form();

		$this->form->set_rules('email', 'trim|valid_email');
		$this->form->set_rules('login', 'trim');
		
		if ($this->form->validation())
		{
			$this->form->set_error('email', '<b>Логин</b> или <b>Пароль</b> введены не верно');
		}
	}
	
	//--------------------------------------------------------------------------
	
	function act_logout()
	{
		$this->view = FALSE;
		$this->user->logout();
		
		hlp::redirect_back();
	}
	
	//--------------------------------------------------------------------------
	
	function act_edit($id = NULL)
	{
		$this->user->model->init_form();
		
		$id = $this->user->group_id == 1 && $id ? $id : $this->user->id;
		$this->db->where('users.id=?', $id);
		$user_data = $this->user->model->get()->row();
		
		if ( ! $user_data) hlp::redirect('/');
		
		$this->data['fields'] = $this->user->groups[$user_data->group_id]['edit_fields'];
		$this->data['data']   =& $user_data;
		
		foreach ($user_data as $field => $val)
		{
			$this->form->set_value($field, $val);
		}
		
		$this->form->set_field('newpassword', 'password', 'Новый пароль', $this->form->rules('password'));
		$this->form->set_field('newpassconf', 'password', 'Подтверждение пароля', $this->form->rules('password') . '|matches[newpassword]');
		
		$this->load->extension('js_validation');
		$this->js_validation->init_form(&$this->form);
		
		if ($this->form->validation())
		{
			if ($_POST['newpassword'])
			{
				$_POST['password'] = $_POST['newpassword'];
			}
			
			$this->db->where('users.id=?', $id);
			$this->user->model->update();
			
			$this->template->message('Изменения сохранены');
		}
	}
	
	//--------------------------------------------------------------------------
	
	function act_registration()
	{
		$this->load->extension('captcha');
		
		$this->user->model->init_form();
		
		$this->form->set_field('passconf', 'password', 'Подтверждение пароля', $this->form->rules('password') . '|matches[password]');
		$this->form->set_field('captcha' , 'input'   , 'Защитный код', 'trim|strip_tags|required|callback[ext.captcha.validation]');
		
		$this->form->set_required($this->user->groups[3]['required_fields']);
		
		$this->load->extension('js_validation');
		$this->js_validation->init_form(&$this->form);
		
		if ($this->form->validation())
		{
			$id = $this->user->model->insert();
			if ($id) hlp::redirect($this->com_url . 'registration_complete/');
		}
		
		$this->data['fields'] = $this->user->groups[3]['reg_fields'];
	}
	
	//--------------------------------------------------------------------------
	
	function act_registration_complete() {}
	
	//--------------------------------------------------------------------------	
	
	function check_email($email)
	{
		$this->form->set_error_message('callback[com.users.check_email]', 'Пользователя с таким e-mail адресом не существует!');
		
		$user = $this->db->where('email=?', $email)->get('users')->row();
		
		$this->_recovery_user_data = $user;
		
		return (bool)$user;
	}
	
	//--------------------------------------------------------------------------
	
	function act_password_recovery($recovery_key = NULL)
	{
		if ($recovery_key && empty($_POST))
		{
			$this->user->auth_by('recovery_key=?', $recovery_key);
			if ($this->user->id)
			{
				$this->db->where('id=?', $this->user->id)->update('users', array('recovery_key' => ''));
				h_url::redirect('/users/edit/' . $this->user->id . '/from_recovery/');
			}
			else
			{
				$this->template->error = 'Ссылка восстановления пароля не подходит. Попробуйте отправить новый запрос на восстановления пароля';
			}
		}
		
		$this->load->library('form');
		$this->form->set_field('email', 'input', 'E-mail', 'required|valid_email|callback[com.users.check_email]');
		
		if ($this->form->validation())
		{
			$user = $this->_recovery_user_data;
			$data = array('recovery_key' => md5(uniqid($user->id)));
			$this->db->where('id=?', $user->id)->update('users', $data);
			
			$data['login'] = $user->name ? $user->name : $user->login;
			$this->load->library('Mail');
			$this->mail->send_to_user($user->id, 'password_recovery', $data);
			
			$this->template->message = 'На ваш e-mail было отправлено письмо с дальнейшими указаниями по восстановлению пароля.';
		}
	}
	
	//--------------------------------------------------------------------------	
	
	
}