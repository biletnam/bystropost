<div class="span3">
	<div class="well sidebar-nav">
		<ul class="nav nav-list">
			<li class="nav-header">Списки</li>
			<li num="users"><?=anchor('admin-panel/management/users/webmasters','Пользователи ('.$cntunit['users'].')');?></li>
			<li num="platforms"><?=anchor('admin-panel/management/platforms','Площадки ('.$cntunit['platforms'].')');?></li>
			<li num="markets"><?=anchor('admin-panel/management/markets','Список бирж ('.$cntunit['markets'].')');?></li>
			<li num="promocode"><?=anchor('admin-panel/management/promocode','Промокоды');?></li>
			<li num="services"><?=anchor('admin-panel/management/services','Список доп.услуг ('.$cntunit['services'].')');?></li>
			<li num="types-of-work"><?=anchor('admin-panel/management/types-of-work','Типы работ ('.$cntunit['twork'].')');?></li>
			<li num="ratings"><?=anchor('admin-panel/management/ratings/webmasters','Отзывы о системе');?></li>
			<li num="events"><?=anchor('admin-panel/actions/events','Новости');?></li>
			<li num="partner-program"><?=anchor('admin-panel/actions/partner-program','Партнерская программа');?></li>
			<li class="nav-header">Связь</li>
			<li num="tickets-outbox"><?=anchor('admin-panel/actions/tickets-outbox','Исходящие тикеты ('.$cntunit['tickets_outbox'].')');?></li>
			<li num="tickets-inbox"><?=anchor('admin-panel/actions/tickets-inbox','Входящие тикеты ('.$cntunit['tickets_inbox'].')');?></li>
			<li num="mails"><?=anchor('admin-panel/management/mails','Почта (<b>'.$cntunit['mails'].'</b>)');?></li>
			<li num="system-message"><?=anchor('admin-panel/messages/system-message','Рассылка');?></li>
			<li class="nav-header">Действия</li>
			<li num="statistic"><?=anchor('admin-panel/actions/statistic','Статистика долгов');?></li>
			<li num="balance"><?=anchor('admin-panel/actions/balance','Баланс');?></li>
			<li num="forum"><?=anchor('admin-panel/actions/forum','Форум');?></li>
			<li num="log"><?=anchor('admin-panel/actions/log','Просмотреть лог');?></li>
			<li num="control"><?=anchor('admin-panel/actions/control','Дополнительно');?></li>
		</ul>
	</div>
</div>