<?php


class COM_Users extends SYS_Component
{
	//--------------------------------------------------------------------------

	public $com_url = '/admin/users/';
	
	//--------------------------------------------------------------------------
	
	function init()
	{
		$this->admin->backend_sub_menu = array(
			$this->com_url . 'group/3/' => 'Пользователи',
			$this->com_url . 'group/2/' => 'Врачи',
			$this->com_url . 'group/1/' => 'Администраторы',
		);
	}
	
	//--------------------------------------------------------------------------
	
	function index()
	{
		hlp::redirect(current(array_keys($this->admin->backend_sub_menu)));
	}

	//--------------------------------------------------------------------------
	
	function act_group($group_id, $status=NULL)
	{
		// @list($status, $order) = explode(':', $opt);

		return $this->render_list($group_id, $status);
	}

	//--------------------------------------------------------------------------

	function render_list($group_id=NULL, $status=NULL)
	{
		$status_list = $this->user->model->status_list();


		if ($group_id !== NULL && $group_id !== FALSE)
		{
			$this->db->where('group_id=?', $group_id);
		}
		if ($status !== NULL && $status !== FALSE)
		{
			$this->db->where('status=?', $status);
		}
		$this->db->order_by('id DESC');
		$this->db->limit(100);
		$data = $this->user->model->get_result();

		$this->load->library('pagination');


		// VIEW
		$this->view = 'list';
		$this->data['data']        =& $data;
		// $this->data['count']       =& $data->count;
		$this->data['group_id']    =& $group_id;
		$this->data['status']      =& $status;
		$this->data['status_list'] =& $status_list;
	}
	
	//--------------------------------------------------------------------------
	
	function router($uid, $sub_a = 'index')
	{
		$this->db->where('users.id=? AND group_id=2', $uid);
		$this->doctor = $this->user->model->get_row();
		
		if ( ! $this->doctor) die('no user');
		
		
		$this->data['doctor'] =& $this->doctor;
		$this->data['link']   = "/admin/admin_doctors/{$this->doctor->id}/";
		$this->data['sub_a']  = $sub_a;
		
		$this->view = 'router';
		
		$sub_method = 'sub_' . $sub_a;
		if (method_exists(&$this, $sub_method))
		{
			$this->$sub_method($uid);
		}
	}
	
	//--------------------------------------------------------------------------
	
	function sub_index($uid)
	{
		$this->data['stat'] = array();
		
		$this->data['stat'][] = array(
			'title' => 'Последний раз был на сайте',
			'value' => $this->doctor->last_visit
		);
		
		
		$last_answer = $this->db->limit(1)->where('did=? AND answerdate!=0', $uid)->get('faq')->row();
		$last_answer = $last_answer ? hlp::date($last_answer->answerdate) : '-';
		$this->data['stat'][] = array(
			'title' => 'Последний ответ был дан',
			'value' => $last_answer
		);
		$this->data['stat'][] = array(
			'title' => 'Ответов',
			'value' => $this->db->where('did=? AND answerdate!=0', $uid)->count_all('faq')
		);
		$this->data['stat'][] = array(
			'title' => 'Не ответил на личные вопросы',
			'value' => $this->db->where('did=? AND answerdate=0', $uid)->count_all('faq')
		);
		
	}
	
	//--------------------------------------------------------------------------
	
	function sub_edit($uid)
	{
		$this->user->model->init_form();
		
		
		$this->form->set_field('doctor_trends', 'multiselect', 'Направления<div style="font-size:11px;width:200px">Чтобы выбрать несколько направлений нажмите клавишу "Ctrl" и не отпуская кликните мышкой по нужному направлению</div>', 'required');
		$this->form->set_options('doctor_trends', $this->user->model->trend_list());
		
		$this->form->set_value('doctor_trends', $this->user->model->doctor_trends_model()->values($uid));
		$this->form->set_field('newpassword', 'password', 'Новый пароль', $this->form->rules('password'));
		$this->form->set_field('newpassconf', 'password', 'Подтверждение пароля', $this->form->rules('password') . '|matches[newpassword]');
		$this->form->remove_field('password');
		
		$this->form->set_required('email');
		
		foreach ($this->doctor as $key => $val)
		{
			$this->form->set_value($key, $val);
		}
		
		if ($this->form->validation())
		{
			$_POST['password'] = $_POST['newpassword'];
			
			$this->db->where('users.id=?', $uid);
			$this->user->model->update();
			
			$this->message = 'Изменения сохранены!';
		}
	}
	
	
	//--------------------------------------------------------------------------
	
	function act_add()
	{
		$pass = @$_POST['password'];
		
		$this->user->model->init_form();
		
		$this->form->remove_field('group_id');
		
		$this->form->set_field('password', 'input', 'Пароль', $this->form->rules('password'));
		
		$this->form->set_required('email');
		
		if ($this->form->validation())
		{
			$_POST['group_id'] = 2;
			$id = $this->user->model->insert();
			
			header("Location: /admin/admin_doctors/{$id}/");
			exit;
		}
		
		$this->form->set_value('password', $pass, TRUE);
	}
	
	//--------------------------------------------------------------------------
}