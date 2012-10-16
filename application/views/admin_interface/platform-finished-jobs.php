<!DOCTYPE html>
<html lang="en">
<?php $this->load->view('admin_interface/includes/head');?>
<body>
	<?php $this->load->view('admin_interface/includes/header');?>
	<div class="container">
		<div class="row">
			<div class="span9">
				<ul class="breadcrumb">
					<li tnum="webmasters">
						<?=anchor($this->session->userdata('backpath'),'Список площадок');?> <span class="divider">/</span>
					</li>
					<li class="active">
						<?=anchor($this->uri->uri_string(),'Выполненные задания');?>
					</li>
					<li style="float:right;">
						<?=anchor('admin-panel/management/finished-jobs/delete/platform/'.$this->uri->segment(5),'Удалить все работы',array('class'=>'btn btn-warning','id'=>'DeleteAll','style'=>'margin-top: -5px;'));?>
					</li>
				</ul>
				<?php $this->load->view('alert_messages/alert-error');?>
				<?php $this->load->view('alert_messages/alert-success');?>
				<table class="table table-bordered" style="width: 700px;">
					<thead>
						<tr>
							<th class="w100"><center>№ п.п</center></th>
							<th class="w100"><center>Дата</center></th>
							<th class="w100"><center><nobr>Тип работы</nobr></center></th>
							<th class="w100"><center>Биржа</center></th>
							<th class="w100"><center><nobr>Цена<br/>на бирже</nobr></center></th>
							<th class="w100"><center>URL-адрес</center></th>
							<th class="w100"><center><nobr>Колич.<br/>символов</nobr></center></th>
							<th class="w100"><center>Стоим.(веб/мен)</center></th>
						</tr>
					</thead>
					<tbody>
					<?php for($i=0,$num=$this->uri->segment(8)+1;$i<count($delivers);$i++,$num++):?>
						<tr>
							<td class="w100" data-status="<?=$delivers[$i]['status'];?>" style="text-align:center; vertical-align:middle;">
								<?=$num;?><br/>
								<nobr>
							<?php if(!$delivers[$i]['status']):?>
								<div id="params<?=$i;?>" style="display:none" data-wid="<?=$delivers[$i]['id'];?>" data-type="<?=$delivers[$i]['typework'];?>" data-market="<?=$delivers[$i]['market'];?>" data-ulrlink="<?=$delivers[$i]['ulrlink'];?>" data-countchars="<?=$delivers[$i]['countchars'];?>" data-mkprice="<?=$delivers[$i]['mkprice'];?>" data-wprice="<?=$delivers[$i]['wprice'];?>" data-mprice="<?=$delivers[$i]['mprice'];?>"></div>
								<a class="EditWork" data-param="<?=$i;?>" data-toggle="modal" href="#editWork" title="Редактировать работу"><i class="icon-edit"></i></a>
							<?php else:?>
								<div id="params<?=$i;?>" style="display:none" data-wid="<?=$delivers[$i]['id'];?>"></div>
							<?php endif;?>
								<a class="DeleteWork" data-param="<?=$i;?>" data-toggle="modal" href="#deleteWork" title="Удалить работу"><i class="icon-trash"></i></a>
								</nobr>
							</td>
							<td class="w100" style="text-align:center; vertical-align:middle;"><nobr><b><?=$delivers[$i]['date'];?></b></nobr></td>
							<td class="w100" style="text-align:center; vertical-align:middle;"><?=$delivers[$i]['twtitle'];?></td>
							<td class="w100" style="text-align:center; vertical-align:middle;"><?=$delivers[$i]['mtitle'];?></td>
							<td class="w100" style="text-align:center; vertical-align:middle;"><nobr><?=$delivers[$i]['mkprice'];?> руб.</nobr></td>
							<td class="w100" style="vertical-align:middle;"><?=anchor($delivers[$i]['ulrlink'],$delivers[$i]['link'],array('target'=>'_blank'));?></td>
							<td class="w100" style="text-align:center; vertical-align:middle;"><nobr><?=$delivers[$i]['countchars'];?> шт.</nobr></td>
							<td class="w100" style="text-align:center; vertical-align:middle;"><nobr><?=$delivers[$i]['wprice'];?> руб.<br/><?=$delivers[$i]['mprice'];?> руб.</nobr></td>
						</tr>
					<?php endfor; ?>
					</tbody>
				</table>
			<?php if($pages): ?>
				<?=$pages;?>
			<?php endif;?>
			</div>
		<?php $this->load->view('admin_interface/includes/rightbar');?>
		<?php $this->load->view('admin_interface/modal/admin-edit-work');?>
		<?php $this->load->view('admin_interface/modal/admin-delete-work');?>
		</div>
	</div>
	<?php $this->load->view('admin_interface/includes/footer');?>
	<?php $this->load->view('admin_interface/includes/scripts');?>
	<script type="text/javascript">
		$(document).ready(function(){
			var wID = 0;
			$("td[data-status='0']").each(function(e){$(this).addClass('notpaid'); $(this).siblings('td').addClass('notpaid');});
			$("td[data-status='1']").each(function(e){$(this).addClass('paid'); $(this).siblings('td').addClass('paid');});
			$(".EditWork").click(function(){
				var Param = $(this).attr('data-param'); wID = $("div[id = params"+Param+"]").attr("data-wid");
				var	wtype = $("div[id = params"+Param+"]").attr("data-type"); var wmarket = $("div[id = params"+Param+"]").attr("data-market");
				var	wulrlink = $("div[id = params"+Param+"]").attr("data-ulrlink"); var wcountchars = $("div[id = params"+Param+"]").attr("data-countchars");
				var	wmkprice = $("div[id = params"+Param+"]").attr("data-mkprice"); var wwprice = $("div[id = params"+Param+"]").attr("data-wprice");
				var	wmprice = $("div[id = params"+Param+"]").attr("data-mprice");
				$(".idWork").val(wID);$("#TypesWork").val(wtype);$("#Market").val(wmarket);
				$("#mkprice").val(wmkprice);$("#UlrLink").val(wulrlink); $("#CountChars").val(wcountchars);
				$("#PriceWM").val(wwprice);$("#PriceM").val(wmprice);
			});
			
			$("#DeleteAll").click(function(){if(confirm("Удалить все работы?") == false) return false;});
			
			$("#send").click(function(event){
				var err = false;
				$(".control-group").removeClass('error');
				$(".help-inline").hide();
				$(".inpval").each(function(i,element){
					if($(this).val()==''){
						$(this).parents(".control-group").addClass('error');
						$(this).siblings(".help-inline").html("Поле не может быть пустым").show();
						err = true;
					}
				});
				if(err){event.preventDefault();}
			});
			$("#editWork").on("hidden",function(){$("#msgalert").remove();$(".control-group").removeClass('error');$(".help-inline").hide();});
			$(".DeleteWork").click(function(){var Param = $(this).attr('data-param'); wID = $("div[id = params"+Param+"]").attr("data-wid");});
			$("#DelWork").click(function(){location.href='<?=$baseurl;?>admin-panel/management/finished-jobs/delete/jobid/'+wID;});
		});
	</script>
</body>
</html>
