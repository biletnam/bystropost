<div class="span3">
	<div class="well sidebar-nav">
		<ul class="nav nav-list">
			<li class="nav-header">Меню</li>
			<li num="already"><?=anchor('manager-panel/actions/control','Задания (<b>'.$cntunit['delivers']['paid'].'</b>/'.$cntunit['delivers']['total'].')',array('title'=>'Оплаченных/Всего'));?></li>
			<li num="platforms"><?=anchor('manager-panel/actions/platforms','Площадки ('.$cntunit['platforms'].')');?></li>
			<li num="mails"><?=anchor('manager-panel/actions/mails','Почта (<b>'.$cntunit['mails']['new'].'</b>/'.$cntunit['mails']['total'].')');?></li>
			<li num="tickets"><?=anchor('manager-panel/actions/tickets/inbox','Тикеты ('.$cntunit['tickets']['inbox'].'/'.$cntunit['tickets']['outbox'].')');?></li>
			<li num="forum"><?=anchor('#','Форум');?></li>
		</ul>
	</div>
</div>