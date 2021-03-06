<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Admin_interface extends MY_Controller{

	var $user = array('uid'=>0,'uname'=>'','ulogin'=>'','utype'=>'','balance'=>0);
	var $loginstatus = array('status'=>FALSE);
	var $months = array("01"=>"января","02"=>"февраля","03"=>"марта","04"=>"апреля","05"=>"мая","06"=>"июня","07"=>"июля","08"=>"августа","09"=>"сентября","10"=>"октября","11"=>"ноября","12"=>"декабря");
	
	function __construct(){
		
		parent::__construct();
		$this->load->model('mdusers');
		$this->load->model('mdunion');
		$this->load->model('mdmessages');
		$this->load->model('mdmarkets');
		$this->load->model('mdtkmsgs');
		$this->load->model('mdtickets');
		$this->load->model('mdplatforms');
		$this->load->model('mdmkplatform');
		$this->load->model('mdtypeswork');
		$this->load->model('mdratings');
		$this->load->model('mddelivesworks');
		$this->load->model('mdservices');
		$this->load->model('mdlog');
		$this->load->model('mdthematic');
		$this->load->model('mdcms');
		$this->load->model('mdvaluesrv');
		$this->load->model('mdwebmarkets');
		$this->load->model('mdattachedservices');
		$this->load->model('mdevents');
		$this->load->model('mdpromocodes');

		$cookieuid = $this->session->userdata('logon');
		if(isset($cookieuid) and !empty($cookieuid)):
			$this->user['uid'] = $this->session->userdata('userid');
			if($this->user['uid']):
				$userinfo = $this->mdusers->read_record($this->user['uid']);
				if($userinfo['type'] == 5):
					$this->user['ulogin'] 		= $userinfo['login'];
					$this->user['uname'] 		= $userinfo['fio'];
					$this->user['utype'] 		= $userinfo['type'];
					$this->user['balance'] 		= $userinfo['balance'];
					$this->loginstatus['status']= TRUE;
				else:
					redirect('');
				endif;
			endif;
			if($this->session->userdata('logon') != md5($userinfo['login'].$userinfo['password'])):
				$this->loginstatus['status'] = FALSE;
				redirect('');
			endif;
		else:
			redirect('');
		endif;
	}
	
	public function control_panel(){
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Дополнительные возможности',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'webmasters'	=> count($this->mdusers->read_users_by_type(1)),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/control-panel",$pagevar);
	}
	
	public function actions_log(){
		
		$from = intval($this->uri->segment(5));
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | События',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'events'		=> $this->mdunion->read_events(25,$from),
					'pages'			=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		for($i=0;$i<count($pagevar['events']);$i++):
			$pagevar['events'][$i]['date'] = $this->operation_dot_date_on_time($pagevar['events'][$i]['date']);
		endfor;
		$config['base_url'] 		= $pagevar['baseurl'].'admin-panel/actions/log/from/';
		$config['uri_segment'] 		= 5;
		$config['total_rows'] 		= $this->mdlog->count_records();
		$config['per_page'] 		= 25;
		$config['num_links'] 		= 4;
		$config['first_link']		= 'В начало';
		$config['last_link'] 		= 'В конец';
		$config['next_link'] 		= 'Далее &raquo;';
		$config['prev_link'] 		= '&laquo; Назад';
		$config['cur_tag_open']		= '<li class="active"><a href="#">';
		$config['cur_tag_close'] 	= '</a></li>';
		$config['full_tag_open'] 	= '<div class="pagination"><ul>';
		$config['full_tag_close'] 	= '</ul></div>';
		$config['first_tag_open'] 	= '<li>';
		$config['first_tag_close'] 	= '</li>';
		$config['last_tag_open'] 	= '<li>';
		$config['last_tag_close'] 	= '</li>';
		$config['next_tag_open'] 	= '<li>';
		$config['next_tag_close'] 	= '</li>';
		$config['prev_tag_open'] 	= '<li>';
		$config['prev_tag_close'] 	= '</li>';
		$config['num_tag_open'] 	= '<li>';
		$config['num_tag_close'] 	= '</li>';
		
		$this->pagination->initialize($config);
		$pagevar['pages'] = $this->pagination->create_links();
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/control-log",$pagevar);
	}
	
	public function actions_log_clear(){
		
		$this->mdlog->delete_records();
		redirect($_SERVER['HTTP_REFERER']);
	}
	
	public function messages_system_clear(){
		
		$this->mdmessages->delete_system();
		redirect($_SERVER['HTTP_REFERER']);
	}
	
	public function actions_profile(){
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Личный кабинет',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'user'			=> $this->mdusers->read_record($this->user['uid']),
					'cntunit'		=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('submit')):
			$_POST['submit'] = NULL;
			$this->form_validation->set_rules('fio',' ','required|trim');
			$this->form_validation->set_rules('oldpas',' ','trim');
			$this->form_validation->set_rules('password',' ','trim');
			$this->form_validation->set_rules('confpass',' ','trim');
			$this->form_validation->set_rules('wmid',' ','trim');
			$this->form_validation->set_rules('phones',' ','trim');
			$this->form_validation->set_rules('icq',' ','trim');
			$this->form_validation->set_rules('skype',' ','trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка. Неверно заполнены необходимые поля<br/>');
				redirect($this->uri->uri_string());
			else:
				if(!empty($_POST['oldpas']) && !empty($_POST['password']) && !empty($_POST['confpass'])):
					if(!$this->mdusers->user_exist('password',md5($_POST['oldpas']))):
						$this->session->set_userdata('msgr',' Не верный старый пароль!');
					elseif($_POST['password']!=$_POST['confpass']):
						$this->session->set_userdata('msgr',' Пароли не совпадают.');
					else:
						$this->mdusers->update_field($this->user['uid'],'password',md5($_POST['password']));
						$this->mdusers->update_field($this->user['uid'],'cryptpassword',$this->encrypt->encode($_POST['password']));
						$this->session->set_userdata('msgs',' Пароль успешно изменен');
						$this->session->set_userdata('logon',md5($this->user['ulogin'].md5($_POST['password'])));
					endif;
				endif;
				if(!isset($_POST['sendmail'])):
					$_POST['sendmail'] = 0;
				endif;
				unset($_POST['password']);unset($_POST['login']);
				$_POST['uid'] = $this->user['uid'];
				$result = $this->mdusers->update_record($_POST);
				if($result):
					$msgs = 'Личные данные успешно сохранены.<br/>'.$this->session->userdata('msgs');
					$this->session->set_userdata('msgs',$msgs);
				endif;
				redirect($this->uri->uri_string());
			endif;
		endif;
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['user']['signdate'] = $this->operation_date($pagevar['user']['signdate']);
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/admin-profile",$pagevar);
	}
	
	/******************************************************** events ******************************************************/
	
	public function actions_events(){
		
		$from = intval($this->uri->segment(5));
		$pagevar = array(
			'title'			=> 'Панель администрирования | Новости',
			'description'	=> '',
			'author'		=> '',
			'baseurl'		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'events'		=> $this->mdevents->read_records_limit(5,$from),
			'pages'			=> array(),
			'msgs'			=> $this->session->userdata('msgs'),
			'msgr'			=> $this->session->userdata('msgr'),
		);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$config['base_url'] 		= $pagevar['baseurl'].'admin-panel/actions/events/from/';
		$config['uri_segment'] 		= 5;
		$config['total_rows'] 		= $this->mdevents->count_records();
		$config['per_page'] 		= 5;
		$config['num_links'] 		= 4;
		$config['first_link']		= 'В начало';
		$config['last_link'] 		= 'В конец';
		$config['next_link'] 		= 'Далее &raquo;';
		$config['prev_link'] 		= '&laquo; Назад';
		$config['cur_tag_open']		= '<li class="active"><a href="#">';
		$config['cur_tag_close'] 	= '</a></li>';
		$config['full_tag_open'] 	= '<div class="pagination"><ul>';
		$config['full_tag_close'] 	= '</ul></div>';
		$config['first_tag_open'] 	= '<li>';
		$config['first_tag_close'] 	= '</li>';
		$config['last_tag_open'] 	= '<li>';
		$config['last_tag_close'] 	= '</li>';
		$config['next_tag_open'] 	= '<li>';
		$config['next_tag_close'] 	= '</li>';
		$config['prev_tag_open'] 	= '<li>';
		$config['prev_tag_close'] 	= '</li>';
		$config['num_tag_open'] 	= '<li>';
		$config['num_tag_close'] 	= '</li>';
		
		$this->pagination->initialize($config);
		$pagevar['pages'] = $this->pagination->create_links();
		
		for($i=0;$i<count($pagevar['events']);$i++):
			$pagevar['events'][$i]['date'] = $this->operation_date($pagevar['events'][$i]['date']);
		endfor;
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->session->set_userdata('backpath',$this->uri->uri_string());
		$this->load->view("admin_interface/control-events",$pagevar);
	}
	
	public function actions_events_add(){
		
		$pagevar = array(
			'title'			=> 'Панель администрирования | Добавление новости',
			'description'	=> '',
			'author'		=> '',
			'baseurl'		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'msgs'			=> $this->session->userdata('msgs'),
			'msgr'			=> $this->session->userdata('msgr'),
		);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('submit')):
			unset($_POST['submit']);
			$this->form_validation->set_rules('title',' ','required|trim');
			$this->form_validation->set_rules('text',' ','required|trim');
			$this->form_validation->set_rules('announcement',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка. Неверно заполнены необходимые поля<br/>');
				$this->actions_events_add();
				return FALSE;
			else:
				if($_FILES['image']['error'] != 4):
					$_POST['image'] = file_get_contents($_FILES['image']['tmp_name']);
				else:
					$_POST['image'] = FALSE;
				endif;
				$translit = $this->translite($_POST['title']);
				$result = $this->mdevents->insert_record($_POST,$translit);
				if($result):
					$this->session->set_userdata('msgs','Запись создана успешно.');
				endif;
				redirect($this->uri->uri_string());
			endif;
		endif;
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/control-add-events",$pagevar);
	}
	
	public function actions_events_edit(){
		
		$nid = $this->uri->segment(5);
		$pagevar = array(
			'title'			=> 'Панель администрирования | Редактирование новости',
			'description'	=> '',
			'author'		=> '',
			'baseurl'		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'event'			=> $this->mdevents->read_record($nid),
			'msgs'			=> $this->session->userdata('msgs'),
			'msgr'			=> $this->session->userdata('msgr'),
		);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('submit')):
			unset($_POST['submit']);
			$this->form_validation->set_rules('title',' ','required|trim');
			$this->form_validation->set_rules('content',' ','trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка. Неверно заполнены необходимые поля<br/>');
				$this->control_edit_events();
				return FALSE;
			else:
				if($_FILES['image']['error'] != 4):
					$_POST['image'] = file_get_contents($_FILES['image']['tmp_name']);
				endif;
				if(isset($_POST['noimage'])):
					$noimage = 1;
				else:
					$noimage = 0;
				endif;
				$translit = $this->translite($_POST['title']);
				$result = $this->mdevents->update_record($nid,$_POST,$translit,$noimage);
				if($result):
					$this->session->set_userdata('msgs','Запись сохранена успешно.');
				endif;
				redirect($this->session->userdata('backpath'));
			endif;
		endif;
		
		$pagevar['event'] = preg_replace('/\<br \/\>/','',$pagevar['event']);
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/control-edit-events",$pagevar);
	}
	
	public function actions_delete_events(){
		
		$nid = $this->uri->segment(6);
		if($nid):
			$result = $this->mdevents->delete_record($nid);
			if($result):
				$this->session->set_userdata('msgs','Запись удалена успешно.');
			else:
				$this->session->set_userdata('msgr','Запись не удалена.');
			endif;
			redirect($this->session->userdata('backpath'));
		else:
			show_404();
		endif;
	}
	
	/******************************************************** users ******************************************************/
	
	public function management_users(){
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Пользователи | ',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'users'			=> array(),
					'count'			=> 0,
					'pages'			=> array(),
					'cntunit'		=> array(),
					'managers'		=> $this->mdusers->read_users_by_type(2),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('eusubmit')):
			$_POST['eusubmit'] = NULL;
			$this->form_validation->set_rules('uid',' ','required|trim');
			$this->form_validation->set_rules('wmid',' ','required|trim');
			$this->form_validation->set_rules('balance',' ','required|trim');
			$this->form_validation->set_rules('type',' ','required|trim');
			$this->form_validation->set_rules('manager',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				$old_manager = $this->mdusers->read_field($_POST['uid'],'manager');
				$_POST['balance'] = preg_replace('/[-]+/','-',$_POST['balance']);
				/*if($_POST['balance']):
					$this->mdfillup->insert_record($_POST['uid'],$_POST['balance'],'Смена состояния баланса через Bystropost.ru',0,1);
				endif;*/
				$result = $this->mdusers->update_record($_POST);
				if($result):
					$this->session->set_userdata('msgs','Информация успешно сохранена.');
					if($_POST['type'] != 1):
						$this->mdusers->update_field($_POST['uid'],'manager',0);
						$_POST['manager'] = 0;
					endif;
					if($_POST['manager']):
						$platforms = $this->mdplatforms->read_managers_platform_online($_POST['uid']);
						for($i=0;$i<count($platforms);$i++):
							if($platforms[$i]['manager'] && ($platforms[$i]['manager'] != $_POST['manager'])):
								$text = 'Здравствуйте! С Ваc снята площадка '.$platforms[$i]['url'];
								$this->mdmessages->send_noreply_message($this->user['uid'],$platforms[$i]['manager'],1,2,$text);
							endif;
							if($platforms[$i]['manager'] != $_POST['manager']):
								$text = 'Здравствуйте! За Вами закреплена новая площадка '.$platforms[$i]['url'];
								$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['manager'],4,2,$text);
							endif;
						endfor;
						$this->mdplatforms->update_managers($_POST['uid'],$_POST['manager']);
						$this->session->set_userdata('msgs','Информация успешно сохранена.<br/><b>Внимание!</b> На аккаунт вебмастера назначен менеджер.');
					else:
						if($old_manager):
							$this->session->set_userdata('msgs','Информация успешно сохранена.<br/><b>Внимание!</b> С вебмастера снят менеджер. С площадок менеджер не снят.<br/>Переназначте менеджера для площадок вручную.');
						endif;
					endif;
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		if($this->input->post('mtsubmit')):
			$_POST['mtsubmit'] = NULL;
			$this->form_validation->set_rules('recipient',' ','required|trim');
			$this->form_validation->set_rules('text',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				$_POST['text'] = $this->replace_a_tag($_POST['text']);
				$id = $this->mdmessages->insert_record($this->user['uid'],$_POST['recipient'],$_POST['text']);
				if($id):
					if($this->mdusers->read_field($_POST['recipient'],'sendmail')):
						
						ob_start();
						?>
						<img src="<?=base_url();?>images/logo.png" alt="" />
						<p><strong>Здравствуйте, <?=$this->mdusers->read_field($_POST['recipient'],'fio');?></strong></p>
						<p>У Вас новое сообщение</p>
						<p>Что бы прочитать его войдите в <?=$this->link_cabinet($_POST['recipient']);?> и перейдите в раздел "Почта"</p>
						<p><br/><?=$this->sub_mailtext($_POST['text'],$_POST['recipient']);?><br/></p>
						<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p>
						<?
						$mailtext = ob_get_clean();
						
						$this->email->clear(TRUE);
						$config['smtp_host'] = 'localhost';
						$config['charset'] = 'utf-8';
						$config['wordwrap'] = TRUE;
						$config['mailtype'] = 'html';
						
						$this->email->initialize($config);
						$this->email->to($this->mdusers->read_field($_POST['recipient'],'login'));
						$this->email->from('admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления');
						$this->email->bcc('');
						$this->email->subject('Bystropost.ru - Почта. Новое сообщение');
						$this->email->message($mailtext);
						$this->email->send();
					endif;
					$this->session->set_userdata('msgs','Сообщение отправлено');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		$from = intval($this->uri->segment(6));
		switch ($this->uri->segment(4)):
			case 'webmasters' 	:	$pagevar['title'] .= 'Группа "Вебмастера"';
									$pagevar['users'] = $this->mdunion->read_users_group_webmasters(10,$from);
									$pagevar['count'] = $this->mdunion->count_users_group_webmasters();
									break;
			case 'optimizators' :	$pagevar['title'] .= 'Группа "Оптимизаторы"';
									$pagevar['users'] = $this->mdunion->read_users_group_optimizators(10,$from);
									$pagevar['count'] = $this->mdunion->count_users_group_optimizators();
									break;
			case 'managers' 	:	$pagevar['title'] .= 'Группа "Менеджеры"';
									$pagevar['users'] = $this->mdunion->read_users_group_manegers(10,$from);
									$pagevar['count'] = $this->mdunion->count_users_group_manegers();
									break;
			case 'admin' 		:	$pagevar['title'] .= 'Группа "Администраторы"';
									$pagevar['users'] = $this->mdunion->read_users_group_admin(10,$from);
									$pagevar['count'] = $this->mdunion->count_users_group_admin();
									break;
			default 			:	$pagevar['title'] .= 'Все группы';
									$pagevar['users'] = $this->mdunion->read_users_group_all(10,$from);
									$pagevar['count'] = $this->mdunion->count_users_group_all();
									break;
		endswitch;
		
		$config['base_url'] 		= $pagevar['baseurl'].'admin-panel/management/users/'.$this->uri->segment(4).'/from/';
		$config['uri_segment'] 		= 6;
		$config['total_rows'] 		= $pagevar['count']; 
		$config['per_page'] 		= 10;
		$config['num_links'] 		= 4;
		$config['first_link']		= 'В начало';
		$config['last_link'] 		= 'В конец';
		$config['next_link'] 		= 'Далее &raquo;';
		$config['prev_link'] 		= '&laquo; Назад';
		$config['cur_tag_open']		= '<li class="active"><a href="#">';
		$config['cur_tag_close'] 	= '</a></li>';
		$config['full_tag_open'] 	= '<div class="pagination"><ul>';
		$config['full_tag_close'] 	= '</ul></div>';
		$config['first_tag_open'] 	= '<li>';
		$config['first_tag_close'] 	= '</li>';
		$config['last_tag_open'] 	= '<li>';
		$config['last_tag_close'] 	= '</li>';
		$config['next_tag_open'] 	= '<li>';
		$config['next_tag_close'] 	= '</li>';
		$config['prev_tag_open'] 	= '<li>';
		$config['prev_tag_close'] 	= '</li>';
		$config['num_tag_open'] 	= '<li>';
		$config['num_tag_close'] 	= '</li>';
		
		$this->pagination->initialize($config);
		$pagevar['pages'] = $this->pagination->create_links();
		
		if($this->input->post('scsubmit')):
			unset($_POST['scsubmit']);
			$result = $this->mdusers->read_users($_POST['srusrid'],$_POST['srusrlogin']);
			$pagevar['title'] .= 'Поиск выполнен';
			$pagevar['users'] = $result;
			$pagevar['pages'] = NULL;
		endif;
		
		for($i=0;$i<count($pagevar['users']);$i++):
			$pagevar['users'][$i]['signdate'] = $this->operation_dot_date_not_time($pagevar['users'][$i]['signdate']);
			if($pagevar['users'][$i]['lastlogin'] != '0000-00-00'):
				$pagevar['users'][$i]['lastlogin'] = $this->operation_dot_date_not_time($pagevar['users'][$i]['lastlogin']);
			else:
				$pagevar['users'][$i]['lastlogin'] = '';
			endif;
			if($pagevar['users'][$i]['type'] == 1):
				if($pagevar['users'][$i]['manager']):
					$pagevar['users'][$i]['manfio'] = $this->mdusers->read_field($pagevar['users'][$i]['manager'],'fio');
					$pagevar['users'][$i]['manemail'] = $this->mdusers->read_field($pagevar['users'][$i]['manager'],'login');
				endif;
				$pagevar['users'][$i]['platforms'] = $this->mdplatforms->count_records_by_webmaster($pagevar['users'][$i]['id']);
				$pagevar['users'][$i]['webmarkets'] = $this->mdwebmarkets->count_records($pagevar['users'][$i]['remoteid']);
				$pagevar['users'][$i]['uporders'] = $this->mddelivesworks->count_records_by_webmaster_status($pagevar['users'][$i]['id'],0);
				$pagevar['users'][$i]['torders'] = $this->mddelivesworks->count_records_by_webmaster($pagevar['users'][$i]['id']);
				$pagevar['users'][$i]['pruporders'] = $this->mddelivesworks->sum_records_by_webmaster_status($pagevar['users'][$i]['id'],0);
				$pagevar['users'][$i]['prtorders'] = $this->mddelivesworks->sum_records_by_webmaster($pagevar['users'][$i]['id']);
				if(!$pagevar['users'][$i]['pruporders']):
					$pagevar['users'][$i]['pruporders'] = 0;
				endif;
				if(!$pagevar['users'][$i]['prtorders']):
					$pagevar['users'][$i]['prtorders'] = 0;
				endif;
			endif;
		endfor;
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->session->set_userdata('backpath',$this->uri->uri_string());
		$this->load->view("admin_interface/management-users",$pagevar);
	}
	
	public function search_users(){
		
		$statusval = array('status'=>FALSE,'retvalue'=>'');
		$search = $this->input->post('squery');
		if(!$search) show_404();
		$users = $this->mdusers->search_users($search);
		if($users):
			$statusval['retvalue'] = '<ul>';
			for($i=0;$i<count($users);$i++):
				$statusval['retvalue'] .= '<li class="usrorg" data-usrid="'.$users[$i]['id'].'">'.$users[$i]['login'].'</li>';
			endfor;
			$statusval['retvalue'] .= '</ul>';
			$statusval['status'] = TRUE;
		endif;
		echo json_encode($statusval);
	}
	
	public function management_users_profile(){
		
		$user = $this->uri->segment(6);
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Личный кабинет',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'user'			=> $this->mdusers->read_record($user),
					'cntunit'		=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('submit')):
			$_POST['submit'] = NULL;
			$this->form_validation->set_rules('fio',' ','required|trim');
			$this->form_validation->set_rules('oldpas',' ','trim');
			$this->form_validation->set_rules('password',' ','trim');
			$this->form_validation->set_rules('confpass',' ','trim');
			$this->form_validation->set_rules('wmid',' ','required|trim');
			$this->form_validation->set_rules('phones',' ','trim');
			$this->form_validation->set_rules('icq',' ','trim');
			$this->form_validation->set_rules('skype',' ','trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка. Неверно заполнены необходимые поля<br/>');
				redirect($this->uri->uri_string());
			else:
				if(!empty($_POST['oldpas']) && !empty($_POST['password']) && !empty($_POST['confpass'])):
					if(!$this->mdusers->user_exist('password',md5($_POST['oldpas']))):
						$this->session->set_userdata('msgr',' Не верный старый пароль!');
					elseif($_POST['password']!=$_POST['confpass']):
						$this->session->set_userdata('msgr',' Пароли не совпадают.');
					else:
						$this->mdusers->update_field($user,'password',md5($_POST['password']));
						$this->mdusers->update_field($user,'cryptpassword',$this->encrypt->encode($_POST['password']));
						$this->session->set_userdata('msgs',' Пароль успешно изменен');
					endif;
				endif;
				if(!isset($_POST['sendmail'])):
					$_POST['sendmail'] = 0;
				endif;
				if(!isset($_POST['antihold'])):
					$_POST['antihold'] = 0;
				endif;
				unset($_POST['password']);unset($_POST['login']);
				$_POST['uid'] = $user;
				$wmid = $this->mdusers->read_by_wmid($_POST['wmid']);
				if($wmid && $wmid != $user):
					$this->session->set_userdata('msgr','Ошибка. WMID уже зареристрирован!');
					redirect($this->uri->uri_string());
				endif;
				$result = $this->mdusers->update_record($_POST);
				if($result):
					if($_POST['antihold'] && $pagevar['user']['debetor']):
						$this->mdusers->update_field($user,'debetor',0);
						$remoteid = $this->mdusers->read_field($user,'remoteid');
						if($remoteid):
							$markets = $this->mdwebmarkets->read_records($remoteid);
							for($i=0;$i<count($markets);$i++):
								$param = 'accid='.$markets[$i]['id'].'&birzid='.$markets[$i]['market'].'&login='.$markets[$i]['login'].'&pass='.base64_encode($this->encrypt->decode($markets[$i]['cryptpassword'])).'&act=1';
								$this->API('UpdateAccount',$param);
							endfor;
						endif;
					endif;
					$msgs = 'Личные данные успешно сохранены.<br/>'.$this->session->userdata('msgs');
					$this->session->set_userdata('msgs',$msgs);
				endif;
				redirect($this->uri->uri_string());
			endif;
		endif;
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['user']['signdate'] = $this->operation_dot_date_not_time($pagevar['user']['signdate']);
		$pagevar['user']['oldpassword'] = $this->encrypt->decode($pagevar['user']['cryptpassword']);
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/admin-profile",$pagevar);
	}
	
	public function management_users_deleting(){
		
		$uid = $this->uri->segment(5);
		if($uid):
			$user = $this->mdusers->read_record($uid);
			$result = $this->mdusers->delete_record($uid);
			if($result):
				$this->mdmessages->delete_records_by_user($uid);
				$this->mdtickets->delete_records_by_user($uid);
				$this->mdtkmsgs->delete_records_by_user($uid);
				$this->mdplatforms->close_platform_by_user_delete($uid);
				ob_start();
				?>
				<img src="<?=base_url();?>images/logo.png" alt="" />
				<p><strong>Здравствуйте, <?=$user['fio'];?></strong></p>
				<p>Ваша учетная запись удалена Администратором</p>
				<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p>
				<?
				$mailtext = ob_get_clean();
				
				$this->email->clear(TRUE);
				$config['smtp_host'] = 'localhost';
				$config['charset'] = 'utf-8';
				$config['wordwrap'] = TRUE;
				$config['mailtype'] = 'html';
				
				$this->email->initialize($config);
				$this->email->to($user['login']);
				$this->email->from('admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления');
				$this->email->bcc('');
				$this->email->subject('Bystropost.ru - Учетная запись удалена');
				$this->email->message($mailtext);	
				$this->email->send();
				$this->session->set_userdata('msgs','Пользователь удален успешно.');
			else:
				$this->session->set_userdata('msgr','Пользователь не удален.');
			endif;
			redirect($_SERVER['HTTP_REFERER']);
		else:
			show_404();
		endif;
	}
	
	public function user_webmarkets_list(){
		
		$remoteid = $this->uri->segment(5);
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Список биржевых аккаунтов',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'accounts'		=> $this->mdunion->read_markets_by_webmaster($remoteid),
					'markets'		=> $this->mdmarkets->read_records(),
					'cntunit'		=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('smsubmit')):
			unset($_POST['smsubmit']);
			$this->form_validation->set_rules('mid',' ','required|trim');
			$this->form_validation->set_rules('market',' ','trim');
			$this->form_validation->set_rules('login',' ','required|trim');
			$this->form_validation->set_rules('password',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
				redirect($this->uri->uri_string());
			else:
				$account = $this->mdwebmarkets->read_record($_POST['mid']);
				$param = 'accid='.$_POST['mid'].'&birzid='.$account['market'].'&login='.$_POST['login'].'&pass='.base64_encode($_POST['password']).'&act=1';
				$this->API('UpdateAccount',$param);
				$this->mdwebmarkets->update_record($_POST['mid'],$remoteid,$_POST);
				$user = $this->mdusers->read_record_remote($remoteid);
				$this->mdmkplatform->update_records($user['id'],$account['login'],$account['market'],$account['password'],$_POST['password'],$_POST['login'],NULL);
				$this->session->set_userdata('msgs','Аккаунт успешно сохранен');
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		for($i=0;$i<count($pagevar['accounts']);$i++):
			$pagevar['accounts'][$i]['password'] = $this->encrypt->decode($pagevar['accounts'][$i]['cryptpassword']);
		endfor;
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/webmaster-webmarkets-list",$pagevar);
	}
	
	public function user_delete_markets(){
		
		$remoteid = $this->uri->segment(5);
		$mid = $this->uri->segment(8);
		if($mid):
			$info = $this->mdwebmarkets->read_record($mid);
			$result = $this->mdwebmarkets->delete_record($remoteid,$mid);
			if($result):
				$param = 'accid='.$info['id'].'&birzid='.$info['market'].'&login='.$info['login'].'&password='.base64_encode($this->encrypt->decode($info['cryptpassword'])).'&act=0';
				$this->API('UpdateAccount',$param);
				$user = $this->mdusers->read_record_remote($remoteid);
				$this->mdmkplatform->delete_records_by_webmarket($user['id'],$info['market'],$info['login'],$info['password']);
				$plmarkets = $this->mdunion->free_platforms($user['id']);
				for($i=0;$i<count($plmarkets);$i++):
					if(is_null($plmarkets[$i]['mkid'])):
						$param = 'siteid='.$plmarkets[$i]['remoteid'].'&value=1';
						$this->API('SetSiteActive',$param);
					endif;
				endfor;
				$this->session->set_userdata('msgs','Запись удалена успешно');
			else:
				$this->session->set_userdata('msgr','Запись не удалено');
			endif;
		endif;
		redirect($_SERVER['HTTP_REFERER']);
	}
	
	public function user_disabled_markets(){
		
		$remoteid = $this->uri->segment(5);
		$mid = $this->uri->segment(8);
		if($mid):
			$info = $this->mdwebmarkets->read_record($mid);
			$result = $this->mdwebmarkets->update_status($remoteid,$mid,0);
			if($result):
				$param = 'accid='.$info['id'].'&birzid='.$info['market'].'&login='.$info['login'].'&password='.base64_encode($this->encrypt->decode($info['cryptpassword'])).'&act=0';
				$this->API('UpdateAccount',$param);
				$user = $this->mdusers->read_record_remote($remoteid);
				$this->mdmkplatform->delete_records_by_webmarket($user['id'],$info['market'],$info['login'],$info['password']);
				$plmarkets = $this->mdunion->free_platforms($user['id']);
				for($i=0;$i<count($plmarkets);$i++):
					if(is_null($plmarkets[$i]['mkid'])):
						$param = 'siteid='.$plmarkets[$i]['remoteid'].'&value=1';
						$this->API('SetSiteActive',$param);
					endif;
				endfor;
				$this->session->set_userdata('msgs','Запись отключена успешно');
			else:
				$this->session->set_userdata('msgr','Запись не отключена');
			endif;
		endif;
		redirect($_SERVER['HTTP_REFERER']);
	}
	
	public function user_enabled_markets(){
		
		$remoteid = $this->uri->segment(5);
		$mid = $this->uri->segment(8);
		if($mid):
			$info = $this->mdwebmarkets->read_record($mid);
			$result = $this->mdwebmarkets->update_status($remoteid,$mid,1);
			if($result):
				$param = 'accid='.$info['id'].'&birzid='.$info['market'].'&login='.$info['login'].'&password='.base64_encode($this->encrypt->decode($info['cryptpassword'])).'&act=1';
				$this->API('UpdateAccount',$param);
				$this->session->set_userdata('msgs','Запись включена успешно');
			else:
				$this->session->set_userdata('msgr','Запись не включена');
			endif;
		endif;
		redirect($_SERVER['HTTP_REFERER']);
	}
	
	/******************************************************** platforms ******************************************************/
	
	public function search_platforms(){
		
		$statusval = array('status'=>FALSE,'retvalue'=>'');
		$search = $this->input->post('squery');
		if(!$search) show_404();
		$platforms = $this->mdplatforms->search_platforms($search);
		if($platforms):
			$statusval['retvalue'] = '<ul>';
			for($i=0;$i<count($platforms);$i++):
				$statusval['retvalue'] .= '<li class="plorg" data-plid="'.$platforms[$i]['id'].'">'.$platforms[$i]['url'].'</li>';
			endfor;
			$statusval['retvalue'] .= '</ul>';
			$statusval['status'] = TRUE;
		endif;
		echo json_encode($statusval);
	}
	
	public function user_platforms_list(){
		
		$user = $this->uri->segment(5);
		$utype = $this->mdusers->read_field($user,'type');
		if($utype != 1):
			redirect('admin-panel/management/users/all');
		endif;
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Список площадок',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'platforms'		=> $this->mdplatforms->read_records_by_webmaster($user),
					'cntunit'		=> array(),
					'managers'		=> $this->mdusers->read_users_by_type(2),
					'markets'		=> $this->mdunion->read_mkplatform_by_webmaster($user),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('epsubmit')):
			$_POST['epsubmit'] = NULL;
			$this->form_validation->set_rules('pid',' ','required|trim');
			$this->form_validation->set_rules('uid',' ','required|trim');
			$this->form_validation->set_rules('ccontext',' ','required|trim');
			$this->form_validation->set_rules('mcontext',' ','required|trim');
			$this->form_validation->set_rules('cnotice',' ','required|trim');
			$this->form_validation->set_rules('mnotice',' ','required|trim');
			$this->form_validation->set_rules('creview',' ','required|trim');
			$this->form_validation->set_rules('mreview',' ','required|trim');
			$this->form_validation->set_rules('cnews',' ','required|trim');
			$this->form_validation->set_rules('mnews',' ','required|trim');
			$this->form_validation->set_rules('manager',' ','required|trim');
			$this->form_validation->set_rules('clinkpic',' ','required|trim');
			$this->form_validation->set_rules('mlinkpic',' ','required|trim');
			$this->form_validation->set_rules('cpressrel',' ','required|trim');
			$this->form_validation->set_rules('mpressrel',' ','required|trim');
			$this->form_validation->set_rules('clinkarh',' ','required|trim');
			$this->form_validation->set_rules('mlinkarh',' ','required|trim');
			
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				$manager = $this->mdplatforms->read_field($_POST['pid'],'manager');
				$remote_id = $this->mdplatforms->read_field($_POST['pid'],'remoteid');
				if(!isset($_POST['locked'])):
					$_POST['locked'] = 0;
				endif;
				if(isset($_POST['status']) && !is_null($_POST['status'])):
					$this->mdplatforms->update_field($_POST['pid'],'status',1);
					$param = 'siteid='.$remote_id.'&value=0';
					$res =  $this->API('SetSiteActive',$param);
					$this->session->set_userdata('msgs','Площадка активирована!');
				endif;
				if(isset($_POST['noticpr']) && !is_null($_POST['noticpr'])):
					$this->mdplatforms->update_field($_POST['pid'],'noticpr',1);
				else:
					$this->mdplatforms->update_field($_POST['pid'],'noticpr',0);
				endif;
				$prevman = $this->mdplatforms->read_field($_POST['pid'],'manager');
				$prevlock = $this->mdplatforms->read_field($_POST['pid'],'locked');
				$result1 = $this->mdplatforms->update_lock($_POST['pid'],$_POST['uid'],$_POST['locked']);
				$result2 = $this->mdplatforms->update_manager($_POST['pid'],$_POST['uid'],$_POST['manager']);
				$result3 = $this->mdplatforms->update_price($_POST['pid'],$_POST['uid'],$_POST);
				if($result1 || $result2 || $result3):
					$platform = $this->mdplatforms->read_field($_POST['pid'],'url');
					if(!$prevman && $_POST['manager']):
						$text = 'Здравствуйте! Ваша площадка '.$platform.' принята к работе';
						$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['uid'],4,1,$text);
						$text = 'Здравствуйте! За Вами закреплена площадка '.$platform;
						$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['manager'],4,2,$text);
						if($this->mdusers->read_field($_POST['uid'],'sendmail')):
							ob_start();
							?>
							<img src="<?=base_url();?>images/logo.png" alt="" />
							<p><strong>Здравствуйте, <?=$this->mdusers->read_field($_POST['uid'],'fio');?></strong></p>
							<p>Ваша площадка <?=$platform;?> принята к работе</p>
							<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p>
							<?
							$mailtext = ob_get_clean();
							
							$this->email->clear(TRUE);
							$config['smtp_host'] = 'localhost';
							$config['charset'] = 'utf-8';
							$config['wordwrap'] = TRUE;
							$config['mailtype'] = 'html';
							
							$this->email->initialize($config);
							$this->email->to($this->mdusers->read_field($_POST['uid'],'login'));
							$this->email->from('admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления');
							$this->email->bcc('');
							$this->email->subject('Bystropost.ru - Площадка в работе');
							$this->email->message($mailtext);	
							$this->email->send();
						endif;
						if($this->mdusers->read_field($_POST['manager'],'sendmail')):
							ob_start();
							?>
							<img src="<?=base_url();?>images/logo.png" alt="" />
							<p><strong>Здравствуйте, <?=$this->mdusers->read_field($_POST['manager'],'fio');?></strong></p>
							<p>За Вами закреплена площадка  <?=$platform;?></p>
							<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p>
							<?
							$mailtext = ob_get_clean();
							
							$this->email->clear(TRUE);
							$config['smtp_host'] = 'localhost';
							$config['charset'] = 'utf-8';
							$config['wordwrap'] = TRUE;
							$config['mailtype'] = 'html';
							
							$this->email->initialize($config);
							$this->email->to($this->mdusers->read_field($_POST['manager'],'login'));
							$this->email->from('admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления');
							$this->email->bcc('');
							$this->email->subject('Bystropost.ru - Новая площадка');
							$this->email->message($mailtext);	
							$this->email->send();
						endif;
					elseif($prevman && !$_POST['manager']):
						$text = 'Здравствуйте! Ваша площадка '.$platform.' снята с работы';
						$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['uid'],1,1,$text);
						$text = 'Здравствуйте! С Ваc снята площадка '.$platform;
						$this->mdmessages->send_noreply_message($this->user['uid'],$prevman,1,2,$text);
						if($this->mdusers->read_field($_POST['uid'],'sendmail')):
							//Высылать письмо-уведомление
						endif;
						if($this->mdusers->read_field($_POST['manager'],'sendmail')):
							//Высылать письмо-уведомление
						endif;
					elseif($prevman != $_POST['manager']):
						$text = 'Здравствуйте! За Вами закреплена новая площадка '.$platform;
						$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['manager'],4,2,$text);
						$text = 'Здравствуйте! С Ваc снята площадка '.$platform;
						$this->mdmessages->send_noreply_message($this->user['uid'],$prevman,1,2,$text);
						
						$this->mdtickets->change_sender_recipient_by_new_manager($_POST['manager'],$prevman,$_POST['pid']);
						$this->mddelivesworks->change_managers($_POST['manager'],$prevman,$_POST['pid']);
						
						if($this->mdusers->read_field($prevman,'sendmail')):
							//Высылать письмо-уведомление
						endif;
						if($this->mdusers->read_field($_POST['manager'],'sendmail')):
							//Высылать письмо-уведомление
						endif;
					endif;
					if(!$prevlock && $_POST['locked']):
						$text = 'Здравствуйте! Ваша площадка '.$platform.' заблокирована администратором';
						$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['uid'],1,1,$text);
						if($_POST['manager']):
							$text = 'Здравствуйте! Закреплення за Вами площадка '.$platform.' заблокирована администратором';
							$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['manager'],1,2,$text);
							if($this->mdusers->read_field($_POST['manager'],'sendmail')):
								//Высылать письмо-уведомление
							endif;
						endif;
						if($manager == 2 && $remote_id):
							$param = 'siteid='.$remote_id.'&value=1';
							$res = $this->API('SetSiteActive',$param);
						endif;
						if($this->mdusers->read_field($_POST['uid'],'sendmail')):
							//Высылать письмо-уведомление
						endif;
					elseif($prevlock && !$_POST['locked']):
						$text = 'Здравствуйте! Ваша площадка '.$platform.' разблокирована администратором';
						$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['uid'],4,1,$text);
						if($_POST['manager']):
							$text = 'Здравствуйте! Закреплення за Вами площадка '.$platform.' разблокирована администратором';
							$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['manager'],4,2,$text);
							if($this->mdusers->read_field($_POST['manager'],'sendmail')):
								//Высылать письмо-уведомление
							endif;
						endif;
						if($manager == 2 && $remote_id):
							$param = 'siteid='.$remote_id.'&value=0';
							$res = $this->API('SetSiteActive',$param);
						endif;
						if($this->mdusers->read_field($_POST['uid'],'sendmail')):
							//Высылать письмо-уведомление
						endif;
					endif;
					$this->session->set_userdata('msgs','Информация успешно сохранена.');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		$ufio = $this->mdusers->read_field($user,'fio');
		$ulogin = $this->mdusers->read_field($user,'login');
		for($i=0;$i<count($pagevar['platforms']);$i++):
			$pagevar['platforms'][$i]['date'] = $this->operation_dot_date($pagevar['platforms'][$i]['date']);
			$pagevar['platforms'][$i]['fio'] = $ufio;
			$pagevar['platforms'][$i]['login'] = $ulogin;
			$pagevar['platforms'][$i]['uid'] = $user;
			if($pagevar['platforms'][$i]['manager']):
				$pagevar['platforms'][$i]['manfio'] = $this->mdusers->read_field($pagevar['platforms'][$i]['manager'],'fio');
				$pagevar['platforms'][$i]['manemail'] = $this->mdusers->read_field($pagevar['platforms'][$i]['manager'],'login');
			else:
				$pagevar['platforms'][$i]['manfio'] = '<font style="color:#ff0000;">Менеджер не закреплен</font>';
				$pagevar['platforms'][$i]['manemail'] = '';
			endif;
			$pagevar['platforms'][$i]['uporders'] = $this->mddelivesworks->count_records_by_platform_status($pagevar['platforms'][$i]['id'],0);
			$pagevar['platforms'][$i]['torders'] = $this->mddelivesworks->count_records_by_platform($pagevar['platforms'][$i]['id']);
		endfor;
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/webmaster-platforms-list",$pagevar);
	}
	
	public function management_platforms(){
		
		$from = intval($this->uri->segment(5));
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Площадки',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'platforms'		=> $this->mdunion->read_platforms_by_owners_pages(5,$from),
					'count'			=> $this->mdunion->count_platforms_by_owners(),
					'pages'			=> array(),
					'cntunit'		=> array(),
					'managers'		=> $this->mdusers->read_users_by_type(2),
					'allplatforms'	=> $this->mdplatforms->count_all(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('mtsubmit')):
			$_POST['mtsubmit'] = NULL;
			$this->form_validation->set_rules('recipient',' ','required|trim');
			$this->form_validation->set_rules('text',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				$_POST['text'] = $this->replace_a_tag($_POST['text']);
				$id = $this->mdmessages->insert_record($this->user['uid'],$_POST['recipient'],$_POST['text']);
				if($id):
					$this->session->set_userdata('msgs','Сообщение отправлено');
				endif;
				if(isset($_POST['sendmail'])):
					ob_start();
					?>
					<img src="<?=base_url();?>images/logo.png" alt="" />
					<p><strong>Здравствуйте, <?=$this->mdusers->read_field($_POST['recipient'],'fio');?></strong></p>
					<p>У Вас новое сообщение</p>
					<p>Что бы прочитать его войдите в <?=$this->link_cabinet($_POST['recipient']);?> и перейдите в раздел "Почта"</p>
					<p><br/><?=$this->sub_mailtext($_POST['text'],$_POST['recipient']);?><br/></p>
					<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p>
					<?
					$mailtext = ob_get_clean();
					
					$this->email->clear(TRUE);
					$config['smtp_host'] = 'localhost';
					$config['charset'] = 'utf-8';
					$config['wordwrap'] = TRUE;
					$config['mailtype'] = 'html';
					
					$this->email->initialize($config);
					$this->email->to($this->mdusers->read_field($_POST['recipient'],'login'));
					$this->email->from('admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления');
					$this->email->bcc('');
					$this->email->subject('Bystropost.ru - Почта. Новое сообщение');
					$this->email->message($mailtext);
					$this->email->send();
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		if($this->input->post('epsubmit')):
			$_POST['epsubmit'] = NULL;
			$this->form_validation->set_rules('pid',' ','required|trim');
			$this->form_validation->set_rules('uid',' ','required|trim');
			$this->form_validation->set_rules('ccontext',' ','required|trim');
			$this->form_validation->set_rules('mcontext',' ','required|trim');
			$this->form_validation->set_rules('cnotice',' ','required|trim');
			$this->form_validation->set_rules('mnotice',' ','required|trim');
			$this->form_validation->set_rules('creview',' ','required|trim');
			$this->form_validation->set_rules('mreview',' ','required|trim');
			$this->form_validation->set_rules('cnews',' ','required|trim');
			$this->form_validation->set_rules('mnews',' ','required|trim');
			$this->form_validation->set_rules('manager',' ','required|trim');
			$this->form_validation->set_rules('clinkpic',' ','required|trim');
			$this->form_validation->set_rules('mlinkpic',' ','required|trim');
			$this->form_validation->set_rules('cpressrel',' ','required|trim');
			$this->form_validation->set_rules('mpressrel',' ','required|trim');
			$this->form_validation->set_rules('clinkarh',' ','required|trim');
			$this->form_validation->set_rules('mlinkarh',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				$manager = $this->mdplatforms->read_field($_POST['pid'],'manager');
				$remote_id = $this->mdplatforms->read_field($_POST['pid'],'remoteid');
				if(!isset($_POST['locked'])):
					$_POST['locked'] = 0;
				endif;
				if(isset($_POST['status']) && !is_null($_POST['status'])):
					$this->mdplatforms->update_field($_POST['pid'],'status',1);
					$param = 'siteid='.$remote_id.'&value=0';
					$res =  $this->API('SetSiteActive',$param);
					$this->session->set_userdata('msgs','Площадка активирована!');
				endif;
				if(isset($_POST['noticpr']) && !is_null($_POST['noticpr'])):
					$this->mdplatforms->update_field($_POST['pid'],'noticpr',1);
				else:
					$this->mdplatforms->update_field($_POST['pid'],'noticpr',0);
				endif;
				$prevman = $this->mdplatforms->read_field($_POST['pid'],'manager');
				$prevlock = $this->mdplatforms->read_field($_POST['pid'],'locked');
				$result1 = $this->mdplatforms->update_lock($_POST['pid'],$_POST['uid'],$_POST['locked']);
				$result2 = $this->mdplatforms->update_manager($_POST['pid'],$_POST['uid'],$_POST['manager']);
				$result3 = $this->mdplatforms->update_price($_POST['pid'],$_POST['uid'],$_POST);
				if($result1 || $result2 || $result3):
					$platform = $this->mdplatforms->read_field($_POST['pid'],'url');
					if(!$prevman && $_POST['manager']):
						$text = 'Здравствуйте! Ваша площадка '.$platform.' принята к работе';
						$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['uid'],4,1,$text);
						$text = 'Здравствуйте! За Вами закреплена площадка '.$platform;
						$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['manager'],4,2,$text);
						if($this->mdusers->read_field($_POST['uid'],'sendmail')):
							ob_start();
							?>
							<img src="<?=base_url();?>images/logo.png" alt="" />
							<p><strong>Здравствуйте, <?=$this->mdusers->read_field($_POST['uid'],'fio');?></strong></p>
							<p>Ваша площадка <?=$platform;?> принята к работе</p>
							<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p>
							<?
							$mailtext = ob_get_clean();
							
							$this->email->clear(TRUE);
							$config['smtp_host'] = 'localhost';
							$config['charset'] = 'utf-8';
							$config['wordwrap'] = TRUE;
							$config['mailtype'] = 'html';
							
							$this->email->initialize($config);
							$this->email->to($this->mdusers->read_field($_POST['uid'],'login'));
							$this->email->from('admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления');
							$this->email->bcc('');
							$this->email->subject('Bystropost.ru - Площадка в работе');
							$this->email->message($mailtext);	
							$this->email->send();
						endif;
						if($this->mdusers->read_field($_POST['manager'],'sendmail')):
							ob_start();
							?>
							<img src="<?=base_url();?>images/logo.png" alt="" />
							<p><strong>Здравствуйте, <?=$this->mdusers->read_field($_POST['manager'],'fio');?></strong></p>
							<p>За Вами закреплена площадка  <?=$platform;?></p>
							
							<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p>
							<?
							$mailtext = ob_get_clean();
							
							$this->email->clear(TRUE);
							$config['smtp_host'] = 'localhost';
							$config['charset'] = 'utf-8';
							$config['wordwrap'] = TRUE;
							$config['mailtype'] = 'html';
							
							$this->email->initialize($config);
							$this->email->to($this->mdusers->read_field($_POST['manager'],'login'));
							$this->email->from('admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления');
							$this->email->bcc('');
							$this->email->subject('Bystropost.ru - Новая площадка');
							$this->email->message($mailtext);	
							$this->email->send();
						endif;
					elseif($prevman && !$_POST['manager']):
						$text = 'Здравствуйте! Ваша площадка '.$platform.' снята с работы';
						$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['uid'],1,1,$text);
						$text = 'Здравствуйте! С Ваc снята площадка '.$platform;
						$this->mdmessages->send_noreply_message($this->user['uid'],$prevman,1,2,$text);
						if($this->mdusers->read_field($_POST['uid'],'sendmail')):
							//Высылать письмо-уведомление
						endif;
						if($this->mdusers->read_field($_POST['manager'],'sendmail')):
							//Высылать письмо-уведомление
						endif;
					elseif($prevman != $_POST['manager']):
						$text = 'Здравствуйте! За Вами закреплена новая площадка '.$platform;
						$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['manager'],4,2,$text);
						$text = 'Здравствуйте! С Ваc снята площадка '.$platform;
						$this->mdmessages->send_noreply_message($this->user['uid'],$prevman,1,2,$text);
						
						$this->mdtickets->change_sender_recipient_by_new_manager($_POST['manager'],$prevman,$_POST['pid']);
						$this->mddelivesworks->change_managers($_POST['manager'],$prevman,$_POST['pid']);
						
						if($this->mdusers->read_field($prevman,'sendmail')):
							//Высылать письмо-уведомление
						endif;
						if($this->mdusers->read_field($_POST['manager'],'sendmail')):
							//Высылать письмо-уведомление
						endif;
					endif;
					if(!$prevlock && $_POST['locked']):
						$text = 'Здравствуйте! Ваша площадка '.$platform.' заблокирована администратором';
						$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['uid'],1,1,$text);
						if($_POST['manager']):
							$text = 'Здравствуйте! Закреплення за Вами площадка '.$platform.' заблокирована администратором';
							$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['manager'],1,2,$text);
							if($this->mdusers->read_field($_POST['manager'],'sendmail')):
								//Высылать письмо-уведомление
							endif;
						endif;
						
						if($manager == 2 && $remote_id):
							$param = 'siteid='.$remote_id.'&value=1';
							$res = $this->API('SetSiteActive',$param);
						endif;
						if($this->mdusers->read_field($_POST['uid'],'sendmail')):
							//Высылать письмо-уведомление
						endif;
					elseif($prevlock && !$_POST['locked']):
						$text = 'Здравствуйте! Ваша площадка '.$platform.' разблокирована администратором';
						$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['uid'],4,1,$text);
						if($_POST['manager']):
							$text = 'Здравствуйте! Закреплення за Вами площадка '.$platform.' разблокирована администратором';
							$this->mdmessages->send_noreply_message($this->user['uid'],$_POST['manager'],4,2,$text);
							if($this->mdusers->read_field($_POST['manager'],'sendmail')):
								//Высылать письмо-уведомление
							endif;
						endif;
						if($manager == 2 && $remote_id):
							$param = 'siteid='.$remote_id.'&value=0';
							$res = $this->API('SetSiteActive',$param);
						endif;
						if($this->mdusers->read_field($_POST['uid'],'sendmail')):
							//Высылать письмо-уведомление
						endif;
					endif;
					$this->session->set_userdata('msgs','Информация успешно сохранена.');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		$config['base_url'] 		= $pagevar['baseurl'].'admin-panel/management/platforms/from/';
		$config['uri_segment'] 		= 5;
		$config['total_rows'] 		= $pagevar['count']; 
		$config['per_page'] 		= 5;
		$config['num_links'] 		= 4;
		$config['first_link']		= 'В начало';
		$config['last_link'] 		= 'В конец';
		$config['next_link'] 		= 'Далее &raquo;';
		$config['prev_link'] 		= '&laquo; Назад';
		$config['cur_tag_open']		= '<li class="active"><a href="#">';
		$config['cur_tag_close'] 	= '</a></li>';
		$config['full_tag_open'] 	= '<div class="pagination"><ul>';
		$config['full_tag_close'] 	= '</ul></div>';
		$config['first_tag_open'] 	= '<li>';
		$config['first_tag_close'] 	= '</li>';
		$config['last_tag_open'] 	= '<li>';
		$config['last_tag_close'] 	= '</li>';
		$config['next_tag_open'] 	= '<li>';
		$config['next_tag_close'] 	= '</li>';
		$config['prev_tag_open'] 	= '<li>';
		$config['prev_tag_close'] 	= '</li>';
		$config['num_tag_open'] 	= '<li>';
		$config['num_tag_close'] 	= '</li>';
		
		$this->pagination->initialize($config);
		$pagevar['pages'] = $this->pagination->create_links();
		
		if($this->input->post('scsubmit')):
			unset($_POST['scsubmit']);
			$result = $this->mdunion->read_platform($_POST['srplid'],$_POST['srplurl']);
			$pagevar['title'] .= 'Администрирование | Площадки | Поиск выполнен';
			$pagevar['platforms'] = $result;
			$pagevar['pages'] = NULL;
		endif;
		
		for($i=0;$i<count($pagevar['platforms']);$i++):
			$pagevar['platforms'][$i]['markets'] = $this->mdmkplatform->read_records_platform($pagevar['platforms'][$i]['id']);
			$pagevar['platforms'][$i]['date'] = $this->operation_dot_date($pagevar['platforms'][$i]['date']);
			if(empty($pagevar['platforms'][$i]['fio'])):
				$pagevar['platforms'][$i]['fio'] = '';
				$pagevar['platforms'][$i]['login'] = '<font style="color:#ff0000;">Владелец не определен</font>';
			endif;
			if($pagevar['platforms'][$i]['manager']):
				$pagevar['platforms'][$i]['manfio'] = $this->mdusers->read_field($pagevar['platforms'][$i]['manager'],'fio');
				$pagevar['platforms'][$i]['manemail'] = $this->mdusers->read_field($pagevar['platforms'][$i]['manager'],'login');
			else:
				$pagevar['platforms'][$i]['manfio'] = '<font style="color:#ff0000;">Менеджер не закреплен</font>';
				$pagevar['platforms'][$i]['manemail'] = '';
			endif;
			$pagevar['platforms'][$i]['uporders'] = $this->mddelivesworks->count_records_by_platform_status($pagevar['platforms'][$i]['id'],0);
			$pagevar['platforms'][$i]['torders'] = $this->mddelivesworks->count_records_by_platform($pagevar['platforms'][$i]['id']);
		endfor;
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->session->set_userdata('backpath',$this->uri->uri_string());
		$this->load->view("admin_interface/management-platforms",$pagevar);
	}

	public function management_delete_platform(){
		
		$pid = $this->uri->segment(6);
		if($pid):
			$info = $this->mdplatforms->read_record($pid);
			$result = $this->mdplatforms->delete_record($pid);
			if($result):
				$text = 'Площадка '.$info['url'].'. Удалена администратором';
				if($info['webmaster']):
					$this->mdmkplatform->delete_records_by_platform($pid,$info['webmaster']);
					$this->mddelivesworks->delete_records_by_platform($pid,$info['webmaster']);
					$this->mdattachedservices->delete_records_by_platform($pid,$info['webmaster']);
					$this->mdmessages->send_noreply_message($this->user['uid'],$info['webmaster'],1,1,$text);
					ob_start();
					?>
					<img src="<?=base_url();?>images/logo.png" alt="" />
					<p><strong>Здравствуйте, <?=$this->mdusers->read_field($info['webmaster'],'fio');?></strong></p>
					<p>Ваша площадка <?=$info['url'];?> удалена администратором</p>
					<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p>
					<?
					$mailtext = ob_get_clean();
					
					$this->email->clear(TRUE);
					$config['smtp_host'] = 'localhost';
					$config['charset'] = 'utf-8';
					$config['wordwrap'] = TRUE;
					$config['mailtype'] = 'html';
					
					$this->email->initialize($config);
					$this->email->to($this->mdusers->read_field($info['webmaster'],'login'));
					$this->email->from('admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления');
					$this->email->bcc('');
					$this->email->subject('Bystropost.ru - Площадка удалена');
					$this->email->message($mailtext);	
					$this->email->send();
				endif;
				if($info['manager']):
					$this->mdmessages->send_noreply_message($this->user['uid'],$info['manager'],1,2,$text);
				endif;
				$this->session->set_userdata('msgs','Площадка удалена успешно.');
				if($info['manager'] == 2 && $info['remoteid']):
					$param = 'siteid='.$info['remoteid'].'&value=1';
					$res = $this->API('SetSiteActive',$param);
				endif;
			else:
				$this->session->set_userdata('msgr','Площадка не удалена.');
			endif;
			redirect($_SERVER['HTTP_REFERER']);
		else:
			show_404();
		endif;
	}
	
	public function control_edit_platform(){
		
		$platform = $this->uri->segment(5);
		if(!$platform):
			redirect('admin-panel/management/platforms');
		endif;
		$webmaster = $this->mdplatforms->read_field($platform,'webmaster');
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Кабинет Вебмастера | Площадки | Редактирование площадки',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'platform'		=> $this->mdplatforms->read_record($platform),
					'markets'		=> $this->mdmarkets->read_records(),
					'thematic'		=> $this->mdthematic->read_records(),
					'cms'			=> $this->mdcms->read_records(),
					'mymarkets'		=> $this->mdmkplatform->read_records_by_platform($platform,$webmaster),
					'services'		=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$pagevar['userinfo']['remote'] = FALSE;
		
		$attached = $this->mdunion->services_attached_list($webmaster);
		for($i=0;$i<count($attached);$i++):
			$pagevar['services'][$i] = $this->mdunion->read_srvvalue_service_platform($attached[$i]['service'],$platform,$webmaster);
		endfor;
		if($this->input->post('submit')):
			$_POST['submit'] = NULL;
			$this->form_validation->set_rules('url',' ','required|trim');
			$this->form_validation->set_rules('subject',' ','required|trim');
			$this->form_validation->set_rules('cms',' ','required|trim');
			$this->form_validation->set_rules('adminpanel',' ','required|trim');
			$this->form_validation->set_rules('aplogin',' ','required|trim');
			$this->form_validation->set_rules('appassword',' ','required|trim');
			$this->form_validation->set_rules('tematcustom',' ','trim');
			$this->form_validation->set_rules('reviews',' ','trim');
			$this->form_validation->set_rules('thematically',' ','trim');
			$this->form_validation->set_rules('illegal',' ','trim');
			$this->form_validation->set_rules('imgstatus',' ','trim');
			$this->form_validation->set_rules('imgwidth',' ','trim');
			$this->form_validation->set_rules('imgheight',' ','trim');
			$this->form_validation->set_rules('imgpos',' ','trim');
			$this->form_validation->set_rules('requests',' ','trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
				redirect($this->uri->uri_string());
			else:
				if($_POST['imgwidth'] && $_POST['imgheight']):
					$_POST['imgstatus'] = 1;
				else:
					$_POST['imgstatus'] = 0;
					$_POST['imgwidth'] = $_POST['imgheight'] = '';
					$_POST['imgpos'] = 'left';
				endif;
				$result = $this->mdplatforms->update_record($platform,$webmaster,$_POST);
				if($pagevar['platform']['manager']):
					/********************************************************************/
					if($pagevar['platform']['manager'] == 2):
						$new_platform = $this->mdplatforms->read_record($platform);
						if($new_platform['remoteid']):
							$pl_data = array();
							$marketslist = array();
							if(count($_POST['markets']) > 0):
								for($i=0,$j=0;$i<count($_POST['markets']);$i+=4):
									if(empty($_POST['markets'][$i+1]) || empty($_POST['markets'][$i+2])) continue;
									$marketslist[$j]['mkid'] 	= $_POST['markets'][$i];
									$marketslist[$j]['mkpub'] 	= $_POST['markets'][$i+3];
									$j++;
								endfor;
							endif;
							$pl_data['adminurl'] = $new_platform['adminpanel'];
							$pl_data['cms'] = $new_platform['cms'];
							$pl_data['cms_login'] = $new_platform['aplogin'];
							$pl_data['cms_pass'] = $new_platform['appassword'];
							$pl_data['tematic'] = $new_platform['subject'];
							$pl_data['tematcustom'] = $new_platform['tematcustom'];
							$pl_data['filter'] = $new_platform['illegal'];
							$pl_data['subjects'] = $new_platform['thematically'];
							$pl_data['review'] = $new_platform['reviews'];
							$pl_data['param'] = array();
							$pl_data['param']['image'] = array();
							$pl_data['param']['image']['status'] = $new_platform['imgstatus'];
							$pl_data['param']['image']['imgwidth'] = $new_platform['imgwidth'];
							$pl_data['param']['image']['imgheight'] = $new_platform['imgheight'];
							$pl_data['param']['image']['imgpos'] = $new_platform['imgpos'];
							if(count($marketslist) > 0):
								for($i=0;$i<count($marketslist);$i++):
									$pl_data['param']['category'][$marketslist[$i]['mkid']] = $marketslist[$i]['mkpub'];
								endfor;
							else:
								$pl_data['param']['category'] = array();
							endif;
							$pl_data['info'] = $new_platform['requests'];
							$pl_data['size'] = 0;
							$param = 'siteid='.$new_platform['remoteid'].'&conf='.base64_encode(json_encode($pl_data));
							$res = $this->API('UpdateSiteOptions',$param);
							/*if(!$pagevar['platform']['status']):
								$this->mdplatforms->update_field($platform,'status',1);
								$param = 'siteid='.$pagevar['platform']['remoteid'].'&value=0';
								$this->API('SetSiteActive',$param);
							endif;*/
						endif;
					endif;
					/********************************************************************/
					if($result):
						$text = "Информация о площадке ".$pagevar['platform']['url']." изменена.<br/>Проверьте свой E-mail что бы увидеть изменения";
						$this->mdmessages->send_noreply_message($this->user['uid'],$pagevar['platform']['manager'],2,2,$text);
						ob_start();?>
						<img src="<?=base_url();?>images/logo.png" alt="" />
						<p><strong>Здравствуйте, <?=$this->mdusers->read_field($pagevar['platform']['manager'],'fio');?></strong></p>
						<p>Вебмастер изменил информацию о площадке: <?=$this->mdplatforms->read_field($platform,'url');?><br/>
						Что изменилось (Было - Сейчас):</p>
						<p>URL: <?=$pagevar['platform']['url'].' - '.$_POST['url'];?><br/>
						Тематика: <?=$pagevar['platform']['subject'].' - '.$_POST['subject'];?><br/>
						CMS: <?=$pagevar['platform']['cms'].' - '.$_POST['cms'];?><br/>
						URL админки: <?=$pagevar['platform']['adminpanel'].' - '.$_POST['adminpanel'];?><br/>
						Логин к админке: <?=$pagevar['platform']['aplogin'].' - '.$_POST['aplogin'];?><br/>
						Пароль к админке: <?=$pagevar['platform']['appassword'].' - '.$_POST['appassword'];?><br/>
						Уточнение тематики: <?=$pagevar['platform']['tematcustom'].' - '.$_POST['tematcustom'];?><br/>
						Обзоры: <?=($pagevar['platform']['reviews'] == 1)?'да':'нет';?> - <?=($_POST['reviews'] == 1)?'да':'нет';?><br/>
						Тематичность: <?=($pagevar['platform']['thematically'] == 1)?'да':'нет';?> - <?=($_POST['thematically'] == 1)?'да':'нет';?><br/>
						Размещать задания которые противоречат законам РФ: <?=($pagevar['platform']['illegal'] == 1)?'Да, размещать':'Нет, не размещать';?> - <?=($_POST['illegal'] == 1)?'Да, размещать':'Нет, не размещать';?></p>
						<p>Критерии к публикации:<br/><br/>
							Ширина изображения:<?=$pagevar['platform']['imgwidth'].' - '.$_POST['imgwidth'];?>
							Высота изображения:<?=$pagevar['platform']['imgheight'].' - '.$_POST['imgheight'];?>
							Позиция изображения:<?=$pagevar['platform']['imgpos'].' - '.$_POST['imgpos'];?>
						</p>
						<p>Пожелания :<br/> <br/><?=$pagevar['platform']['requests'].'<br/><br/>'.$_POST['requests'];?></p>
						<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p>
						<?
						$mailtext = ob_get_clean();
						
						$this->email->clear(TRUE);
						$config['smtp_host'] = 'localhost';
						$config['charset'] = 'utf-8';
						$config['wordwrap'] = TRUE;
						$config['mailtype'] = 'html';
						
						$this->email->initialize($config);
						$this->email->to($this->mdusers->read_field($pagevar['platform']['manager'],'login'));
						$this->email->from('admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления');
						$this->email->bcc('');
						$this->email->subject('Bystropost.ru - Изменения по площадке.');
						$this->email->message($mailtext);	
						$this->email->send();
						$this->mdlog->insert_record($this->user['uid'],'Событие №16: Состояние площадки - изменена');
						$this->session->set_userdata('msgs','Платформа успешно сохранена.');
					endif;
				endif;
				if(isset($_POST['markets'])):
					$cntmarkets = count($_POST['markets']);
					$marketslist = array();
					if($cntmarkets > 0):
						for($i=0,$j=0;$i<$cntmarkets;$i+=4):
							if(empty($_POST['markets'][$i+1]) || empty($_POST['markets'][$i+2])) continue;
							$marketslist[$j]['mkid'] 	= $_POST['markets'][$i];
							$marketslist[$j]['mklogin'] = $_POST['markets'][$i+1];
							$marketslist[$j]['mkpass'] 	= $_POST['markets'][$i+2];
							$marketslist[$j]['mkpub'] 	= $_POST['markets'][$i+3];
							$j++;
						endfor;
					endif;
					if(count($marketslist)):
						$this->mdmkplatform->delete_records_by_platform($platform,$webmaster);
						$this->mdmkplatform->group_insert($webmaster,$platform,$marketslist);
					endif;
				endif;
			endif;
			redirect($this->session->userdata('backpath'));
		endif;
		for($i=0;$i<count($pagevar['mymarkets']);$i++):
			$pagevar['mymarkets'][$i]['password'] = $this->encrypt->decode($pagevar['mymarkets'][$i]['cryptpassword']);
		endfor;
		if(!$pagevar['platform']['imgwidth'] && !$pagevar['platform']['imgheight']):
			$pagevar['platform']['imgstatus'] = 0;
			$pagevar['platform']['imgwidth'] = '';
			$pagevar['platform']['imgheight'] = '';
		endif;
		
		$this->load->view("admin_interface/management-edit-platform",$pagevar);
	}
	
	public function management_view_platform(){
		
		$platform = $this->uri->segment(5);
		if(!$platform):
			redirect('admin-panel/management/platforms');
		endif;
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Площадки | Просмотр площадки',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'platform'		=> $this->mdplatforms->read_record($platform),
					'markets'		=> $this->mdmarkets->read_records(),
					'mymarkets'		=> array(),
					'thematic'		=> $this->mdthematic->read_records(),
					'services'		=> array(),
					'cms'			=> $this->mdcms->read_records(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$pagevar['mymarkets'] = $this->mdmkplatform->read_records_by_platform($platform,$pagevar['platform']['webmaster']);
		$attached = $this->mdunion->services_attached_list($pagevar['platform']['webmaster']);
		for($i=0;$i<count($attached);$i++):
			$pagevar['services'][$i] = $this->mdunion->read_srvvalue_service_platform($attached[$i]['service'],$platform,$pagevar['platform']['webmaster']);
		endfor;
		for($i=0;$i<count($pagevar['mymarkets']);$i++):
			$pagevar['mymarkets'][$i]['password'] = $this->encrypt->decode($pagevar['mymarkets'][$i]['cryptpassword']);
		endfor;
		if(!$pagevar['platform']['imgwidth'] && !$pagevar['platform']['imgheight']):
			$pagevar['platform']['imgstatus'] = 0;
			$pagevar['platform']['imgwidth'] = '-';
			$pagevar['platform']['imgheight'] = '-';
		endif;
		
		$this->load->view("admin_interface/management-view-platform",$pagevar);
	}
	
	public function calculate(){
		
		$statusval = array('nextstep'=>TRUE,'plcount'=>0,'count'=>'','from'=>'','calc'=>'');
		$calc = trim($this->input->post('calc'));
		$count = trim($this->input->post('count'));
		$from = trim($this->input->post('from'));
		if(!$count || !$calc):
			show_404();
		endif;
		$platforms = $this->mdplatforms->read_limit_records($count,$from);
		if(!count($platforms)):
			$statusval['nextstep'] = FALSE;
		else:
			if($calc == 'pr'):
				for($i=0;$i<count($platforms);$i++):
					$result = $this->mdplatforms->update_field($platforms[$i]['id'],'pr',$this->getpagerank($platforms[$i]['url']));
					if($result):
						$statusval['plcount']++;
					endif;
				endfor;
			elseif($calc == 'tic'):
				for($i=0;$i<count($platforms);$i++):
					$oldtic = $this->mdplatforms->read_field($platforms[$i]['id'],'tic');
					$tic = $this->getTIC('http://'.$platforms[$i]['url']);
					$result = $this->mdplatforms->update_field($platforms[$i]['id'],'tic',$tic);
					if($result):
						$statusval['plcount']++;
					endif;
					if($oldtic != $tic):
						$addwtic = 5; $addmtic = 2;
						if($oldtic < 30 AND $tic >= 30):
							$sqlquery = "UPDATE platforms SET ccontext=ccontext+$addwtic, mcontext=mcontext+$addmtic,cnotice=cnotice+$addwtic,mnotice=mnotice+$addmtic,clinkpic=clinkpic+$addwtic,mlinkpic=mlinkpic+$addmtic,cpressrel=cpressrel+$addwtic,mpressrel=mpressrel+$addmtic,clinkarh=clinkarh+$addwtic,mlinkarh=mlinkarh+$addmtic WHERE platforms.id = ".$platforms[$i]['id']." AND platforms.noticpr = 0";
							$this->mdplatforms->run_query($sqlquery);
						elseif($oldtic >= 30 AND $tic < 30):
							$sqlquery = "UPDATE platforms SET ccontext=ccontext-$addwtic, mcontext=mcontext-$addmtic,cnotice=cnotice-$addwtic,mnotice=mnotice-$addmtic,clinkpic=clinkpic-$addwtic,mlinkpic=mlinkpic-$addmtic,cpressrel=cpressrel-$addwtic,mpressrel=mpressrel-$addmtic,clinkarh=clinkarh-$addwtic,mlinkarh=mlinkarh-$addmtic WHERE platforms.id = ".$platforms[$i]['id']." AND platforms.noticpr = 0";
							$this->mdplatforms->run_query($sqlquery);
						endif;
					endif;
				endfor;
			endif;
		endif;
		$statusval['plcount'] = count($platforms);
		$statusval['count'] = $count;
		$statusval['from'] = $from;
		$statusval['calc'] = $calc;
		echo json_encode($statusval);
	}
	
	/******************************************************** markets ******************************************************/

	public function management_markets(){
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Биржи',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'markets'		=> $this->mdmarkets->read_records(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('amsubmit')):
			$_POST['amsubmit'] = NULL;
			$this->form_validation->set_rules('title',' ','required|trim');
			$this->form_validation->set_rules('url',' ','required|prep_url|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				if($_FILES['icon']['error'] != 4):
					$_POST['icon'] = file_get_contents($_FILES['icon']['tmp_name']);
				else:
					$_POST['icon'] = file_get_contents(base_url().'images/noimages/no_news.png');
				endif;
				$result = $this->mdmarkets->insert_record($_POST);
				if($result):
					$this->session->set_userdata('msgs','Биржа добавлена успешно');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		if($this->input->post('emsubmit')):
			$_POST['emsubmit'] = NULL;
			$this->form_validation->set_rules('title',' ','required|trim');
			$this->form_validation->set_rules('url',' ','required|prep_url|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				if($_FILES['icon']['error'] != 4):
					$_POST['icon'] = file_get_contents($_FILES['icon']['tmp_name']);
				endif;
				$result = $this->mdmarkets->update_record($_POST);
				if($result):
					$this->session->set_userdata('msgs','Биржа изменена успешно');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/management-markets",$pagevar);
	}
	
	public function management_markets_deleting(){
		
		$mid = $this->uri->segment(5);
		if($mid):
			$result = $this->mdmarkets->delete_record($mid);
			if($result):
				$this->session->set_userdata('msgs','Биржа удалена успешно');
			else:
				$this->session->set_userdata('msgr','Биржа не удалена');
			endif;
			redirect($_SERVER['HTTP_REFERER']);
		else:
			show_404();
		endif;
	}
	
	/******************************************************** promocode ******************************************************/

	public function management_promocode(){
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Промокоды',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'markets'		=> $this->mdmarkets->read_records(),
					'codes'			=> $this->mdpromocodes->read_records(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('acsubmit')):
			$_POST['aсsubmit'] = NULL;
			$this->form_validation->set_rules('code',' ','required|trim');
			$this->form_validation->set_rules('datefrom',' ','required|trim');
			$this->form_validation->set_rules('dateto',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				if(count($_POST['birzid']) == count($pagevar['markets'])):
					$_POST['birzid'] = 0;
				else:
					$_POST['birzid'] = implode(",", $_POST['birzid']);
				endif;
				$pattern = "/(\d+)\.(\w+)\.(\d+)/i";
				$replacement = "\$3-\$2-\$1";
				$_POST['datefrom'] = preg_replace($pattern,$replacement,$_POST['datefrom']);
				$pattern = "/(\d+)\.(\w+)\.(\d+)/i";
				$replacement = "\$3-\$2-\$1";
				$_POST['dateto'] = preg_replace($pattern,$replacement,$_POST['dateto']);
				if($_POST['dateto'] < $_POST['datefrom']):
					$begin = $_POST['datefrom'];
					$_POST['datefrom'] = $_POST['dateto'];
					$_POST['dateto'] = $begin;
				endif;
				$result = $this->mdpromocodes->insert_record($_POST);
				if($result):
					$this->session->set_userdata('msgs','Промокод добавлен успешно');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		if($this->input->post('ecsubmit')):
			$_POST['ecsubmit'] = NULL;
			$this->form_validation->set_rules('code',' ','required|trim');
			$this->form_validation->set_rules('datefrom',' ','required|trim');
			$this->form_validation->set_rules('dateto',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				if(count($_POST['birzid']) == count($pagevar['markets'])):
					$_POST['birzid'] = 0;
				else:
					$_POST['birzid'] = implode(",", $_POST['birzid']);
				endif;
				$pattern = "/(\d+)\.(\w+)\.(\d+)/i";
				$replacement = "\$3-\$2-\$1";
				$_POST['datefrom'] = preg_replace($pattern,$replacement,$_POST['datefrom']);
				
				$pattern = "/(\d+)\.(\w+)\.(\d+)/i";
				$replacement = "\$3-\$2-\$1";
				$_POST['dateto'] = preg_replace($pattern,$replacement,$_POST['dateto']);
				if($_POST['dateto'] < $_POST['datefrom']):
					$begin = $_POST['datefrom'];
					$_POST['datefrom'] = $_POST['dateto'];
					$_POST['dateto'] = $begin;
				endif;
				$result = $this->mdpromocodes->update_record($_POST);
				if($result):
					$this->session->set_userdata('msgs','Промокод изменен успешно');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		for($i=0;$i<count($pagevar['codes']);$i++):
			$pagevar['codes'][$i]['datefrom'] = $this->operation_dot_date($pagevar['codes'][$i]['datefrom']);
			$pagevar['codes'][$i]['dateto'] = $this->operation_dot_date($pagevar['codes'][$i]['dateto']);
		endfor;
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/management-promocode",$pagevar);
	}
	
	public function management_promocode_deleting(){
		
		$сid = $this->uri->segment(5);
		if($сid):
			$result = $this->mdpromocodes->delete_record($сid);
			if($result):
				$this->session->set_userdata('msgs','Промокод удален успешно');
			else:
				$this->session->set_userdata('msgr','Промокод не удален');
			endif;
			redirect($_SERVER['HTTP_REFERER']);
		else:
			show_404();
		endif;
	}
	
	/***************************************************** partner program*************************************************/
	
	public function partner_program(){
		
		$from = intval($this->uri->segment(5));
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Партнерская программа',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'webmasters'	=> $this->mdunion->parent_partners_list(10,$from),
					'pages'			=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		for($i=0;$i<count($pagevar['webmasters']);$i++):
			$pagevar['webmasters'][$i]['platforms'] = $this->mdunion->count_platforms_partners($pagevar['webmasters'][$i]['id']);
			$works = $this->mdunion->count_summa_works_partners($pagevar['webmasters'][$i]['id']);
			if($works):
				$pagevar['webmasters'][$i]['summa'] = round($works['summa']*0.05,2);
				$pagevar['webmasters'][$i]['works'] = $works['works'];
			endif;
		endfor;
		$config['base_url'] 		= $pagevar['baseurl'].'admin-panel/actions/partner-program/from/';
		$config['uri_segment'] 		= 5;
		$config['total_rows'] 		= $this->mdunion->count_parent_partners();
		$config['per_page'] 		= 10;
		$config['num_links'] 		= 4;
		$config['first_link']		= 'В начало';
		$config['last_link'] 		= 'В конец';
		$config['next_link'] 		= 'Далее &raquo;';
		$config['prev_link'] 		= '&laquo; Назад';
		$config['cur_tag_open']		= '<li class="active"><a href="#">';
		$config['cur_tag_close'] 	= '</a></li>';
		$config['full_tag_open'] 	= '<div class="pagination"><ul>';
		$config['full_tag_close'] 	= '</ul></div>';
		$config['first_tag_open'] 	= '<li>';
		$config['first_tag_close'] 	= '</li>';
		$config['last_tag_open'] 	= '<li>';
		$config['last_tag_close'] 	= '</li>';
		$config['next_tag_open'] 	= '<li>';
		$config['next_tag_close'] 	= '</li>';
		$config['prev_tag_open'] 	= '<li>';
		$config['prev_tag_close'] 	= '</li>';
		$config['num_tag_open'] 	= '<li>';
		$config['num_tag_close'] 	= '</li>';
		
		$this->pagination->initialize($config);
		$pagevar['pages'] = $this->pagination->create_links();
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->session->set_userdata('backpath',$this->uri->uri_string());
		$this->load->view("admin_interface/partner-program",$pagevar);
	}
	
	public function partners_list(){
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Партнерская программа | Cписок приглашенных',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'webmasters'	=> $this->mdunion->partners_list($this->uri->segment(5)),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		for($i=0;$i<count($pagevar['webmasters']);$i++):
			$pagevar['webmasters'][$i]['platforms'] = $this->mdplatforms->count_records_by_webmaster($pagevar['webmasters'][$i]['id']);
			$pagevar['webmasters'][$i]['works']	= $this->mddelivesworks->count_records_by_webmaster_status($pagevar['webmasters'][$i]['id'],1);
			$pagevar['webmasters'][$i]['summa'] = $this->mddelivesworks->sum_records_by_webmaster_status($pagevar['webmasters'][$i]['id'],1);
			$pagevar['webmasters'][$i]['summa'] = round($pagevar['webmasters'][$i]['summa']*0.05,2);
		endfor;
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->session->set_userdata('backpath',$this->uri->uri_string());
		$this->load->view("admin_interface/partners-list",$pagevar);
	}
	
	/******************************************************** jobs ******************************************************/
	
	public function user_finished_jobs(){
		
		$from = intval($this->uri->segment(8));
		$fpaid = $fnotpaid = 1;
		if($this->session->userdata('jobsfilter') != ''):
			$filter = preg_split("/,/",$this->session->userdata('jobsfilter'));
			if(count($filter) == 1):
				$fpaid = ($filter[0])?1:0;
				$fnotpaid = (!$filter[0])?1:0;
			endif;
		else:
			$this->session->set_userdata('jobsfilter','0,1');
		endif;
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Выполненные задания для вебмастера',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'delivers'		=> $this->mdunion->delivers_works_webmaster($this->uri->segment(5),10,$from,$this->session->userdata('jobsfilter')),
					'filter'		=> array('fpaid'=>$fpaid,'fnotpaid'=>$fnotpaid),
					'typeswork'		=> $this->mdtypeswork->read_records(),
					'markets'		=> $this->mdmarkets->read_records(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('submit')):
			$_POST['submit'] = NULL;
			$this->form_validation->set_rules('wid',' ','required|trim');
			$this->form_validation->set_rules('typework',' ','required|trim');
			$this->form_validation->set_rules('market',' ','required|trim');
			$this->form_validation->set_rules('mkprice',' ','required|trim');
			$this->form_validation->set_rules('ulrlink',' ','required|prep_url|trim');
			$this->form_validation->set_rules('countchars',' ','required|trim');
			$this->form_validation->set_rules('wprice',' ','required|trim');
			$this->form_validation->set_rules('mprice',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				$this->mddelivesworks->update_record($_POST['wid'],$_POST);
				$this->session->set_userdata('msgs','Запись о работе сохранена');
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		$config['base_url'] 	= $pagevar['baseurl'].'admin-panel/management/users/userid/'.$this->uri->segment(5).'/finished-jobs/from/';
		$config['uri_segment'] 	= 8;
		$config['total_rows'] 	= $this->mdunion->count_delivers_works_webmaster($this->uri->segment(5),$this->session->userdata('jobsfilter'));
		$config['per_page'] 	= 10;
		$config['num_links'] 	= 4;
		$config['first_link']		= 'В начало';
		$config['last_link'] 		= 'В конец';
		$config['next_link'] 		= 'Далее &raquo;';
		$config['prev_link'] 		= '&laquo; Назад';
		$config['cur_tag_open']		= '<li class="active"><a href="#">';
		$config['cur_tag_close'] 	= '</a></li>';
		$config['full_tag_open'] 	= '<div class="pagination"><ul>';
		$config['full_tag_close'] 	= '</ul></div>';
		$config['first_tag_open'] 	= '<li>';
		$config['first_tag_close'] 	= '</li>';
		$config['last_tag_open'] 	= '<li>';
		$config['last_tag_close'] 	= '</li>';
		$config['next_tag_open'] 	= '<li>';
		$config['next_tag_close'] 	= '</li>';
		$config['prev_tag_open'] 	= '<li>';
		$config['prev_tag_close'] 	= '</li>';
		$config['num_tag_open'] 	= '<li>';
		$config['num_tag_close'] 	= '</li>';
		
		$this->pagination->initialize($config);
		$pagevar['pages'] = $this->pagination->create_links();
		
		if($this->input->post('scsubmit')):
			unset($_POST['scsubmit']);
			$result = $this->mdunion->read_webmaster_jobs($this->uri->segment(5),$_POST['srdjid'],$_POST['srdjurl']);
			$pagevar['title'] .= 'Поиск выполнен';
			$pagevar['delivers'] = $result;
			$pagevar['pages'] = NULL;
		endif;
		
		for($i=0;$i<count($pagevar['delivers']);$i++):
			$pagevar['delivers'][$i]['date'] = $this->operation_dot_date($pagevar['delivers'][$i]['date']);
			if(mb_strlen($pagevar['delivers'][$i]['ulrlink'],'UTF-8') > 15):
				$pagevar['delivers'][$i]['link'] = mb_substr($pagevar['delivers'][$i]['ulrlink'],0,15,'UTF-8');
				$pagevar['delivers'][$i]['link'] .= ' ... '.mb_substr($pagevar['delivers'][$i]['ulrlink'],strlen($pagevar['delivers'][$i]['ulrlink'])-10,10,'UTF-8');;
			else:
				$pagevar['delivers'][$i]['link'] = $pagevar['delivers'][$i]['ulrlink'];
			endif;
		endfor;
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/user-finished-jobs",$pagevar);
	}
	
	public function platform_finished_jobs(){
		
		$from = intval($this->uri->segment(8));
		
		$fpaid = $fnotpaid = 1;
		if($this->session->userdata('jobsfilter') != ''):
			$filter = preg_split("/,/",$this->session->userdata('jobsfilter'));
			if(count($filter) == 1):
				$fpaid = ($filter[0])?1:0;
				$fnotpaid = (!$filter[0])?1:0;
			endif;
		else:
			$this->session->set_userdata('jobsfilter','0,1');
		endif;
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Выполненные задания по площадке',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'delivers'		=> $this->mdunion->delivers_works_platform($this->uri->segment(5),10,$from,$this->session->userdata('jobsfilter')),
					'filter'		=> array('fpaid'=>$fpaid,'fnotpaid'=>$fnotpaid),
					'typeswork'		=> $this->mdtypeswork->read_records(),
					'markets'		=> $this->mdmarkets->read_records(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('submit')):
			$_POST['submit'] = NULL;
			$this->form_validation->set_rules('wid',' ','required|trim');
			$this->form_validation->set_rules('typework',' ','required|trim');
			$this->form_validation->set_rules('market',' ','required|trim');
			$this->form_validation->set_rules('mkprice',' ','required|trim');
			$this->form_validation->set_rules('ulrlink',' ','required|prep_url|trim');
			$this->form_validation->set_rules('countchars',' ','required|trim');
			$this->form_validation->set_rules('wprice',' ','required|trim');
			$this->form_validation->set_rules('mprice',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				$this->mddelivesworks->update_record($_POST['wid'],$_POST);
				$this->session->set_userdata('msgs','Запись о работе сохранена');
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		$config['base_url'] 	= $pagevar['baseurl'].'admin-panel/management/platforms/platformid/'.$this->uri->segment(5).'/finished-jobs/from/';
		$config['uri_segment'] 	= 8;
		$config['total_rows'] 	= $this->mdunion->count_delivers_works_platform($this->uri->segment(5),$this->session->userdata('jobsfilter'));
		$config['per_page'] 	= 10;
		$config['num_links'] 	= 4;
		$config['first_link']		= 'В начало';
		$config['last_link'] 		= 'В конец';
		$config['next_link'] 		= 'Далее &raquo;';
		$config['prev_link'] 		= '&laquo; Назад';
		$config['cur_tag_open']		= '<li class="active"><a href="#">';
		$config['cur_tag_close'] 	= '</a></li>';
		$config['full_tag_open'] 	= '<div class="pagination"><ul>';
		$config['full_tag_close'] 	= '</ul></div>';
		$config['first_tag_open'] 	= '<li>';
		$config['first_tag_close'] 	= '</li>';
		$config['last_tag_open'] 	= '<li>';
		$config['last_tag_close'] 	= '</li>';
		$config['next_tag_open'] 	= '<li>';
		$config['next_tag_close'] 	= '</li>';
		$config['prev_tag_open'] 	= '<li>';
		$config['prev_tag_close'] 	= '</li>';
		$config['num_tag_open'] 	= '<li>';
		$config['num_tag_close'] 	= '</li>';
		
		$this->pagination->initialize($config);
		$pagevar['pages'] = $this->pagination->create_links();
		
		if($this->input->post('scsubmit')):
			unset($_POST['scsubmit']);
			$result = $this->mdunion->read_platform_jobs($this->uri->segment(5),$_POST['srdjid'],$_POST['srdjurl']);
			$pagevar['title'] .= 'Поиск выполнен';
			$pagevar['delivers'] = $result;
			$pagevar['pages'] = NULL;
		endif;
		
		for($i=0;$i<count($pagevar['delivers']);$i++):
			$pagevar['delivers'][$i]['date'] = $this->operation_dot_date($pagevar['delivers'][$i]['date']);
			if(mb_strlen($pagevar['delivers'][$i]['ulrlink'],'UTF-8') > 15):
				$pagevar['delivers'][$i]['link'] = mb_substr($pagevar['delivers'][$i]['ulrlink'],0,15,'UTF-8');
				$pagevar['delivers'][$i]['link'] .= ' ... '.mb_substr($pagevar['delivers'][$i]['ulrlink'],strlen($pagevar['delivers'][$i]['ulrlink'])-10,10,'UTF-8');;
			else:
				$pagevar['delivers'][$i]['link'] = $pagevar['delivers'][$i]['ulrlink'];
			endif;
		endfor;
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/platform-finished-jobs",$pagevar);
	}
	
	public function delete_finished_jobs(){
		
		$wid = $this->uri->segment(6);
		if($wid):
			$result = $this->mddelivesworks->delete_record($wid);
			if($result):
				$this->session->set_userdata('msgs','Работа удалена.');
			endif;
			redirect($_SERVER['HTTP_REFERER']);
		else:
			redirect($this->session->userdata('backpath'));
		endif;
	}
	
	public function delete_user_jobs(){
		
		$uid = $this->uri->segment(6);
		if($uid):
			$result = $this->mddelivesworks->delete_records_user($uid);
			if($result):
				$this->session->set_userdata('msgs','Работы удалены.');
			endif;
			redirect($_SERVER['HTTP_REFERER']);
		else:
			redirect($this->session->userdata('backpath'));
		endif;
	}
	
	public function delete_platform_jobs(){
		
		$pid = $this->uri->segment(6);
		if($pid):
			$result = $this->mddelivesworks->delete_records_platform($pid);
			if($result):
				$this->session->set_userdata('msgs','Работы удалены.');
			endif;
			redirect($_SERVER['HTTP_REFERER']);
		else:
			redirect($this->session->userdata('backpath'));
		endif;
	}
	
	public function users_search_jobs(){
		
		$statusval = array('status'=>FALSE,'retvalue'=>'');
		$search = $this->input->post('squery');
		$webmaster = $this->input->post('user');
		if(!$search) show_404();
		$jworks = $this->mddelivesworks->search_webmaster_jobs($webmaster,$search);
		if($jworks):
			$statusval['retvalue'] = '<ul>';
			for($i=0;$i<count($jworks);$i++):
				$statusval['retvalue'] .= '<li class="djorg" data-djid="'.$jworks[$i]['id'].'">'.$jworks[$i]['ulrlink'].'</li>';
			endfor;
			$statusval['retvalue'] .= '</ul>';
			$statusval['status'] = TRUE;
		endif;
		echo json_encode($statusval);
	}
	
	public function platform_search_jobs(){
		
		$statusval = array('status'=>FALSE,'retvalue'=>'');
		$search = $this->input->post('squery');
		$platform = $this->input->post('platform');
		if(!$search) show_404();
		$jworks = $this->mddelivesworks->search_platform_jobs($platform,$search);
		if($jworks):
			$statusval['retvalue'] = '<ul>';
			for($i=0;$i<count($jworks);$i++):
				$statusval['retvalue'] .= '<li class="djorg" data-djid="'.$jworks[$i]['id'].'">'.$jworks[$i]['ulrlink'].'</li>';
			endfor;
			$statusval['retvalue'] .= '</ul>';
			$statusval['status'] = TRUE;
		endif;
		echo json_encode($statusval);
	}
	
	public function finished_jobs_filter(){
		
		$statusval = array('status'=>TRUE,'filter'=>'','paid'=>-1,'notpaid'=>-1);
		$showed = trim($this->input->post('showed'));
		$this->session->set_userdata('jobsfilter','0,1');
		if(!$showed):
			$this->session->set_userdata('jobsfilter','');
		else:
			$filter = preg_split("/&/",$showed);
			for($i=0;$i<count($filter);$i++):
				$fparam[$i] = preg_split("/=/",$filter[$i]);
			endfor;
			if(count($fparam)==1):
				$this->session->set_userdata('jobsfilter',$fparam[0][1]);
				if($fparam[0][1]):
					$statusval['paid'] = 1;$statusval['notpaid'] = 0;
				else:
					$statusval['paid'] = 0;$statusval['notpaid'] = 1;
				endif;
			else:
				$this->session->set_userdata('jobsfilter',$fparam[0][1].','.$fparam[1][1]);
				$statusval['paid'] = 1;$statusval['notpaid'] = 1;
			endif;
		endif;
		$statusval['filter'] = $this->session->userdata('jobsfilter');
		echo json_encode($statusval);
	}
	
	/******************************************************** works ******************************************************/
	
	public function management_types_work(){
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Типы работ',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'tpswork'		=> $this->mdtypeswork->read_records(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('amsubmit')):
			$_POST['amsubmit'] = NULL;
			$this->form_validation->set_rules('title',' ','required|trim');
			$this->form_validation->set_rules('wprice',' ','required|trim');
			$this->form_validation->set_rules('mprice',' ','required|trim');
			$this->form_validation->set_rules('ticpr',' ','trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				if(!isset($_POST['ticpr'])):
					$_POST['ticpr'] = 0;
				endif;
				$result = $this->mdtypeswork->insert_record($_POST);
				if($result):
					$this->session->set_userdata('msgs','Тип работ добавлен успешно');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		if($this->input->post('emsubmit')):
			$_POST['emsubmit'] = NULL;
			$this->form_validation->set_rules('title',' ','required|trim');
			$this->form_validation->set_rules('wprice',' ','required|trim');
			$this->form_validation->set_rules('mprice',' ','required|trim');
			$this->form_validation->set_rules('ticpr',' ','trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				if(!isset($_POST['ticpr'])):
					$_POST['ticpr'] = 0;
				endif;
//				$worktype = $this->mdtypeswork->read_record($_POST['tpid']);
				$result = $this->mdtypeswork->update_record($_POST);
				if($result):
					/*if($worktype['ticpr'] && !$_POST['ticpr']):
						$this->mdplatforms->update_nickname_ticpr($worktype['nickname'],'-5','-2');
					elseif(!$worktype['ticpr'] && $_POST['ticpr']):
						$this->mdplatforms->update_nickname_ticpr($worktype['nickname'],'+5','+2');
					endif;*/
					$this->session->set_userdata('msgs','Тип работ изменен успешно');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/management-types-work",$pagevar);
	}
	
	public function management_types_work_deleting(){
		
		$wid = $this->uri->segment(5);
		if($wid):
			$result = $this->mdtypeswork->delete_record($wid);
			if($result):
				$this->session->set_userdata('msgs','Тип работ удален успешно');
			else:
				$this->session->set_userdata('msgr','Тип работ не удален');
			endif;
			redirect($_SERVER['HTTP_REFERER']);
		else:
			show_404();
		endif;
	}
	
	/******************************************************** ratings ******************************************************/
	
	public function management_ratings(){
		
		switch($this->uri->segment(4)):
			case 'advertisers' 	: $rtype = 1; break;
			case 'webmasters' 	: $rtype = 2; break;
			default 			: redirect('/');
		endswitch;
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Отзывы о системе',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'ratings'		=> $this->mdratings->read_records($rtype),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('arsubmit')):
			$_POST['arsubmit'] = NULL;
			$this->form_validation->set_rules('title',' ','required|trim');
			$this->form_validation->set_rules('text',' ','required|trim');
			$this->form_validation->set_rules('resource',' ','prep_url|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				if($_FILES['avatar']['error'] != 4):
					$_POST['avatar'] = file_get_contents($_FILES['avatar']['tmp_name']);
				else:
					$_POST['avatar'] = file_get_contents(base_url().'images/no-avatar.gif');
				endif;
				$result = $this->mdratings->insert_record($_POST,$rtype);
				if($result):
					$this->session->set_userdata('msgs','Отзыв добавлен успешно');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/management-ratings",$pagevar);
	}
	
	public function management_rating_deleting(){
		
		$rid = $this->uri->segment(5);
		if($rid):
			$result = $this->mdratings->delete_record($rid);
			if($result):
				$this->session->set_userdata('msgs','Отзыв удален успешно');
			else:
				$this->session->set_userdata('msgr','Отзыв не удален');
			endif;
			redirect($_SERVER['HTTP_REFERER']);
		else:
			show_404();
		endif;
	}
	
	/****************************************************** tickets ******************************************************/
	
	public function ticket_create(){
		
		if($this->mdplatforms->ownew_manager_platform($this->user['uid'],$this->uri->segment(5),array(0,1))):
			$this->session->set_flashdata('platform_ticket',$this->mdplatforms->read_field($this->uri->segment(5),'url'));
			redirect('admin-panel/actions/tickets-outbox');
		endif;
		redirect('admin-panel/management/users/webmasters');
	}
	
	public function tickets_outbox(){
		
		$from = intval($this->uri->segment(5));
		$hideticket = FALSE;
		if($this->session->userdata('hideticket')):
			$hideticket = TRUE;
		endif;
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Исходящие тикеты',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'tickets'		=> $this->mdunion->read_tickets_by_sender(0,15,$from,$hideticket),
					'platforms'		=> array(),
					'create_ticket'	=> $this->session->flashdata('platform_ticket'),
					'hideticket'	=> $hideticket,
					'pages'			=> $this->pagination('manager-panel/actions/tickets-outbox',5,$this->mdunion->count_tickets_by_sender(0,$hideticket),15),
					'cntunit'		=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$platforms = $this->mdplatforms->platforms_by_admin('id,url','id');
		for($i=0;$i<count($platforms);$i++):
			$pagevar['platforms'][] = mb_strtolower($platforms[$i]['url'],'UTF-8');
		endfor;
		if($this->input->post('insticket')):
			unset($_POST['insticket']);
			$this->form_validation->set_rules('title',' ','required|trim');
			$this->form_validation->set_rules('text',' ','required|trim');
			$this->form_validation->set_rules('type',' ','required|trim');
			$this->form_validation->set_rules('platform',' ','required|trim');
			$this->form_validation->set_rules('importance',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка. Не заполены необходимые поля.');
				redirect($this->uri->uri_string());
			else:
				$ticket_data = $this->input->post();
				$recipient = FALSE;
				$platform_id = $this->mdplatforms->exist_platform($ticket_data['platform']);
				if($platform_id):
					if($ticket_data['type'] == 1):
						$recipient = $this->mdplatforms->read_field($platform_id,'webmaster');
						if(!$recipient):
							$this->session->set_userdata('msgr','Ошибка. Получатель не определен.');
							redirect($this->uri->uri_string());
						endif;
					else:
						$recipient = $this->mdplatforms->read_field($platform_id,'manager');
						if(!$recipient):
							$this->session->set_userdata('msgr','Ошибка. Получатель не определен.');
							redirect($this->uri->uri_string());
						endif;
					endif;
					if($recipient):
						ob_start();
						?><img src="<?=base_url();?>images/logo.png" alt="" />
						<p><strong>Здравствуйте, <?=$this->mdusers->read_field($recipient,'fio');?></strong></p>
						<p>У Вас новое сообщение через тикет-систему</p>
						<p>Что бы прочитать его войдите в <?=$this->link_cabinet($recipient);?> и перейдите в раздел "Тикеты"</p>
						<p><br/><?=$this->sub_tickettext($ticket_data['text'],$recipient);?><br/></p>
						<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p><?
						$mailtext = ob_get_clean();
						$this->send_mail($this->mdusers->read_field($recipient,'login'),'admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления','Bystropost.ru - Новый тикет',$mailtext);
					endif;
					$ticket_data['platform'] = $platform_id;
					$ticket = $this->mdtickets->insert_record(0,$recipient,$ticket_data);
					if($ticket):
						$this->mdtkmsgs->insert_record(0,$ticket,0,$recipient,1,$ticket_data['text']);
						$this->mdlog->insert_record($this->user['uid'],'Событие №17: Состояние тикета - создан');
						$this->mdmessages->send_noreply_message($this->user['uid'],$recipient,2,$this->mdusers->read_field($recipient,'type'),'Новое сообщение через тикет-систему. Тикет ID: '.$ticket);
						$this->session->set_userdata('msgs','Тикет успешно создан.');
					endif;
				else:
					$this->session->set_userdata('msgr','Ошибка. Не верно указана площадка.');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		for($i=0;$i<count($pagevar['tickets']);$i++):
			$pagevar['tickets'][$i]['date'] = $this->operation_dot_date_on_time($pagevar['tickets'][$i]['date']);
			$pagevar['tickets'][$i]['msg_date'] = $this->operation_dot_date_on_time($this->mdtkmsgs->in_finish_message_date(0,$pagevar['tickets'][$i]['id']));
			$finish_sender = $this->mdtkmsgs->in_finish_message_sender(0,$pagevar['tickets'][$i]['id']);
			if($finish_sender):
				$pagevar['tickets'][$i]['msg_sender'] = $this->mdusers->read_field($finish_sender,'position');
			elseif($finish_sender == '0'):
				$pagevar['tickets'][$i]['msg_sender'] = 'Администратор';
			else:
				$pagevar['tickets'][$i]['msg_sender'] = 'Без ответа';
			endif;
			if($pagevar['tickets'][$i]['recipient']):
				$pagevar['tickets'][$i]['position'] = $this->mdusers->read_field($pagevar['tickets'][$i]['recipient'],'login');
			endif;
		endfor;
		$this->session->set_userdata('backpath',$this->uri->uri_string());
		$this->load->view("admin_interface/tickets/outbox",$pagevar);
	}
	
	public function tickets_inbox(){
		
		$from = intval($this->uri->segment(5));
		$hideticket = FALSE;
		if($this->session->userdata('hideticket')):
			$hideticket = TRUE;
		endif;
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Входящие тикеты',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'tickets'		=> $this->mdunion->read_tickets_by_recipient(0,15,$from,$hideticket),
					'hideticket'	=> $hideticket,
					'pages'			=> $this->pagination('admin-panel/actions/tickets-inbox',5,$this->mdunion->count_tickets_by_recipient(0,$hideticket),15),
					'cntunit'		=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		for($i=0;$i<count($pagevar['tickets']);$i++):
			$pagevar['tickets'][$i]['date'] = $this->operation_dot_date_on_time($pagevar['tickets'][$i]['date']);
			$pagevar['tickets'][$i]['msg_date'] = $this->operation_dot_date_on_time($this->mdtkmsgs->in_finish_message_date(0,$pagevar['tickets'][$i]['id']));
			if($pagevar['tickets'][$i]['sender']):
				$pagevar['tickets'][$i]['position_send'] = $this->mdusers->read_field($pagevar['tickets'][$i]['sender'],'login');
			endif;
			$finish_sender = $this->mdtkmsgs->in_finish_message_sender(0,$pagevar['tickets'][$i]['id']);
			if($finish_sender):
				$pagevar['tickets'][$i]['msg_sender'] = $this->mdusers->read_field($finish_sender,'position');
			elseif($finish_sender == '0'):
				$pagevar['tickets'][$i]['msg_sender'] = 'Администратор';
			else:
				$pagevar['tickets'][$i]['msg_sender'] = 'Без ответа';
			endif;
			if($pagevar['tickets'][$i]['recipient']):
				$pagevar['tickets'][$i]['position_to'] = '<span class="label label-info">'.$this->mdusers->read_field($pagevar['tickets'][$i]['recipient'],'login').'</span>';
			else:
				$pagevar['tickets'][$i]['position_to'] = '<span class="label label-warning">Администратор</span>';
			endif;
		endfor;
		$this->session->set_userdata('backpath',$this->uri->uri_string());
		$this->load->view("admin_interface/tickets/inbox",$pagevar);
	}
	
	public function read_ticket(){
		
		$ticket = $this->uri->segment(5);
		$from = intval($this->uri->segment(7));
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Тикеты | Чтение тикета',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'ticket'		=> $this->mdunion->view_ticket_info($ticket),
					'messages'		=> $this->mdunion->read_messages_by_ticket_pages($ticket,7,$from),
					'pages'			=> $this->pagination("admin-panel/actions/".$this->uri->segment(3)."/read-ticket-id/$ticket",7,$this->mdunion->count_messages_by_ticket($ticket),7),
					'cntunit'		=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('insticket')):
			unset($_POST['insticket']);
			
			$message = $this->input->post();
			$msgs = '';
			if($this->uri->segment(3) == 'tickets-outbox'):
				$recipient = $pagevar['ticket']['recipient'];
			elseif($this->uri->segment(3) == 'tickets-inbox'):
				$recipient = $pagevar['ticket']['sender'];
			endif;
			if(isset($message['closeticket'])):
				$this->mdlog->insert_record($this->user['uid'],'Событие №18: Состояние тикета - закрыт');
				$msgs .= '<span class="label label-important">Тикет закрыт</span><br/>';
				$this->mdtickets->update_field($ticket,'status',1);
				$this->mdtickets->update_field($ticket,'sender_answer',0);
				$this->mdtickets->update_field($ticket,'recipient_answer',0);
				$this->mdmessages->send_noreply_message($this->user['uid'],$recipient,2,$this->mdusers->read_field($recipient,'type'),'Администратор закрыл тикет ID: '.$ticket);
			else:
				if(empty($message['text'])):
					$this->session->set_userdata('msgr','Ошибка. Не заполены необходимые поля.');
					redirect($this->uri->uri_string());
				endif;
			endif;
			if(!empty($message['text'])):
				if($this->uri->segment(3) == 'tickets-outbox'):
					$recipient = $pagevar['ticket']['recipient'];
				elseif($this->uri->segment(3) == 'tickets-inbox'):
					$recipient = $pagevar['ticket']['sender'];
				endif;
				$result = $this->mdtkmsgs->insert_record(0,$ticket,0,$recipient,0,$message['text']);
				if($result):
					if(!$pagevar['ticket']['recipient'] && $pagevar['ticket']['sender']):
						$this->mdtickets->update_field($ticket,'recipient_answer',1);
						$this->mdtickets->update_field($ticket,'sender_answer',0);
						$this->mdtickets->update_field($ticket,'sender_reading',0);
					elseif($pagevar['ticket']['recipient'] && !$pagevar['ticket']['sender']):
						$this->mdtickets->update_field($ticket,'sender_answer',1);
						$this->mdtickets->update_field($ticket,'recipient_answer',0);
						$this->mdtickets->update_field($ticket,'recipient_reading',0);
					else:
						$this->mdtickets->update_field($ticket,'recipient_answer',1);
						$this->mdtickets->update_field($ticket,'sender_answer',0);
						$this->mdtickets->update_field($ticket,'sender_reading',0);
					endif;
					$this->mdlog->insert_record($this->user['uid'],'Событие №19: Состояние тикета - новое сообщение');
					$this->mdmessages->send_noreply_message($this->user['uid'],$recipient,2,$this->mdusers->read_field($recipient,'type'),'Новое сообщение через тикет-систему. Тикет ID: '.$ticket);
					$this->session->set_userdata('msgs',$msgs.' Сообщение отправлено');
					if(isset($message['sendmail'])):
						ob_start();
						?><img src="<?=base_url();?>images/logo.png" alt="" />
						<p><strong>Здравствуйте, <?=$this->mdusers->read_field($recipient,'fio');?></strong></p>
						<p>Получен ответ на Ваше сообщение. в тикет-системе.</p>
						<p>Что бы прочитать его войдите в <?=$this->link_cabinet($recipient);?> и перейдите в раздел "Тикеты"</p>
						<p><br/><?=$this->sub_tickettext($message['text'],$recipient);?><br/></p>
						<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p><?
						$mailtext = ob_get_clean();
						$this->send_mail($this->mdusers->read_field($recipient,'login'),'admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления','Bystropost.ru - Тикеты. Новое сообщение',$mailtext);
					endif;
				endif;
			endif;
			if(isset($message['closeticket'])):
				redirect($this->session->userdata('backpath'));
			else:
				redirect($this->uri->uri_string());
			endif;
		endif;
		
		for($i=0;$i<count($pagevar['messages']);$i++):
			if(!isset($pagevar['messages'][$i]['email'])):
				$pagevar['messages'][$i]['email'] = 'Администратор';
			endif;
		endfor;
		
		for($i=0;$i<count($pagevar['messages']);$i++):
			$pagevar['messages'][$i]['date'] = $this->operation_dot_date_on_time($pagevar['messages'][$i]['date']);
			if($pagevar['messages'][$i]['sender']):
				$pagevar['messages'][$i]['email'] = $this->mdusers->read_field($pagevar['messages'][$i]['sender'],'login');
				$sender_type = $this->mdusers->read_field($pagevar['messages'][$i]['sender'],'type');
				switch($sender_type):
					case 1:
						$pagevar['messages'][$i]['position'] = 'Вебмастер';
						$pagevar['messages'][$i]['ico']	= '<img class="img-polaroid" src="'.$pagevar['baseurl'].'images/icons/webmaster.png" alt="" />';
						break;
					case 2:
						$pagevar['messages'][$i]['position'] = 'Менеджер';
						$pagevar['messages'][$i]['ico']	= '<img class="img-polaroid" src="'.$pagevar['baseurl'].'images/icons/manager.png" alt="" />';
						break;
				endswitch;
			else:
				$pagevar['messages'][$i]['email'] = 'Администратор';
				$pagevar['messages'][$i]['position'] = 'Администратор';
				$pagevar['messages'][$i]['ico']	= '<img class="img-polaroid" src="'.$pagevar['baseurl'].'images/icons/administrator.png" alt="" />';
			endif;
		endfor;
		$pagevar['ticket']['message'] = $this->mdtkmsgs->main_message($ticket,FALSE,'id,text,date');
		$pagevar['ticket']['message']['date'] = $this->operation_dot_date_on_time($pagevar['ticket']['message']['date']);
		$pagevar['ticket']['message']['position'] = $this->mdusers->read_field($pagevar['ticket']['sender'],'position');
		$pagevar['ticket']['message']['email'] = $this->mdusers->read_field($pagevar['ticket']['sender'],'login');
		if($pagevar['ticket']['sender']):
			$sender_type = $this->mdusers->read_field($pagevar['ticket']['sender'],'type');
			if($sender_type == 1):
				$pagevar['ticket']['message']['ico'] = '<img class="img-polaroid" src="'.$pagevar['baseurl'].'images/icons/webmaster.png" alt="" />';
			elseif($sender_type == 2):
				$pagevar['ticket']['message']['ico'] = '<img class="img-polaroid" src="'.$pagevar['baseurl'].'images/icons/manager.png" alt="" />';
			endif;
		else:
			$pagevar['ticket']['message']['position'] = 'Администратор';
			$pagevar['ticket']['message']['ico'] = '<img class="img-polaroid" src="'.$pagevar['baseurl'].'images/icons/administrator.png" alt="" />';
		endif;
		
		if(!$pagevar['ticket']['recipient']):
			$this->mdtickets->update_field($ticket,'recipient_reading',1);
		elseif(!$pagevar['ticket']['sender']):
			$this->mdtickets->update_field($ticket,'sender_reading',1);
		endif;
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/tickets/messages",$pagevar);
	}
	
	public function control_open_ticket(){
		
		$ticket = $this->uri->segment(5);
		if($ticket):
			$result = $this->mdtickets->open_ticket($ticket);
			if($result):
				$this->session->set_userdata('msgs','Тикет открыт');
			endif;
			redirect($this->session->userdata('backpath'));
		else:
			redirect('manager-panel/actions/control');
		endif;
	}
	
	public function hide_closed_tickets(){
		
		$statusval = array('status'=>FALSE);
		$toggle = trim($this->input->post('toggle'));
		$hideticket = $this->session->userdata('hideticket');
		if($hideticket):
			$this->session->set_userdata('hideticket',FALSE);
		else:
			$this->session->set_userdata('hideticket',TRUE);
		endif;
		$statusval['status'] = $this->session->userdata('hideticket');
		echo json_encode($statusval);
	}
	
	/********************************************************* other *******************************************************/
	
	public function sendind_registering_info(){
		
		$statusval = array('nextstep'=>TRUE,'count'=>'','from'=>'','sending'=>0,'webmaster'=>'');
		$count = trim($this->input->post('count'));
		$from = trim($this->input->post('from'));
		if(!$count):
			show_404();
		endif;
		$webmasters = $this->mdusers->read_users_type(1,$count,$from);
		for($i=0;$i<count($webmasters);$i++):
			ob_start();
			?>
			<img src="<?=base_url();?>images/logo.png" alt="" />
			<p><strong>Здравствуйте, <?=$webmasters[$i]['login'];?></strong></p>
			<p>Поздравляем! Вас успешно зарегистрированы в статусе вебмастера.</p>
			<p>Ваша работа будет осуществляться через <a href="http://bystropost.ru/">личный кабинет.</a></p>
			<p>Для входа в личный кабинет используйте:</p>
			<p>Логин: <strong><?=$webmasters[$i]['login'];?></strong></p>
			<p>Пароль: <strong><?=$this->encrypt->decode($webmasters[$i]['cryptpassword']);?></strong></p>
			<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p>
			<?
			$mailtext = ob_get_clean();
			$this->email->clear(TRUE);
			$config['smtp_host'] = 'localhost';
			$config['charset'] = 'utf-8';
			$config['wordwrap'] = TRUE;
			$config['mailtype'] = 'html';
			
			$this->email->initialize($config);
			$this->email->to($webmasters[$i]['login']);
			$this->email->from('admin@bystropost.ru','Быстропост - система автоматической монетизации');
			$this->email->bcc('');
			$this->email->subject('Регистрация на Bystropost.ru');
			$this->email->message($mailtext);
			$this->email->send();
			$statusval['sending']++;
			$statusval['webmaster'] = $webmasters[$i]['login'];
		endfor;
		$statusval['count'] = $count;
		$statusval['from'] = $from;
		echo json_encode($statusval);
	}
	
	public function actions_forum(){
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Форум',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/actions-forum",$pagevar);
	}
	
	public function actions_balance(){
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Баланс',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'income'		=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$pagevar['income']['total'] = $this->mddelivesworks->calc_summ('wprice','2012-01-01',1);
		$pagevar['income']['ten'] = $this->mddelivesworks->calc_summ('wprice',date("Y-m-d",mktime(0,0,0,date("m"),date("d")-10,date("Y"))),1);
		$pagevar['income']['managers'] = $this->mddelivesworks->calc_summ('wprice-mprice','2012-01-01',1);
		$pagevar['income']['debt'] = $this->mddelivesworks->calc_summ('wprice','2012-01-01',0);
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/actions-balance",$pagevar);
	}
	
	public function messages_system(){
		
		$from = intval($this->uri->segment(5));
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Отправка системного сообщения',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('submit')):
			$_POST['submit'] = NULL;
			$this->form_validation->set_rules('group',' ','required|trim');
			$this->form_validation->set_rules('type',' ','required|trim');
			$this->form_validation->set_rules('text',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				$_POST['text'] = $this->replace_a_tag($_POST['text']);
				$id = $this->mdmessages->send_system_message($this->user['uid'],$_POST);
				if($id):
					$this->session->set_userdata('msgs','Сообщение отправлено');
				endif;
				$users = $this->mdusers->read_users_by_type($_POST['group']);
				for($i=0;$i<count($users);$i++):
					if($users[$i]['sendmail']):
						ob_start();
						?>
						<img src="<?=base_url();?>images/logo.png" alt="" />
						<p><strong>Здравствуйте, <?=$users[$i]['fio'];?></strong></p>
						<p>Получено новое сообщение.</p>
						<p>Что бы прочитать его войдите в <?=$this->link_cabinet($users[$i]['id']);?> и перейдите в раздел "Почта"</p>
						<p><br/><?=$this->sub_mailtext($_POST['text'],$users[$i]['id']);?><br/></p>
						<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p>
						<?
						$mailtext = ob_get_clean();
						
						$this->email->clear(TRUE);
						$config['smtp_host'] = 'localhost';
						$config['charset'] = 'utf-8';
						$config['wordwrap'] = TRUE;
						$config['mailtype'] = 'html';
						
						$this->email->initialize($config);
						$this->email->to($users[$i]['login']);
						$this->email->from('admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления');
						$this->email->bcc('');
						$this->email->subject('Bystropost.ru - Почта. Новое сообщение');
						$this->email->message($mailtext);
						$this->email->send();
					endif;
				endfor;
			endif;
			redirect($this->uri->uri_string());
		endif;
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/messages-system",$pagevar);
	}
	
	public function management_mails(){
		
		$from = intval($this->uri->segment(5));
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Личные сообщения',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'mails'			=> $this->mdunion->read_mails_admin_pages($this->user['uid'],5,$from),
					'count'			=> $this->mdunion->count_mails_admin_pages($this->user['uid']),
					'pages'			=> array(),
					'cntunit'		=> array('mails'=>0),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		if($this->input->post('mtsubmit')):
			$_POST['mtsubmit'] = NULL;
			$this->form_validation->set_rules('recipient',' ','required|trim');
			$this->form_validation->set_rules('text',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				$_POST['text'] = $this->replace_a_tag($_POST['text']);
				$id = $this->mdmessages->insert_record($this->user['uid'],$_POST['recipient'],$_POST['text']);
				if($id):
					if($this->mdusers->read_field($_POST['recipient'],'sendmail')):
						ob_start();
						?>
						<img src="<?=base_url();?>images/logo.png" alt="" />
						<p><strong>Здравствуйте, <?=$this->mdusers->read_field($_POST['recipient'],'fio');?></strong></p>
						<p>У Вас новое сообщение</p>
						<p>Что бы прочитать его войдите в <?=$this->link_cabinet($_POST['recipient']);?> и перейдите в раздел "Почта"</p>
						<p><br/><?=$this->sub_mailtext($_POST['text'],$_POST['recipient']);?><br/></p>
						<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p>
						<?
						$mailtext = ob_get_clean();
						
						$this->email->clear(TRUE);
						$config['smtp_host'] = 'localhost';
						$config['charset'] = 'utf-8';
						$config['wordwrap'] = TRUE;
						$config['mailtype'] = 'html';
						
						$this->email->initialize($config);
						$this->email->to($this->mdusers->read_field($_POST['recipient'],'login'));
						$this->email->from('admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления');
						$this->email->bcc('');
						$this->email->subject('Bystropost.ru - Почта. Новое сообщение');
						$this->email->message($mailtext);
						$this->email->send();
					endif;
					$this->session->set_userdata('msgs','Сообщение отправлено');
				endif;
				if(isset($_POST['sendmail'])):
					
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		for($i=0;$i<count($pagevar['mails']);$i++):
			$pagevar['mails'][$i]['date'] = $this->operation_dot_date_on_time($pagevar['mails'][$i]['date']);
		endfor;
		$config['base_url'] 	= $pagevar['baseurl'].'admin-panel/management/mails/from/';
		$config['uri_segment'] 	= 5;
		$config['total_rows'] 	= $pagevar['count'];
		$config['per_page'] 	= 5;
		$config['num_links'] 	= 4;
		$config['first_link']		= 'В начало';
		$config['last_link'] 		= 'В конец';
		$config['next_link'] 		= 'Далее &raquo;';
		$config['prev_link'] 		= '&laquo; Назад';
		$config['cur_tag_open']		= '<li class="active"><a href="#">';
		$config['cur_tag_close'] 	= '</a></li>';
		$config['full_tag_open'] 	= '<div class="pagination"><ul>';
		$config['full_tag_close'] 	= '</ul></div>';
		$config['first_tag_open'] 	= '<li>';
		$config['first_tag_close'] 	= '</li>';
		$config['last_tag_open'] 	= '<li>';
		$config['last_tag_close'] 	= '</li>';
		$config['next_tag_open'] 	= '<li>';
		$config['next_tag_close'] 	= '</li>';
		$config['prev_tag_open'] 	= '<li>';
		$config['prev_tag_close'] 	= '</li>';
		$config['num_tag_open'] 	= '<li>';
		$config['num_tag_close'] 	= '</li>';
		
		$this->pagination->initialize($config);
		$pagevar['pages'] = $this->pagination->create_links();
		$this->mdmessages->set_read_mails_by_admin($this->user['uid']);
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/messages-private",$pagevar);
	}
	
	public function messages_private_delete(){
		
		$mid = $this->uri->segment(6);
		if($mid):
			$result = $this->mdmessages->delete_record($mid);
			if($result):
				$this->session->set_userdata('msgs','Сообшение удалено успешно');
			else:
				$this->session->set_userdata('msgr','Сообшение не удалено');
			endif;
			redirect($_SERVER['HTTP_REFERER']);
		else:
			show_404();
		endif;
	}
	
	public function control_delete_msg_ticket(){
		
		$message = $this->uri->segment(6);
		if($message):
			$result = $this->mdtkmsgs->delete_record($message);
			if($result):
				$this->session->set_userdata('msgs','Сообшение удалено успешно');
			else:
				$this->session->set_userdata('msgr','Сообшение не удалено');
			endif;
			redirect($_SERVER['HTTP_REFERER']);
		else:
			show_404();
		endif;
	}
	
	public function actions_logoff(){
		
		$this->session->sess_destroy();
		redirect('');
	}
	
	public function reading_users_messages(){
		
		$from = intval($this->uri->segment(8));
		$user = intval($this->uri->segment(6));
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Просмотр сообщений',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'owner'			=> $this->mdusers->read_small_info($user),
					'mails'			=> $this->mdmessages->read_mails_user_pages($user,10,$from),
					'count'			=> $this->mdmessages->count_mails_user_pages($user),
					'pages'			=> array(),
					'cntunit'		=> array('mails'=>0),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		for($i=0;$i<count($pagevar['mails']);$i++):
			$pagevar['mails'][$i]['date'] = $this->operation_dot_date($pagevar['mails'][$i]['date']);
			$pagevar['mails'][$i]['recipient'] = $this->mdusers->read_field($pagevar['mails'][$i]['recipient'],'login');
			if(!$pagevar['mails'][$i]['recipient']):
				$pagevar['mails'][$i]['recipient'] = '<span class="active">Системное сообщение</span>';
			endif;
		endfor;
		
		$config['base_url'] 	= $pagevar['baseurl'].'admin-panel/management/users/read-messages/userid/'.$user.'/from/';
		$config['uri_segment'] 	= 8;
		$config['total_rows'] 	= $pagevar['count'];
		$config['per_page'] 	= 10;
		$config['num_links'] 	= 4;
		$config['first_link']		= 'В начало';
		$config['last_link'] 		= 'В конец';
		$config['next_link'] 		= 'Далее &raquo;';
		$config['prev_link'] 		= '&laquo; Назад';
		$config['cur_tag_open']		= '<li class="active"><a href="#">';
		$config['cur_tag_close'] 	= '</a></li>';
		$config['full_tag_open'] 	= '<div class="pagination"><ul>';
		$config['full_tag_close'] 	= '</ul></div>';
		$config['first_tag_open'] 	= '<li>';
		$config['first_tag_close'] 	= '</li>';
		$config['last_tag_open'] 	= '<li>';
		$config['last_tag_close'] 	= '</li>';
		$config['next_tag_open'] 	= '<li>';
		$config['next_tag_close'] 	= '</li>';
		$config['prev_tag_open'] 	= '<li>';
		$config['prev_tag_close'] 	= '</li>';
		$config['num_tag_open'] 	= '<li>';
		$config['num_tag_close'] 	= '</li>';
		
		$this->pagination->initialize($config);
		$pagevar['pages'] = $this->pagination->create_links();
		$this->mdmessages->set_read_mails_by_admin($this->user['uid']);
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/reading-users-messages",$pagevar);
	}
	
	public function calculate_tic(){
		
		$platforms = $this->mdplatforms->read_urls();
		for($i=0;$i<count($platforms);$i++):
			$oldtic = $this->mdplatforms->read_field($platforms[$i]['id'],'tic');
			$tic = $this->getTIC('http://'.$platforms[$i]['url']);
			$this->mdplatforms->update_field($platforms[$i]['id'],'tic',$tic);
			if($oldtic != $tic):
				$addwtic = 5; $addmtic = 2;
				if($oldtic < 30 AND $tic >= 30):
					$sqlquery = "UPDATE platforms SET ccontext=ccontext+$addwtic, mcontext=mcontext+$addmtic,cnotice=cnotice+$addwtic,mnotice=mnotice+$addmtic,clinkpic=clinkpic+$addwtic,mlinkpic=mlinkpic+$addmtic,cpressrel=cpressrel+$addwtic,mpressrel=mpressrel+$addmtic,clinkarh=clinkarh+$addwtic,mlinkarh=mlinkarh+$addmtic WHERE platforms.id = ".$platforms[$i]['id']." AND platforms.noticpr = 0";
					$this->mdplatforms->run_query($sqlquery);
				elseif($oldtic >= 30 AND $tic < 30):
					$sqlquery = "UPDATE platforms SET ccontext=ccontext-$addwtic, mcontext=mcontext-$addmtic,cnotice=cnotice-$addwtic,mnotice=mnotice-$addmtic,clinkpic=clinkpic-$addwtic,mlinkpic=mlinkpic-$addmtic,cpressrel=cpressrel-$addwtic,mpressrel=mpressrel-$addmtic,clinkarh=clinkarh-$addwtic,mlinkarh=mlinkarh-$addmtic WHERE platforms.id = ".$platforms[$i]['id']." AND platforms.noticpr = 0";
					$this->mdplatforms->run_query($sqlquery);
				endif;
			endif;
		endfor;
		$this->session->set_userdata('msgs','Яндекс тИЦ успешно вычислен');
		redirect('admin-panel/management/platforms');
	}
	
	public function calculate_pr(){
	
		$platforms = $this->mdplatforms->read_urls();
		for($i=0;$i<count($platforms);$i++):
			$this->mdplatforms->update_field($platforms[$i]['id'],'pr',$this->getpagerank($platforms[$i]['url']));
		endfor;
		$this->session->set_userdata('msgs','Google PageRank успешно вычислен');
		redirect('admin-panel/management/platforms');
	}

	public function management_services(){
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Дополнительные услуги',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'services'		=> $this->mdservices->read_records(),
					'valuesrv'		=> $this->mdvaluesrv->read_records(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		if($this->input->post('assubmit')):
			$_POST['amsubmit'] = NULL;
			$this->form_validation->set_rules('title',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				$result = $this->mdservices->insert_record($_POST);
				if($result):
					$this->session->set_userdata('msgs','Услуга добавлена успешно');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		if($this->input->post('asvsubmit')):
			$_POST['asvsubmit'] = NULL;
			$this->form_validation->set_rules('title',' ','required|trim');
			$this->form_validation->set_rules('sid',' ','required|trim');
			$this->form_validation->set_rules('wprice',' ','required|trim');
			$this->form_validation->set_rules('mprice',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				$result = $this->mdvaluesrv->insert_record($_POST);
				if($result):
					$this->session->set_userdata('msgs','Значение услуги добавлено успешно');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		if($this->input->post('essubmit')):
			$_POST['essubmit'] = NULL;
			$this->form_validation->set_rules('sid',' ','required|trim');
			$this->form_validation->set_rules('title',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				$result = $this->mdservices->update_record($_POST);
				if($result):
					$this->session->set_userdata('msgs','Услуга изменена успешно');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		if($this->input->post('esvsubmit')):
			$_POST['esvsubmit'] = NULL;
			$this->form_validation->set_rules('svid',' ','required|trim');
			$this->form_validation->set_rules('title',' ','required|trim');
			$this->form_validation->set_rules('wprice',' ','required|numeric|trim');
			$this->form_validation->set_rules('mprice',' ','required|numeric|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				if(!isset($_POST['delsrvvalue'])):
					$result = $this->mdvaluesrv->update_record($_POST);
					if($result):
						$this->session->set_userdata('msgs','Значение услуги сохранено успешно');
					endif;
				else:
					$result = $this->mdvaluesrv->delete_record($_POST['svid']);
					if($result):
						$this->session->set_userdata('msgs','Значение услуги удалено успешно');
					endif;
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/management-services",$pagevar);
	}
	
	public function management_services_deleting(){
		
		$sid = $this->uri->segment(5);
		if($sid):
			$attched = $this->mdattachedservices->service_admin_exist($sid);
			if(!$attched):
				$result = $this->mdservices->delete_record($sid);
				if($result):
					$this->mdvaluesrv->delete_records($sid);
					$this->session->set_userdata('msgs','Услуга удалена успешно');
				else:
					$this->session->set_userdata('msgr','Услуга не удалена');
				endif;
			else:
				$this->session->set_userdata('msgr','Услуга используется. Удалить не возможно.');
			endif;
			redirect('admin-panel/management/services');
		else:
			show_404();
		endif;
	}
	
	/*********************************************************** API *********************************************************/
	
	function actions_api(){
	
		$query = "select id from tkmsgs group by ticket order by date desc";
		$message = $this->mdtkmsgs->query_execute($query,TRUE);
		$query = '';
		for($i=0;$i<count($message);$i++):
			$query .= (string)$message[$i]['id'];
			if($i+1<count($message)):
				$query.=',';
			endif;
		endfor;
		$query_update = 'update tkmsgs set main = 1 where id IN ('.$query.')';
		$this->mdtkmsgs->query_execute($query_update,FALSE);
		print_r($query_update);
		/*$mass_data = array();
		$post = array('hash'=>'fe162efb2429ef9e83e42e43f8195148','action'=>'GetSitesFromAccount','param'=>'birzid=2&accid=76');
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,'http://megaopen.ru/api.php');
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_TIMEOUT,30);
		$data = curl_exec($ch);
		curl_close($ch);
		 if($data!==false):
			$res = json_decode($data, true);
			if((int)$res['status']==1):
				$mass_data = $res['data'];
			else:
				print_r($res['error']);
			endif;
		else:
			print_r('Нет данных для загрузки!');
		endif;
		print_r($mass_data);
		echo '<br/>Количество: '.count($mass_data).'<br/><br/>';
		$publication = 'По теме';
		$mrs = $mass_data[746]['param']['category'];
		foreach($mrs AS $key=>$mr):
			$publication = $mr;
			if($key == 0):
				$publication = $mr;
			endif;
		endforeach;*/
	/*======================== Загрузка вебмастеров начало ============================ */
		/*$data = array(); $cnt = 0;
		foreach($mass_data AS $key => $value):
			if($key):
				if($mass_data[$key]['email'] == 'Sacred3'):
					continue;
				endif;
				switch($mass_data[$key]['email']):
					case 'Sacred3' :continue; break;
					case 'chistyakoveo' :$mass_data[$key]['email'] = 'chistyakoveo@yandex.ru'; break;
					case 'dokmaster' :$mass_data[$key]['email'] = 'lingon@inbox.ru'; break;
					case 'Dolgoff' :$mass_data[$key]['email'] = 'ilya.dolgoff@gmail.com'; break;
					case 'Dolgoff' :$mass_data[$key]['email'] = 'ilya.dolgoff@gmail.com'; break;
					case 'TigerV' :$mass_data[$key]['email'] = 'wwwwizard@mail.ru'; break;
				endswitch;
				$data['login'] = $mass_data[$key]['email'];
				$data['password'] = $this->randomPassword(8);
				$data['fio'] = 'Имя не указанно';
				$data['wmid'] = '';
				$data['knowus'] = 'Загружен через API';
				$data['sendmail'] = 1;
				print_r('Обработка: '.$key.' Email: '.$data['login']);
				if(!$this->mdusers->user_exist('login ',$data['login'])):
					print_r(' Статус: Не уществует!');
					$uid = $this->mdusers->insert_record($data,1);
					if($uid):
						$this->mdusers->update_field($uid,'manager',2);
						$this->mdusers->update_field($uid,'remoteid',$key);
						$cnt++;
						print_r(' Добавлен. ID = '.$uid.'<br/>');
					else:
						print_r(' Не добавлен.<br/>');
					endif;
				else:
					print_r(' Статус: Существует! Не добавлен.<br/>');
				endif;
			endif;
		endforeach;
		print_r('Импортировнно: '.$cnt.' вебмастеров');*/
		/*=============================== Загрузка вебмастеров конец ============================*/
		/*======================== Дозагрузка аккаунтов на биржах начало ======================== */
		/*$data = array(); $cnt = 0;
		foreach($mass_data AS $key => $value):
			if($key):
				$data['id'] = $key;
				$data['market'] = $mass_data[$key]['bizhid'];
				$data['login'] = $mass_data[$key]['login'];
				$data['password'] = $mass_data[$key]['pass'];
				$data['webmaster'] = $mass_data[$key]['userid'];
				if($data['webmaster'] && $data['market']):
					if(!$this->mdwebmarkets->exist_market($data['id'])):
//						$this->mdwebmarkets->insert_record($data['id'],$data['webmaster'],$data);
						$user = $this->mdusers->read_record_remote($data['webmaster'],'login');
						echo 'ДАННЫЕ ЕСТЬ: ID-'.$data['id'].', ВМ-'.$data['webmaster'].', ЛОГИН-'.$data['login'].', ПАРОЛЬ-'.$data['password'].', БИРЖА-'.$data['market'].', ВЕБМАСТЕР: '.$user['login'].', ПАРОЛЬ: '.$this->encrypt->decode($user['cryptpassword']).'<br/>';
						$cnt++;
					endif;
				else:
					echo 'НЕТ ДАННЫХ: ID-'.$data['id'].', ВМ-'.$data['webmaster'].', ЛОГИН-'.$data['login'].', ПАРОЛЬ-'.$data['password'].', БИРЖА-'.$data['market'].'<br/>';
				endif;
			endif;
		endforeach;
		print_r('Импортированно: '.$cnt.' аккаунтов');*/
		/*======================== Дозагрузка аккаунтов на биржах конец ========================*/
	}
	
	/******************************************************** statistic ******************************************************/
	
	public function actions_statistic(){
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Администрирование | Статистика',
					'baseurl' 		=> base_url(),
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'stat'			=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$pagevar['stat']['to3days'] = $this->mddelivesworks->calc_debet('wprice',date("Y-m-d",mktime(0,0,0,date("m"),date("d")-3,date("Y"))),'=');
		$pagevar['stat']['to4days'] = $this->mddelivesworks->calc_debet('wprice',date("Y-m-d",mktime(0,0,0,date("m"),date("d")-4,date("Y"))),'=');
		$pagevar['stat']['to5days'] = $this->mddelivesworks->calc_debet('wprice-mprice',date("Y-m-d",mktime(0,0,0,date("m"),date("d")-5,date("Y"))),'=');
		$pagevar['stat']['from5days'] = $this->mddelivesworks->calc_debet('wprice',date("Y-m-d",mktime(0,0,0,date("m"),date("d")-5,date("Y"))),"<");
		
		$pagevar['cntunit']['users'] = $this->mdusers->count_all();
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_all();
		$pagevar['cntunit']['markets'] = $this->mdmarkets->count_all();
		$pagevar['cntunit']['services'] = $this->mdservices->count_all();
		$pagevar['cntunit']['twork'] = $this->mdtypeswork->count_all();
		$pagevar['cntunit']['mails'] = $this->mdmessages->count_records_by_admin_new($this->user['uid']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_all_records(0);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender(0);
		
		$this->load->view("admin_interface/actions-statistic",$pagevar);
	}

	public function alert_debet(){
		
		$statusval = array('status'=>TRUE,'count'=>0,'bdate'=>'');
		$days = trim($this->input->post('days'));
		if(!$days):
			show_404();
		endif;
		if($days<=5):
			$znak = '=';
		else:
			$znak = '<';
			$days = 5;
		endif;
		$date = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$days,date("Y")));
		$debetors = $this->mdunion->read_debetors_list($date,$znak);
		for($i=0;$i<count($debetors);$i++):
				ob_start();
				?>
				<img src="<?=base_url();?>images/logo.png" alt="" />
				<p><strong>Здравствуйте, <?=$debetors[$i]['ulogin'];?> </strong></p>
				<p>У Вас есть неоплаченные заявки <?=($days<=5)? 'за '.$days: 'старше 5' ;?> дня(-ей).</p>
				<?php if($days<5):?>
					<p>Напоминаем Вам. Если у Вас будут неоплаченные заявки старше 5 дней (включительно) то Ваш аккаун будет заблокирован до полного погашения задолженности.</p>
				<?php elseif($days>=5):?>
				<p>ВНИМАНИЕ! Ваш аккаунт заблокирован по причине задолженности. Оплатите завершенные работы от 5 дней (включительно) для разблокировки.</p>
				<?php endif;?>
				<p>Спасибо, что пользуетесь нашим сайтом!</p>
				<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p>
				<?
				$mailtext = ob_get_clean();
				
				$this->email->clear(TRUE);
				$config['smtp_host'] = 'localhost';
				$config['charset'] = 'utf-8';
				$config['wordwrap'] = TRUE;
				$config['mailtype'] = 'html';
				
				$this->email->initialize($config);
				$this->email->to($debetors[$i]['ulogin']);
				$this->email->from('admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления');
				$this->email->bcc('');
				$this->email->subject('Bystropost.ru - Уведомление о задолженности');
				$this->email->message($mailtext);
				$this->email->send();
				$statusval['count']++;
		endfor;
		$statusval['bdate'] = $date;
		echo json_encode($statusval);
	}
	
	public function locked_debet(){
		
		$statusval = array('status'=>TRUE,'debetors'=>0,'birzlock'=>0);
		$days = trim($this->input->post('days'));
		if(!$days):
			show_404();
		endif;
		if($days<=5):
			$znak = '=';
		elseif($days>5):
			$znak = '<';
		endif;
		$date = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-5,date("Y")));
		$statusval['debetors'] = $this->mdunion->update_debetors_status($date,$znak,1);
		if($statusval['debetors']):
			$debetors = $this->mdunion->debetors_webmarkets();
			for($i=0;$i<count($debetors);$i++):
				$param = 'accid='.$debetors[$i]['id'].'&birzid='.$debetors[$i]['market'].'&login='.$debetors[$i]['login'].'&pass='.base64_encode($this->encrypt->decode($debetors[$i]['cryptpassword'])).'&act=2';
				$this->API('UpdateAccount',$param);
				$statusval['birzlock']++;
			endfor;
		endif;
		echo json_encode($statusval);
	}

	/******************************************************** functions ******************************************************/
	
	public function replace_text_a_tag($string){
		
		$pattern = "/{(.+?)\|(http:\/\/.+?)}/i";
		$replacement = "<a href=\"\$2\">\$1</a>";
		return preg_replace($pattern, $replacement,$string);
	}
	
	public function replace_a_tag($string){
		
		$patterns[0] = "/&Prime;+?/";$patterns[1] = "/{+?/";$patterns[2] = "/}+?/";
		$replacements[2] = "";$replacements[1] = "<";$replacements[0] = ">";
		return preg_replace($patterns, $replacements, $string);
	}
}