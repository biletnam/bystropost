<!DOCTYPE html>
<html>
<?php $this->load->view("users_interface/includes/head");?>

<body>
	<div class="container">
		<?php $this->load->view("users_interface/includes/navigation");?>
		<?php $this->load->view("users_interface/includes/header");?>
		<div class="clear"></div>
		<div class="row smaller">
			<div class="span12">
				<div id="register_tree" class="no-margin">
					<?=anchor('users/registering/optimizer','<img src="'.$baseurl.'images/spacer.gif" width="500px" height="450px" border="0">');?>
				</div>			
				<p>
					Система Быстропост для Оптимизаторов очень проста и требует минимальных знаний для того, чтобы вы поняли, как она работает. 
					Стоимость услуги фиксирована, 20% от вашего баланса рекламной кампании. А теперь внимательно прочитайте текст о самой системе.
				</p>
				<p>
					Исходя из общего числа сайтов в различных биржах, для которых мы выполняем задания от оптимизаторов в новом контенте, учитывая 
					все требования которые не противоречат правилам системы и желаниям заказчика, мы можем предложить вам качественное размещение.
				</p>
				<p>
					Как всё происходит? В ручную отсеивается список площадок, который оптимально подходит к вашей рекламной компании как донор для вашего сайта. А именно, 
					рассчитывается общий сео-эффект, исходя из заспамленности ресурса, качества ресурса, его тематики и многое другое.
				</p>
				<p>
					Остальное, дело рук уже рерайтеров, которые качественно напишут текст и используют ваши ключевые слова. Все работы делаются в 
					исключительно новом и уникальном контенте. Индексация таких ссылок безупречная, ведь эти сайты у нас в системе и мы знаем все их ньюансы.
					И самое главное, всё происходит внутри системы. Никаких прямых переводов. Вы просто пополняете счет в бирже и предоставляете доступ к 
					аккаунту. На данный момент, минимальная консультация плюс + составление ключевых слов осуществляется бесплатно.	
				</p>
				<p>
					И помните, работая с нами, вы экономите время и нервы. А это самое дорогое что у нас нет.
					Для того чтобы отдать пыльную работу и заняться уже сейчас продвижением своего сайта, нажмите на эту красивую кнопочку ниже.
				</p>
			</div>
		</div>
	</div>
	<?php $this->load->view("users_interface/includes/footer");?>
	<?php $this->load->view("users_interface/includes/scripts");?>
</body>
</html>