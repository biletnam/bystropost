<!DOCTYPE html>
<html lang="en">
<?php $this->load->view('admin_interface/includes/head');?>
<body>
	<?php $this->load->view('admin_interface/includes/header');?>
	<div class="container">
		<div class="row">
			<div class="span9">
				<ul class="breadcrumb">
					<li class="active">
						<?=anchor('admin-panel/management/markets','Биржи');?>
					</li>
				</ul>
				<?php $this->load->view('alert_messages/alert-error');?>
				<?php $this->load->view('alert_messages/alert-success');?>
				<table class="table table-striped table-bordered">
					<thead>
						<tr>
							<th class="w50"><center><nobr>ID</nobr></center></th>
							<th class="w50"><center><nobr>Название</nobr></center></th>
							<th class="w400"><center><nobr>URL</nobr></center></th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
					<?php for($i=0;$i<count($markets);$i++):?>
						<tr>
							<td class="w50"><nobr><?=$markets[$i]['id'];?></nobr></td>
							<td class="w50"><nobr><img src="<?=$baseurl;?>markets/viewimage/<?=$markets[$i]['id'];?>" alt=""/>&nbsp;<b><?=$markets[$i]['title'];?></b></nobr></td>
							<td class="w400"><b><a href="<?=$markets[$i]['url'];?>"><?=$markets[$i]['url'];?></a></b></td>
							<td style="width:105px;">
								<div id="params<?=$i;?>" style="display:none" data-mid="<?=$markets[$i]['id'];?>" data-title="<?=$markets[$i]['title'];?>" data-url="<?=$markets[$i]['url'];?>"></div>
								<a class="btn btn-success editMarket" data-param="<?=$i;?>" data-toggle="modal" href="#editMarket" title="Редактировать биржу"><nobr>&nbsp;&nbsp;<i class="icon-pencil icon-white"></i>&nbsp;&nbsp;</nobr></a>
								<a class="btn btn-danger deleteMarket" data-param="<?=$i;?>" data-toggle="modal" href="#deleteMarket" title="Удалить биржу"><nobr>&nbsp;&nbsp;<i class="icon-trash icon-white"></i>&nbsp;&nbsp;</nobr></a>
							</td>
						</tr>
					<?php endfor; ?>
					</tbody>
				</table>
				<a class="btn btn-info" data-toggle="modal" href="#addMarket" title="Добавить биржу"><nobr>&nbsp;&nbsp;<i class="icon-plus icon-white"></i> Добавить&nbsp;&nbsp;</nobr></a>
			</div>
		<?php $this->load->view('admin_interface/includes/rightbar');?>
		<?php $this->load->view('admin_interface/modal/admin-edit-market');?>
		<?php $this->load->view('admin_interface/modal/admin-add-market');?>
		<?php $this->load->view('admin_interface/modal/admin-delete-market');?>
		</div>
	</div>
	<?php $this->load->view('admin_interface/includes/footer');?>
	<?php $this->load->view('admin_interface/includes/scripts');?>
	<script type="text/javascript">
		$(document).ready(function(){
			var mID = 0;
			$(".editMarket").click(function(){
				var Param = $(this).attr('data-param'); mID = $("div[id = params"+Param+"]").attr("data-mid");
				var	mTitle = $("div[id = params"+Param+"]").attr("data-title"); var	mURL = $("div[id = params"+Param+"]").attr("data-url");
				$(".idMarket").val(mID);$("#emTitle").val(mTitle);$("#emURL").val(mURL);
			});
			
			$("#emsend").click(function(event){
				var err = false;
				$(".control-group").removeClass('error');
				$(".help-inline").hide();
				$(".eminput").each(function(i,element){
					if($(this).val()==''){
						$(this).parents(".control-group").addClass('error');
						$(this).siblings(".help-inline").html("Поле не может быть пустым").show();
						err = true;
					}
				});
				if(err){event.preventDefault();}
			});
			
			$("#amsend").click(function(event){
				var err = false;
				$(".control-group").removeClass('error');
				$(".help-inline").hide();
				$(".aminput").each(function(i,element){
					if($(this).val()==''){
						$(this).parents(".control-group").addClass('error');
						$(this).siblings(".help-inline").html("Поле не может быть пустым").show();
						err = true;
					}
				});
				if(err){event.preventDefault();}
			});
			
			$(".mkmodal").on("hidden",function(){$("#msgalert").remove();$(".control-group").removeClass('error');$(".help-inline").hide();});
			$(".deleteMarket").click(function(){var Param = $(this).attr('data-param'); mID = $("div[id = params"+Param+"]").attr("data-mid");});
			$("#DelMarket").click(function(){location.href='<?=$baseurl;?>admin-panel/management/markets/marketid/'+mID;});
		});
	</script>
</body>
</html>
