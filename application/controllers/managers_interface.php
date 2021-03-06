<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Managers_interface extends MY_Controller{
	
	var $user = array('uid'=>0,'uname'=>'','ulogin'=>'','utype'=>'','signdate'=>'','balance'=>0);
	var $loginstatus = array('status'=>FALSE);
	
	function __construct(){
		
		parent::__construct();
		
		$cookieuid = $this->session->userdata('logon');
		if(isset($cookieuid) and !empty($cookieuid)):
			$this->user['uid'] = $this->session->userdata('userid');
			if($this->user['uid']):
				$userinfo = $this->mdusers->read_record($this->user['uid']);
				if($userinfo['type'] == 2):
					$this->user['ulogin'] 			= $userinfo['login'];
					$this->user['uname'] 			= $userinfo['fio'];
					$this->user['utype'] 			= $userinfo['type'];
					$this->user['signdate'] 		= $userinfo['signdate'];
					$this->user['balance'] 			= $userinfo['balance'];
					$this->loginstatus['status'] 	= TRUE;
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
		
		$from = intval($this->uri->segment(5));
		
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
		$cntunit = 25;
		if($this->session->userdata('jobscount') != ''):
			$cntunit = $this->session->userdata('jobscount');
		else:
			$this->session->set_userdata('jobscount',25);
		endif;
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Кабинет Менеджера | Выполненные задания',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'delivers'		=> $this->mdunion->delivers_works_manager($this->user['uid'],$this->session->userdata('jobscount'),$from,$this->session->userdata('jobsfilter')),
					'filter'		=> array('fpaid'=>$fpaid,'fnotpaid'=>$fnotpaid),
					'cntwork'		=> $cntunit,
					'cntunit'		=> array(),
					'pages'			=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		for($i=0;$i<count($pagevar['delivers']);$i++):
			$pagevar['delivers'][$i]['date'] = $this->operation_dot_date($pagevar['delivers'][$i]['date']);
		endfor;
		
		$config['base_url'] 		= $pagevar['baseurl'].'manager-panel/actions/control/from/';
		$config['uri_segment'] 		= 5;
		$config['total_rows'] 		= $this->mdunion->count_delivers_works_manager($this->user['uid'],$this->session->userdata('jobsfilter'));
		$config['per_page'] 		= $cntunit;
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
			$result = $this->mdunion->read_manager_jobs($this->user['uid'],$_POST['srdjid'],$_POST['srdjurl']);
			$pagevar['title'] .= 'Поиск выполнен';
			$pagevar['delivers'] = $result;
			$pagevar['pages'] = NULL;
		endif;
		
		for($i=0;$i<count($pagevar['delivers']);$i++):
			if(mb_strlen($pagevar['delivers'][$i]['ulrlink'],'UTF-8') > 15):
				$pagevar['delivers'][$i]['link'] = mb_substr($pagevar['delivers'][$i]['ulrlink'],0,15,'UTF-8');
				$pagevar['delivers'][$i]['link'] .= ' ... '.mb_substr($pagevar['delivers'][$i]['ulrlink'],strlen($pagevar['delivers'][$i]['ulrlink'])-10,10,'UTF-8');;
			else:
				$pagevar['delivers'][$i]['link'] = $pagevar['delivers'][$i]['ulrlink'];
			endif;
		endfor;
		
		$pagevar['cntunit']['delivers']['paid'] = $this->mddelivesworks->count_records_by_manager_status($this->user['uid'],1);
		$pagevar['cntunit']['delivers']['total'] = $this->mddelivesworks->count_all_manager($this->user['uid']);
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_records_by_manager($this->user['uid']);
		$pagevar['cntunit']['mails']['new'] = $this->mdmessages->count_records_by_recipient_new($this->user['uid'],$this->user['utype']);
		$pagevar['cntunit']['mails']['total'] = $this->mdmessages->count_records_by_recipient($this->user['uid'],$this->user['utype'],$this->user['signdate']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_records_by_recipient($this->user['uid']);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender($this->user['uid']);
		
		$this->load->view("managers_interface/control-panel",$pagevar);
	}
	
	public function control_profile(){
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Кабинет Менеджера | Профиль',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'user'			=> $this->mdusers->read_record($this->user['uid']),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$pagevar['user']['signdate'] = $this->operation_date($pagevar['user']['signdate']);
		
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
				$wmid = $this->mdusers->read_by_wmid($_POST['wmid']);
				if($wmid && $wmid != $this->user['uid']):
					$this->session->set_userdata('msgr','Ошибка. WMID уже зареристрирован!');
					redirect($this->uri->uri_string());
				endif;
				$result = $this->mdusers->update_record($_POST);
				if($result):
					$msgs = 'Личные данные успешно сохранены.<br/>'.$this->session->userdata('msgs');
					$this->session->set_userdata('msgs',$msgs);
				endif;
				redirect($this->uri->uri_string());
			endif;
		endif;
		
		$pagevar['cntunit']['delivers']['paid'] = $this->mddelivesworks->count_records_by_manager_status($this->user['uid'],1);
		$pagevar['cntunit']['delivers']['total'] = $this->mddelivesworks->count_all_manager($this->user['uid']);
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_records_by_manager($this->user['uid']);
		$pagevar['cntunit']['mails']['new'] = $this->mdmessages->count_records_by_recipient_new($this->user['uid'],$this->user['utype']);
		$pagevar['cntunit']['mails']['total'] = $this->mdmessages->count_records_by_recipient($this->user['uid'],$this->user['utype'],$this->user['signdate']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_records_by_recipient($this->user['uid']);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender($this->user['uid']);
		
		$this->load->view("managers_interface/manager-profile",$pagevar);
	}

	public function control_jobs_search(){
		
		$statusval = array('status'=>FALSE,'retvalue'=>'');
		$search = $this->input->post('squery');
		if(!$search) show_404();
		$jworks = $this->mddelivesworks->search_manager_jobs($this->user['uid'],$search);
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
	
	public function finished_jobs_count_page(){
		
		$statusval = array('status'=>TRUE,'countwork'=>25);
		$countwork = trim($this->input->post('countwork'));
		$this->session->set_userdata('jobscount',$statusval['countwork']);
		if(!$countwork):
			$this->session->set_userdata('jobscount','');
		else:
			$this->session->set_userdata('jobscount',$countwork);
			$statusval['countwork'] = $countwork;
		endif;
		echo json_encode($statusval);
	}
	
	/******************************************************** other ******************************************************/
	
	function views(){
	
		$type = $this->uri->segment(2);
		switch ($type):
			case 'market-profile'	:	$pagevar = array('markets'=>$this->mdmarkets->read_records(),'baseurl'=>base_url());
										$this->load->view('managers_interface/includes/markets-profile',$pagevar);
										break;
					default 		:	show_404();
		endswitch;
	}
	
	public function actions_logoff(){
		
		$this->session->sess_destroy();
		redirect('');
	}
	
	/***************************************************** platforms ******************************************************/
	
	public function control_platforms(){
		
		$from = intval($this->uri->segment(5));
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Кабинет Менеджера | Назначенные площадки',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'platforms'		=> $this->mdplatforms->read_records_by_manager($this->user['uid'],15,$from),
					'count'			=> $this->mdplatforms->count_records_by_manager($this->user['uid']),
					'pages'			=> array(),
					'workplatform'	=> $this->mdplatforms->count_works_records_by_manager($this->user['uid']),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$config['base_url'] 		= $pagevar['baseurl'].'manager-panel/actions/platforms/from/';
		$config['uri_segment'] 		= 5;
		$config['total_rows'] 		= $pagevar['count']; 
		$config['per_page'] 		= 15;
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
		
		$pagevar['cntunit']['delivers']['paid'] = $this->mddelivesworks->count_records_by_manager_status($this->user['uid'],1);
		$pagevar['cntunit']['delivers']['total'] = $this->mddelivesworks->count_all_manager($this->user['uid']);
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_records_by_manager($this->user['uid']);
		$pagevar['cntunit']['mails']['new'] = $this->mdmessages->count_records_by_recipient_new($this->user['uid'],$this->user['utype']);
		$pagevar['cntunit']['mails']['total'] = $this->mdmessages->count_records_by_recipient($this->user['uid'],$this->user['utype'],$this->user['signdate']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_records_by_recipient($this->user['uid']);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender($this->user['uid']);
		
		if($this->input->post('mtsubmit')):
			$_POST['mtsubmit'] = NULL;
			$this->form_validation->set_rules('pid',' ','required|trim');
			$this->form_validation->set_rules('text',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				$recipient = $this->mdplatforms->read_field($_POST['pid'],'webmaster');
				if($recipient):
					$id = $this->mdmessages->insert_record($this->user['uid'],$recipient,$_POST['text']);
					if($id):
						$this->mdmessages->send_noreply_message($this->user['uid'],0,2,5,'Менеджер '.$this->user['ulogin'].' написал письмо вебмастеру '.$this->mdusers->read_field($recipient,'login'));
						$this->session->set_userdata('msgs','Сообщение отправлено');
					endif;
					if(isset($_POST['sendmail'])):
						ob_start();
						?>
						<img src="<?=base_url();?>images/logo.png" alt="" />
						<p><strong>Здравствуйте, <?=$this->mdusers->read_field($recipient,'fio');?></strong></p>
						<p>У Вас новое сообщение</p>
						<p>Что бы прочитать его войдите в <?=$this->link_cabinet($recipient);?> и перейдите в раздел "Почта"</p>
						<p><br/><?=$this->sub_mailtext($_POST['text'],$recipient);?><br/></p>
						<br/><br/><p><a href="http://www.bystropost.ru/">С уважением, www.Bystropost.ru</a></p>
						<?
						$mailtext = ob_get_clean();
						
						$this->email->clear(TRUE);
						$config['smtp_host'] = 'localhost';
						$config['charset'] = 'utf-8';
						$config['wordwrap'] = TRUE;
						$config['mailtype'] = 'html';
						
						$this->email->initialize($config);
						$this->email->to($this->mdusers->read_field($recipient,'login'));
						$this->email->from('admin@bystropost.ru','Bystropost.ru - Система мониторинга и управления');
						$this->email->bcc('');
						$this->email->subject('Bystropost.ru - Почта. Новое сообщение');
						$this->email->message($mailtext);
						$this->email->send();
					endif;
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		if($this->input->post('scsubmit')):
			unset($_POST['scsubmit']);
			$result = $this->mdunion->read_platform($_POST['srplid'],$_POST['srplurl'],$this->user['uid']);
			$pagevar['title'] .= 'Кабинет Менеджера | Назначенные площадки | Поиск выполнен';
			$pagevar['platforms'] = $result;
			$pagevar['pages'] = NULL;
		endif;
		
		for($i=0;$i<count($pagevar['platforms']);$i++):
			$pagevar['platforms'][$i]['date'] = $this->operation_dot_date($pagevar['platforms'][$i]['date']);
		endfor;
		$this->session->set_userdata('backpath',$this->uri->uri_string());
		$this->load->view("managers_interface/control-platforms",$pagevar);
	}
	
	public function control_edit_platform(){
		
		$platform = $this->uri->segment(5);
		if(!$this->mdplatforms->ownew_manager_platform($this->user['uid'],$platform)):
			show_404();
		endif;
		$webmaster = $this->mdplatforms->read_field($platform,'webmaster');
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Кабинет Менеджера | Назначенные площадки | Редактирование площадки',
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
		
		$pagevar['userinfo']['remote'] = TRUE;
		
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
			redirect('manager-panel/actions/platforms');
		endif;
		for($i=0;$i<count($pagevar['mymarkets']);$i++):
			$pagevar['mymarkets'][$i]['password'] = $this->encrypt->decode($pagevar['mymarkets'][$i]['cryptpassword']);
		endfor;
		if(!$pagevar['platform']['imgwidth'] && !$pagevar['platform']['imgheight']):
			$pagevar['platform']['imgstatus'] = 0;
			$pagevar['platform']['imgwidth'] = '';
			$pagevar['platform']['imgheight'] = '';
		endif;
		
		$this->load->view("managers_interface/control-edit-platform",$pagevar);
	}
	
	public function control_view_platform(){
		
		$platform = $this->uri->segment(5);
		if(!$this->mdplatforms->ownew_manager_platform($this->user['uid'],$platform)):
			redirect('manager-panel/actions/platforms');
		endif;
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Кабинет Менеджера | Назначенные площадки | Просмотр площадки',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'platform'		=> $this->mdplatforms->read_record($platform),
					'markets'		=> $this->mdmarkets->read_records(),
					'mymarkets'		=> array(),
					'services'		=> array(),
					'thematic'		=> $this->mdthematic->read_records(),
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
		
		$this->load->view("managers_interface/control-view-platform",$pagevar);
	}
	
	public function search_platforms(){
		
		$statusval = array('status'=>FALSE,'retvalue'=>'');
		$search = $this->input->post('squery');
		if(!$search) show_404();
		$platforms = $this->mdplatforms->search_platforms($search,$this->user['uid']);
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
	
	/**************************************************** deliver work ****************************************************/
	
	public function deliver_work(){
		
		$platform = $this->uri->segment(4);
		
		if(!$this->mdplatforms->ownew_manager_platform($this->user['uid'],$platform)):
			redirect('manager-panel/actions/platforms');
		endif;
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Кабинет Менеджера | Сдача задания',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'cntunit'		=> array(),
					'typeswork'		=> $this->mdtypeswork->read_records(),
					'markets'		=> $this->mdmarkets->read_records(),
					'platform'		=> $this->mdplatforms->read_record($platform),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('submit')):
			$_POST['submit'] = NULL;
			$this->form_validation->set_rules('typework',' ','required|trim');
			$this->form_validation->set_rules('market',' ','required|trim');
			$this->form_validation->set_rules('mkprice',' ','required|trim');
			$this->form_validation->set_rules('ulrlink',' ','required|prep_url|trim');
			$this->form_validation->set_rules('countchars',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка при сохранении. Не заполены необходимые поля.');
			else:
				if(!strstr($_POST['ulrlink'],$pagevar['platform']['url'])):
					$this->session->set_userdata('msgr','URL не пренадлежит площадке. Повторите ввод.');
					redirect($this->uri->uri_string());
				endif;
				$webmaster = $this->mdplatforms->read_field($platform,'webmaster');
				$nickname = $this->mdtypeswork->read_field($_POST['typework'],'nickname');
				$wprice = $this->mdplatforms->read_field($platform,'c'.$nickname);
				$mprice = $this->mdplatforms->read_field($platform,'m'.$nickname);
				if($webmaster):
					$work = $this->mddelivesworks->insert_record($webmaster,$platform,$this->user['uid'],$wprice,$mprice,$_POST);
					if($work):
						$user = $this->mdusers->read_record($webmaster);
						if($user['autopaid'] && ($user['balance'] >= $wprice)):
							$this->mdusers->change_user_balance($webmaster,-$wprice);
							$this->mddelivesworks->update_status_ones($webmaster,$work);
							$this->mdusers->change_user_balance($this->user['uid'],$mprice);
							$this->mdusers->change_admins_balance($wprice-$mprice);
							$this->mdfillup->insert_record(0,$wprice-$mprice,'Начисление средств администратору при автоматической оплате (Ручной ввод)');
							$this->mdlog->insert_record($webmaster,'Событие №11: Произведена оплата за выполненные работы');
							if($user['partner_id']):
								$pprice = round($wprice*0.05,2);
								$this->mdusers->change_user_balance($user['partner_id'],$pprice);
								$this->mdfillup->insert_record($user['partner_id'],$pprice,'Средства по партнерской программе',0,1);
							endif;
						endif;
						$this->mdlog->insert_record($this->user['uid'],'Событие №21: Состояние задания - сдано');
						$this->session->set_userdata('msgs','Отчет о выполенной работе создан');
					endif;
				else:
					$this->session->set_userdata('msgr','Отчет о выполенной работе не создан');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		$arr = array(); $i = 0;
		foreach($pagevar['platform'] as $key => $value):
			$arr[$i] = $value;
			$i++;
		endforeach;
		$pagevar['typeswork'][0]['mprice'] = $arr[23]; //context
		$pagevar['typeswork'][1]['mprice'] = $arr[25]; //notice
		$pagevar['typeswork'][2]['mprice'] = $arr[27]; //rewiew
		$pagevar['typeswork'][3]['mprice'] = $arr[31]; //linkpic
		$pagevar['typeswork'][4]['mprice'] = $arr[33]; //press
		$pagevar['typeswork'][5]['mprice'] = $arr[35]; //linkarh
		$pagevar['typeswork'][6]['mprice'] = $arr[29]; //news
		
		$pagevar['platform']['date'] = $this->operation_dot_date($pagevar['platform']['date']);
		
		$pagevar['cntunit']['delivers']['paid'] = $this->mddelivesworks->count_records_by_manager_status($this->user['uid'],1);
		$pagevar['cntunit']['delivers']['total'] = $this->mddelivesworks->count_all_manager($this->user['uid']);
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_records_by_manager($this->user['uid']);
		$pagevar['cntunit']['mails']['new'] = $this->mdmessages->count_records_by_recipient_new($this->user['uid'],$this->user['utype']);
		$pagevar['cntunit']['mails']['total'] = $this->mdmessages->count_records_by_recipient($this->user['uid'],$this->user['utype'],$this->user['signdate']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_records_by_recipient($this->user['uid']);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender($this->user['uid']);
		
		$this->load->view("managers_interface/deliver-work",$pagevar);
	}
	
	public function remote_deliver_work(){
		
		$statusval = array('nextstep'=>TRUE,'plcount'=>0,'count'=>'','from'=>'','wkol'=>0,'datefrom'=>'','dateto'=>'');
		$count = trim($this->input->post('count'));
		$from = trim($this->input->post('from'));
		if(!$count):
			show_404();
		endif;
		$datefrom = date("Y-m-d",mktime(0,0,0,date("m")-1,date("d"),date("Y")));
//		$datefrom = "2012-11-01";
		$dateto = date("Y-m-d");
		$platforms = $this->mdplatforms->read_managers_platform_remote($this->user['uid'],$count,$from);
		if(!count($platforms)):
			$statusval['nextstep'] = FALSE;
		else:
			$markets = $this->mdmarkets->read_records();
			$typeswork = $this->mdtypeswork->read_records_id();
			for($pl=0;$pl<count($platforms);$pl++):
				$remote_webmaster = $this->mdusers->read_field($platforms[$pl]['webmaster'],'remoteid');
				if(!$remote_webmaster):
					continue;
				endif;
				$webmarkets = $this->mdwebmarkets->read_records($remote_webmaster);
				for($mk=0;$mk<count($markets);$mk++):
					for($wmk=0;$wmk<count($webmarkets);$wmk++):
						if($webmarkets[$wmk]['market'] == $markets[$mk]['id']):
							$param = 'birzid='.$markets[$mk]['id'].'&accid='.$webmarkets[$wmk]['id'].'&datefrom='.$datefrom.'&dateto='.$dateto;
							$deliver_works = $this->API('GetFinishedOrder',$param);
							if($deliver_works):
								$dwd = 0;
								$dw_data = array();
								foreach($deliver_works as $key => $value):
									$dw_data[$dwd] = $value;
									$dw_data[$dwd]['id'] = $key;
									$dwd++;
								endforeach;
								for($dwd=0;$dwd<count($dw_data);$dwd++):
									if($platforms[$pl]['remoteid'] === $dw_data[$dwd]['siteid']):
										if($dw_data[$dwd]['type'] <= 7):
											$new_work['id'] 		= $dw_data[$dwd]['id'];
											$new_work['webmaster'] 	= $platforms[$pl]['webmaster'];
											$new_work['platform'] 	= $dw_data[$dwd]['siteid'];
											$new_work['manager'] 	= $this->user['uid'];
											$new_work['typework'] 	= $dw_data[$dwd]['type'];
											$new_work['market'] 	= $markets[$mk]['id'];
											$new_work['mkprice'] 	= ( isset($dw_data[$dwd]['birzprice']) && !is_null($dw_data[$dwd]['birzprice']) ) ? $dw_data[$dwd]['birzprice'] : 0; // andrewgs
											$new_work['ulrlink'] 	= $dw_data[$dwd]['link'];
											$new_work['countchars'] = ( isset($dw_data[$dwd]['size']) && !is_null($dw_data[$dwd]['size']) ) ? $dw_data[$dwd]['size'] : 0; // andrewgs
											if(isset($dw_data[$dwd]['our_price']) && isset($dw_data[$dwd]['client_price'])):
												$new_work['wprice']	= $dw_data[$dwd]['client_price'];
												$new_work['mprice']	= $dw_data[$dwd]['our_price'];
											else:
												$new_work['wprice']	= $this->mdplatforms->read_field($platforms[$pl]['id'],'c'.$typeswork[$dw_data[$dwd]['type']-1]['nickname']);
												$new_work['mprice']	= $this->mdplatforms->read_field($platforms[$pl]['id'],'m'.$typeswork[$dw_data[$dwd]['type']-1]['nickname']);
											endif;
											$new_work['status'] 	= 0;
											$new_work['date'] 		= $dw_data[$dwd]['date'];
											$new_work['datepaid'] 	= '0000-00-00';
											
											if(!$this->mddelivesworks->exist_work($new_work['id'])):
												$work = $this->mddelivesworks->insert_record($new_work['webmaster'],$platforms[$pl]['id'],$this->user['uid'],$new_work['wprice'],$new_work['mprice'],$new_work);
												if($work):
													$user = $this->mdusers->read_record($new_work['webmaster']);
													if($user['autopaid'] && ($user['balance'] >= $new_work['wprice'])):
														$this->mdusers->change_user_balance($new_work['webmaster'],-$new_work['wprice']);
														$this->mddelivesworks->update_status_ones($new_work['webmaster'],$work);
														$this->mdusers->change_user_balance(2,$new_work['mprice']);
														$this->mdusers->change_admins_balance($new_work['wprice']-$new_work['mprice']);
														$this->mdfillup->insert_record(0,$new_work['wprice']-$new_work['mprice'],'Начисление средств администратору при автоматической плате (Автоматический ввод)');
														if($user['partner_id']):
															$pprice = round($new_work['wprice']*0.05,2);
															$this->mdusers->change_user_balance($user['partner_id'],$pprice);
															$this->mdfillup->insert_record($user['partner_id'],$pprice,'Средства по партнерской программе',0,1);
														endif;
													endif;
												endif;
												$statusval['wkol']++;
											else:
												continue;
											endif;
										endif;
									endif;
								endfor;
							endif;
						endif;
					endfor;
				endfor;
			endfor;
		endif;
		$statusval['plcount'] = count($platforms);
		$statusval['count'] = $count;
		$statusval['from'] = $from;
		$statusval['datefrom'] = $datefrom;
		$statusval['dateto'] = $dateto;
		echo json_encode($statusval);
	}

	/******************************************************* mails *********************************************************/
	
	public function control_mails(){
		
		$from = intval($this->uri->segment(5));
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Кабинет Менеджера | Входящие сообщения',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'mails'			=> $this->mdunion->read_mails_by_recipient($this->user['uid'],$this->user['utype'],$this->user['signdate'],10,$from),
					'cntunit'		=> array(),
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
		
		for($i=0;$i<count($pagevar['mails']);$i++):
			$pagevar['mails'][$i]['date'] = $this->operation_dot_date_on_time($pagevar['mails'][$i]['date']);
		endfor;
		
		$config['base_url'] 	= $pagevar['baseurl'].'manager-panel/actions/mails/from/';
		$config['uri_segment'] 	= 5;
		$config['total_rows'] 	= $this->mdunion->count_mails_by_recipient($this->user['uid'],$this->user['utype'],$this->user['signdate']);
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
		$this->mdmessages->set_read_mails_by_recipient($this->user['uid'],$this->user['utype']);
		
		$pagevar['cntunit']['delivers']['paid'] = $this->mddelivesworks->count_records_by_manager_status($this->user['uid'],1);
		$pagevar['cntunit']['delivers']['total'] = $this->mddelivesworks->count_all_manager($this->user['uid']);
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_records_by_manager($this->user['uid']);
		$pagevar['cntunit']['mails']['new'] = $this->mdmessages->count_records_by_recipient_new($this->user['uid'],$this->user['utype']);
		$pagevar['cntunit']['mails']['total'] = $this->mdmessages->count_records_by_recipient($this->user['uid'],$this->user['utype'],$this->user['signdate']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_records_by_recipient($this->user['uid']);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender($this->user['uid']);
		
		$this->load->view("managers_interface/control-mails",$pagevar);
	}
	
	public function control_delete_mail(){
		
		$mid = $this->uri->segment(6);
		if($mid):
			if($this->mdmessages->is_system($mid)):
				redirect('manager-panel/actions/mails');
			endif;
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
	
	/****************************************************** tickets ******************************************************/
	
	public function ticket_create(){
		
		if($this->mdplatforms->ownew_manager_platform($this->user['uid'],$this->uri->segment(5),array(0,1))):
			$this->session->set_flashdata('platform_ticket',$this->mdplatforms->read_field($this->uri->segment(5),'url'));
			redirect('manager-panel/actions/tickets-outbox');
		endif;
		redirect('manager-panel/actions/platforms');
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
					'title'			=> 'Кабинет Менеджера | Исходящие тикеты',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'tickets'		=> $this->mdunion->read_tickets_by_sender($this->user['uid'],5,$from,$hideticket),
					'platforms'		=> array(),
					'create_ticket'	=> $this->session->flashdata('platform_ticket'),
					'hideticket'	=> $hideticket,
					'pages'			=> $this->pagination('manager-panel/actions/tickets-outbox',5,$this->mdunion->count_tickets_by_sender($this->user['uid'],$hideticket),5),
					'cntunit'		=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$platforms = $this->mdplatforms->platforms_by_manager($this->user['uid'],'id,url','id');
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
				$recipient = 0;
				$platform_id = $this->mdplatforms->exist_platform($ticket_data['platform']);
				if($this->mdplatforms->ownew_manager_platform($this->user['uid'],$platform_id)):
					if($ticket_data['type'] == 1):
						$webmaster = $this->mdplatforms->read_field($platform_id,'webmaster');
						if($webmaster):
							$recipient = $webmaster;
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
					$ticket = $this->mdtickets->insert_record($this->user['uid'],$recipient,$ticket_data);
					if($ticket):
						$this->admin_ticket_mail($this->user['ulogin'],$ticket);
						$this->mdtkmsgs->insert_record($this->user['uid'],$ticket,$this->user['uid'],$recipient,1,$this->replace_href_tag($ticket_data['text']));
						$this->mdlog->insert_record($this->user['uid'],'Событие №17: Состояние тикета - создан');
						$this->session->set_userdata('msgs','Тикет успешно создан.');
						$this->mdmessages->send_noreply_message($this->user['uid'],$recipient,2,$this->mdusers->read_field($recipient,'type'),'Новое сообщение через тикет-систему. Тикет ID: '.$ticket);
						if($recipient):
							$this->mdmessages->send_noreply_message($this->user['uid'],0,2,5,'Менеджер создал тикет для вебмастера');
						else:
							$this->mdmessages->send_noreply_message($this->user['uid'],0,2,5,'Новое сообщение через тикет-систему');
						endif;
					endif;
				else:
					$this->session->set_userdata('msgr','Ошибка. Не верно указана площадка.');
				endif;
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		$pagevar['cntunit']['delivers']['paid'] = $this->mddelivesworks->count_records_by_manager_status($this->user['uid'],1);
		$pagevar['cntunit']['delivers']['total'] = $this->mddelivesworks->count_all_manager($this->user['uid']);
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_records_by_manager($this->user['uid']);
		$pagevar['cntunit']['mails']['new'] = $this->mdmessages->count_records_by_recipient_new($this->user['uid'],$this->user['utype']);
		$pagevar['cntunit']['mails']['total'] = $this->mdmessages->count_records_by_recipient($this->user['uid'],$this->user['utype'],$this->user['signdate']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_records_by_recipient($this->user['uid']);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender($this->user['uid']);
		
		for($i=0;$i<count($pagevar['tickets']);$i++):
			$pagevar['tickets'][$i]['date'] = $this->operation_dot_date_on_time($pagevar['tickets'][$i]['date']);
			$pagevar['tickets'][$i]['msg_date'] = $this->operation_dot_date_on_time($this->mdtkmsgs->in_finish_message_date($this->user['uid'],$pagevar['tickets'][$i]['id']));
			$finish_sender = $this->mdtkmsgs->in_finish_message_sender($this->user['uid'],$pagevar['tickets'][$i]['id']);
			if($finish_sender):
				$pagevar['tickets'][$i]['msg_sender'] = $this->mdusers->read_field($finish_sender,'position');
			elseif($finish_sender == '0'):
				$pagevar['tickets'][$i]['msg_sender'] = 'Администратор';
			else:
				$pagevar['tickets'][$i]['msg_sender'] = 'Без ответа';
			endif;
			if($pagevar['tickets'][$i]['recipient']):
				$pagevar['tickets'][$i]['position'] = $this->mdusers->read_field($pagevar['tickets'][$i]['recipient'],'login');
			else:
				$pagevar['tickets'][$i]['position'] = 'Администратору';
			endif;
		endfor;
		$this->session->set_userdata('backpath',$this->uri->uri_string());
		$this->load->view("managers_interface/tickets/outbox",$pagevar);
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
					'title'			=> 'Кабинет Менеджера | Входящие тикеты',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'tickets'		=> $this->mdunion->read_tickets_by_recipient($this->user['uid'],5,$from,$hideticket),
					'hideticket'	=> $hideticket,
					'pages'			=> $this->pagination('manager-panel/actions/tickets-inbox',5,$this->mdunion->count_tickets_by_recipient($this->user['uid'],$hideticket),5),
					'cntunit'		=> array(),
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr')
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$pagevar['cntunit']['delivers']['paid'] = $this->mddelivesworks->count_records_by_manager_status($this->user['uid'],1);
		$pagevar['cntunit']['delivers']['total'] = $this->mddelivesworks->count_all_manager($this->user['uid']);
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_records_by_manager($this->user['uid']);
		$pagevar['cntunit']['mails']['new'] = $this->mdmessages->count_records_by_recipient_new($this->user['uid'],$this->user['utype']);
		$pagevar['cntunit']['mails']['total'] = $this->mdmessages->count_records_by_recipient($this->user['uid'],$this->user['utype'],$this->user['signdate']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_records_by_recipient($this->user['uid']);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender($this->user['uid']);
		
		for($i=0;$i<count($pagevar['tickets']);$i++):
			$pagevar['tickets'][$i]['date'] = $this->operation_dot_date_on_time($pagevar['tickets'][$i]['date']);
			$pagevar['tickets'][$i]['msg_date'] = $this->operation_dot_date_on_time($this->mdtkmsgs->in_finish_message_date($this->user['uid'],$pagevar['tickets'][$i]['id']));
			$finish_sender = $this->mdtkmsgs->in_finish_message_sender($this->user['uid'],$pagevar['tickets'][$i]['id']);
			if($finish_sender):
				$pagevar['tickets'][$i]['msg_sender'] = $this->mdusers->read_field($finish_sender,'position');
			elseif($finish_sender == '0'):
				$pagevar['tickets'][$i]['msg_sender'] = 'Администратор';
			else:
				$pagevar['tickets'][$i]['msg_sender'] = 'Без ответа';
			endif;
			if($pagevar['tickets'][$i]['sender']):
				$pagevar['tickets'][$i]['position'] = $this->mdusers->read_field($pagevar['tickets'][$i]['sender'],'login');
			else:
				$pagevar['tickets'][$i]['position'] = 'Администратор';
			endif;
		endfor;
		$this->session->set_userdata('backpath',$this->uri->uri_string());
		$this->load->view("managers_interface/tickets/inbox",$pagevar);
	}
	
	public function read_ticket(){
		
		$ticket = $this->uri->segment(5);
		$from = intval($this->uri->segment(7));
		if(!$this->mdtickets->ownew_ticket_or_recipient($this->user['uid'],$ticket)):
			redirect('manager-panel/actions/control');
		endif;
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Кабинет Менеджера | Тикеты | Чтение тикета',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'ticket'		=> $this->mdunion->view_ticket_info($ticket),
					'messages'		=> $this->mdunion->read_messages_by_ticket_pages($ticket,7,$from),
					'pages'			=> $this->pagination("manager-panel/actions/".$this->uri->segment(3)."/read-ticket-id/$ticket",7,$this->mdunion->count_messages_by_ticket($ticket),7),
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
				$this->mdmessages->send_noreply_message($this->user['uid'],$recipient,2,$this->mdusers->read_field($recipient,'type'),'Менеджер закрыл тикет ID: '.$ticket);
			else:
				if(empty($message['text'])):
					$this->session->set_userdata('msgr','Ошибка. Не заполены необходимые поля.');
					redirect($this->uri->uri_string());
				endif;
			endif;
			if(!empty($message['text'])):
				
				$result = $this->mdtkmsgs->insert_record($this->user['uid'],$ticket,$this->user['uid'],$recipient,0,$this->replace_href_tag($message['text']));
				if($result):
					if($this->user['uid'] == $pagevar['ticket']['recipient']):
						$this->mdtickets->update_field($ticket,'recipient_answer',1);
						$this->mdtickets->update_field($ticket,'sender_answer',0);
						$this->mdtickets->update_field($ticket,'sender_reading',0);
					elseif($this->user['uid'] == $pagevar['ticket']['sender']):
						$this->mdtickets->update_field($ticket,'sender_answer',1);
						$this->mdtickets->update_field($ticket,'recipient_answer',0);
						$this->mdtickets->update_field($ticket,'recipient_reading',0);
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
					$this->admin_ticket_mail($this->user['ulogin'],$ticket);
				endif;
			endif;
			if(isset($message['closeticket'])):
				redirect($this->session->userdata('backpath'));
			else:
				redirect($this->uri->uri_string());
			endif;
		endif;
		
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
		if($this->user['uid'] == $pagevar['ticket']['recipient']):
			$this->mdtickets->update_field($ticket,'recipient_reading',1);
		elseif($this->user['uid'] == $pagevar['ticket']['sender']):
			$this->mdtickets->update_field($ticket,'sender_reading',1);
		endif;
		
		$pagevar['cntunit']['delivers']['paid'] = $this->mddelivesworks->count_records_by_manager_status($this->user['uid'],1);
		$pagevar['cntunit']['delivers']['total'] = $this->mddelivesworks->count_all_manager($this->user['uid']);
		$pagevar['cntunit']['platforms'] = $this->mdplatforms->count_records_by_manager($this->user['uid']);
		$pagevar['cntunit']['mails']['new'] = $this->mdmessages->count_records_by_recipient_new($this->user['uid'],$this->user['utype']);
		$pagevar['cntunit']['mails']['total'] = $this->mdmessages->count_records_by_recipient($this->user['uid'],$this->user['utype'],$this->user['signdate']);
		$pagevar['cntunit']['tickets_inbox'] = $this->mdtickets->count_records_by_recipient($this->user['uid']);
		$pagevar['cntunit']['tickets_outbox'] = $this->mdtickets->count_records_by_sender($this->user['uid']);
		
		$this->load->view("managers_interface/tickets/messages",$pagevar);
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
	
	/**************************************************** functions ******************************************************/
	
	public function escaping_domen($domen){
			
		$list = preg_split("/\./",$domen);
		return implode("\.", $list);
	}
}