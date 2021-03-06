<!DOCTYPE html>
<html lang="en">
<?php $this->load->view("managers_interface/includes/head");?>

<body>
	<?php $this->load->view("managers_interface/includes/header");?>
	<div class="container">
		<div class="row">
			<div class="span9">
				<ul class="breadcrumb">
					<li class="active">
						<?=anchor("manager-panel/actions/platforms","Все площадки");?>
					</li>
				<?php if($userinfo['uid'] == 2):?>
					<li style="float:right;">
						<a class="btn btn-info DLWorks none" style="margin-top: -5px;" href="#" title="Импортировать работы"><i class="icon-download-alt icon-white"></i> Импортировать работы</a>
						<span id="SpLoadWorks" class="btn btn-warning" style="display:none;margin-top: -5px;"></span>
					</li>
				<?php endif;?>
				</ul>
				<?php $this->load->view("alert_messages/alert-error");?>
				<?php $this->load->view("alert_messages/alert-success");?>
				<div class="alert alert-info" id="msdownloading" style="display:none;">
					<h3>Ожидайте!</h3>Производится импорт выполненных работ. Это может занять некоторое время...
				</div>
				<div class="pull-right">
				<?=form_open($this->uri->uri_string(),array('class'=>'bs-docs-example form-search')); ?>
					<input type="hidden" id="srplid" name="srplid" value="">
					<input type="text" class="span4 search-query" id="srplurl" name="srplurl" value="" autocomplete="off" placeholder="Поиск от 2-х символов">
					<div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="<?=$baseurl;?>images/arrow.png" style="position: relative; top: -15px; left: 30px;" alt="upArrow" />
						<div class="suggestionList" id="suggestionsList"> &nbsp; </div>
					</div>
					<button type="submit" class="btn btn-primary" id="seacrh" name="scsubmit" value="seacrh"><i class="icon-search icon-white"></i> Найти</button>
				<?= form_close(); ?>
				</div>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th><center><nobr>URL площадки</nobr></center></th>
							<th><center><nobr>тиц / pr</nobr></center></th>
							<th><center>Дата</center></th>
							<th><center>Статус</center></th>
						</tr>
					</thead>
					<tbody>
					<?php for($i=0;$i<count($platforms);$i++):?>
						<tr>
							<td class="span5 ttpl">
							<?php if(!$platforms[$i]['locked'] && $platforms[$i]['status']):?>
								<?=anchor('manager-panel/actions/platforms/view-platform/'.$platforms[$i]['id'],$platforms[$i]['url'],array('title'=>'Просмотреть площадку'));?>
							<?php else:?>
								<?=anchor($this->uri->uri_string(),$platforms[$i]['url'],array('class'=>'none muted'));?>
							<?php endif;?>
							<?php if(!$platforms[$i]['status']):?>
								<i class="icon-exclamation-sign" title="Не активна"></i>
							<?php endif;?>
							<?php if($platforms[$i]['locked']):?>
								<i class="icon-lock" title="Заблокирована"></i>
							<?php endif;?>
							</td>
							<td class="span2"><center><?=$platforms[$i]['tic'];?> / <?=$platforms[$i]['pr'];?></center></td>
						<?php if(!$platforms[$i]['status']):?>
							<td class="span2" data-status="noactive">
						<?php elseif($platforms[$i]['locked']):?>
							<td class="span2" data-locked="locked">
						<?php else:?>
							<td class="span2">
						<?php endif;?>
								<center><nobr><?=$platforms[$i]['date'];?></nobr></center>
							</td>
							<td class="span3" style="text-align: center; vertical-align: middle;">
							<?php if(!$platforms[$i]['locked'] && $platforms[$i]['status']):?>
								<?=anchor('manager-panel/actions/platforms/edit-platform/'.$platforms[$i]['id'],'&nbsp;<i class="icon-pencil icon-white"></i>&nbsp;',array('title'=>'Редактировать площадку','class'=>'btn btn-success '));?>
								<?=anchor('manager-panel/actions/platforms/'.$platforms[$i]['id'].'/deliver-work','&nbsp;<i class="icon-briefcase icon-white"></i>&nbsp',array('class'=>'btn btn-primary DeliverWork','title'=>'Сдать задание'));?>
							<?php else:?>
								<?=anchor('','&nbsp;<i class="icon-pencil icon-white"></i>&nbsp;',array('title'=>'Редактировать площадку','class'=>'btn btn-success disabled none'));?>
							<?php endif;?>
							<?php if(!$platforms[$i]['locked']):?>
								<?=anchor('manager-panel/actions/create-ticket/platform-id/'.$platforms[$i]['id'],'&nbsp;<i class="icon-tags icon-white"></i>&nbsp;',array('title'=>'Создать тикет','class'=>'btn btn-info'));?>
							<?php endif;?>
							</td>
						</tr>
					<?php endfor; ?>
					</tbody>
				</table>
				<?php if($pages): ?>
					<?=$pages;?>
				<?php endif;?>
			</div>
		<?php $this->load->view("managers_interface/includes/rightbar");?>
		<?php $this->load->view('managers_interface/modal/mail-users');?>
		</div>
	</div>
	<?php $this->load->view("managers_interface/includes/footer");?>
	<?php $this->load->view("managers_interface/includes/scripts");?>
	<script type="text/javascript">
		$(document).ready(function(){
			$("td[data-status='noactive']").each(function(e){
				$(this).addClass('alert alert-message'); $(this).siblings('td').addClass('alert alert-message');
			});
			$("td[data-locked='locked']").each(function(e){
				$(this).addClass('alert alert-error'); $(this).siblings('td').addClass('alert alert-error');
			});
			
			$(".mailUser").click(function(){
				var	nPlatform = $(this).parents("tr:first").find("td:first a").html();
				$("#nPlatform").val(nPlatform);$(".idPlatform").val($(this).attr("data-pid"));
			});
			
			$("#mtsend").click(function(event){
				var err = false;
				$(".control-group").removeClass('error');
				$(".help-inline").hide();
				if($("#mailText").val() == ''){
					$("#mailText").parents(".control-group").addClass('error');
					$("#mailText").siblings(".help-inline").html("Поле не может быть пустым").show();
					event.preventDefault();
				}
			});
			
		<?php if($userinfo['uid'] == 2):?>
			var stopRequest = false;
			var stopScript = false;
			$(".DLWorks").click(function(){
				if(!confirm("Начать импорт?")) return false;
				var objSpan = $("#SpLoadWorks");
				var intervalID; var plcount = <?=$workplatform;?>;
				var from=0;var count = <?=($workplatform<10)?$workplatform:10;?>;
				$(objSpan).siblings('a').remove();
				ajaxRequest(count,from);
				$(objSpan).show().html('Обработка площадок: '+parseInt(from+count)+' из '+plcount);
				intervalID = setInterval(
					function(){
						if(stopRequest){
							if(stopScript || (from+1) >=plcount){
								$(objSpan).show().html('Обработка завершена!');
								clearInterval(intervalID);
							}else{
								from = from + count;
								ajaxRequest(count,from);
								if((from+count) <=plcount){
									$(objSpan).show().html('Обработка площадок: '+parseInt(from+count)+' из '+plcount);
								}else{
									$(objSpan).show().html('Обработка площадок: '+plcount+' из '+plcount);
								}
								
							}
						}
					}
				,1000);
			});
			function ajaxRequest(count,from){
			
				stopRequest = false;
				stopScript = false;
				$.ajax({
					url: "<?=$baseurl;?>manager-panel/actions/platforms/remote_deliver_work",
					data: ({'count':count,'from':from}),
					type: "POST",
					dataType: "JSON",
					success: function(data){
						stopRequest = true;
						if(!data.nextstep){stopScript = true;}
					}
				});
			}
		<?php endif?>
		
			function suggest(inputString){
				if(inputString.length < 2){
					$("#suggestions").fadeOut();
				}else{
					$("#srplurl").addClass('load');
					$.post("<?=$baseurl;?>manager-panel/actions/platforms/search",{squery: ""+inputString+""},
						function(data){
							if(data.status){
								$("#suggestions").fadeIn();
								$("#suggestionsList").html(data.retvalue);
								$(".plorg").live('click',function(){fill($(this).html(),$(this).attr("data-plid"));});
							}else{
								$('#suggestions').fadeOut();
							};
							$("#srplurl").removeClass('load');
					},"json");
				}
			};
			
			function fill(url,plid){
				$("#srplurl").val(url);
				$("#srplid").val(plid);
				setTimeout("$('#suggestions').fadeOut();", 600);
			};
			
			$("#srplurl").keyup(function(){$("#srplid").val('');suggest(this.value)});
			$("#srplurl").focusout(function(){setTimeout("$('#suggestions').fadeOut();",600);});
			
			$("#seacrh").click(function(event){if($("#srplurl").val() == ''){event.preventDefault();}});
		});
	</script>
</body>
</html>
