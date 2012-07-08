<!DOCTYPE html>
<html lang="en">
<?php $this->load->view("clients_interface/includes/head");?>

<body>
	<?php $this->load->view("clients_interface/includes/header");?>
	
	<div class="container">
		<div class="row">
			<div class="span12">
				<?php $this->load->view("alert_messages/alert-error");?>
				<?php $this->load->view("alert_messages/alert-success");?>
				<div id="stable">
					<div id="panel_segments">
						<div class="panel_segment">
							<big><?=anchor('#','Готовые задания (0)');?></big>
							<img src="<?=$baseurl;?>images/panel_pic1.jpg">
							<div class="text">
								Перед началом продвижения сайта мы тщательно исследуем как сам сайт, так и рыночный спрос в интересующей
							</div>
						</div>
						<div class="panel_segment">
							<big><?=anchor('webmaster-panel/actions/platforms','Площадки ('.$cntunit['platforms'].')');?></big>
							<img src="<?=$baseurl;?>images/panel_pic2.jpg">
							<div class="text">
								Перед началом продвижения сайта мы тщательно исследуем как сам сайт, так и рыночный спрос в интересующей
							</div>
						</div>
						<div class="panel_segment">
							<big><?=anchor('webmaster-panel/actions/tickets','Тикеты ('.$cntunit['tickets'].')');?></big>
							<img src="<?=$baseurl;?>images/panel_pic3.jpg">
							<div class="text">
								Перед началом продвижения сайта мы тщательно исследуем как сам сайт, так и рыночный спрос в интересующей
							</div>
						</div>
						<div class="panel_segment">
							<big><?=anchor('webmaster-panel/actions/mails','Почта ('.$cntunit['mails'].')');?></big>
							<img src="<?=$baseurl;?>images/panel_pic4.jpg">
							<div class="text">
								Перед началом продвижения сайта мы тщательно исследуем как сам сайт, так и рыночный спрос в интересующей
							</div>
						</div>
						<div class="panel_segment">
							<big><?=anchor('#','Дополнительные услуги');?></big>
							<img src="<?=$baseurl;?>images/panel_pic5.jpg">
							<div class="text">
								Перед началом продвижения сайта мы тщательно исследуем как сам сайт, так и рыночный спрос в интересующей
							</div>
						</div>
						<div class="panel_segment">
							<big><?=anchor('#','Форум');?></big>
							<img src="<?=$baseurl;?>images/panel_pic6.jpg">
							<div class="text">
								Перед началом продвижения сайта мы тщательно исследуем как сам сайт, так и рыночный спрос в интересующей
							</div>
						</div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
	<?php $this->load->view("clients_interface/includes/footer");?>
	<?php $this->load->view("clients_interface/includes/scripts");?>
</body>
</html>