<?=form_open($this->uri->uri_string(),array('class'=>'form-horizontal')); ?>
	<input type="hidden" class="idMail" value="" name="mid" />
	<input type="hidden" class="idUser" value="" name="recipient" />
	<div id="mailUser" class="modal hide fade dmodal">
		<div class="modal-header">
			<a class="close" data-dismiss="modal">×</a>
			<h3>Отправка письма пользователю</h3>
		</div>
		<div class="modal-body">
			<fieldset>
				<div class="control-group">
					<label for="fio" class="control-label">Имя пользователя: </label>
					<div class="controls">
						<input type="text" disabled="disabled" class="input-xlarge mtinput eFio" name="fio">
						<span class="help-inline" style="display:none;">&nbsp;</span>
					</div>
				</div>
				<div class="control-group">
					<label for="login" class="control-label">Логин пользователя: </label>
					<div class="controls">
						<input type="text" disabled="disabled" class="input-xlarge mtinput eLogin" name="login">
						<span class="help-inline" style="display:none;">&nbsp;</span>
					</div>
				</div>
				<div class="control-group">
					<label for="text" class="control-label">Текст сообщения: </label>
					<div class="controls">
						<textarea id="mailText" name="text" class="input-xlarge mtinput" rows="8"></textarea>
						<span class="help-inline" style="display:none;">&nbsp;</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="sendmail"></label>
					<div class="controls">
						<label class="checkbox">
							<input id="sendMail" name="sendmail" type="checkbox" value="1" checked="checked">
							Установите если нужно уведомить по почте
						</label>
					<?php if($this->uri->segment(3) == 'tickets'):?>
						<label class="checkbox">
							<input id="closeTicket" name="closeticket" type="checkbox" value="1">
							Закрыть тикет
						</label>
					<?php endif;?>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="modal-footer">
			<div style="float:left"><?=anchor('','Просмотреть все сообщения',array('class'=>'ReadAllMessages none'));?></div>
			<button class="btn" data-dismiss="modal">Отменить</button>
			<button class="btn btn-success" type="submit" id="mtsend" name="mtsubmit" value="send">Отправить</button>
		</div>
	</div>
<?= form_close(); ?>