<header class="admin">
	<div class="container">
		<div class="row header-subnav">
			<div class="span12">
				<?=anchor("news","Новости");?>
				<?=anchor("faq","FAQ");?>
				<?=anchor("manner-of-payment","Порядок оплаты");?>
				<?=anchor("partners-program","Партнерская программа",array('class'=>'strong'));?>
				<?=anchor('prices','Цены');?>
				<?=anchor("about-content","О контенте");?>
				<?=anchor("capabilities","Наши возможности");?>
				<?=anchor("interface","Интерфейс");?>
				<?=anchor("contacts","Контакты");?>
				<?=anchor("http://idea.bystropost.ru/","Ваши идеи");?>
				<?=anchor("users-ratings/webmasters","Отзывы");?>
			</div>
		</div>
		<div class="row">
			<div class="span7">
				<?=anchor("manager-panel/actions/control",' ',array('id'=>'logo'));?>
			</div>
			<div class="span5" style="padding-top:10px;">
				<div class="user-panel">
					<?php $this->load->view("topblock/managers");?>
				</div>
			</div>
		</div>
	</div>
</header>
<div class="container">
	<div class="row">
		<div class="span12">
			<h1 class="admin-h1">Система мониторинга и управления <span>/ Профиль &laquo;Менеджера&raquo;</span></h1>
		</div>
	</div>
</div>