<!DOCTYPE html>
<html lang="en">
<?php $this->load->view('clients_interface/includes/head');?>
<body>
	<?php $this->load->view('clients_interface/includes/header');?>
	<div class="container">
		<div class="row">
			<div class="span9">
				<ul class="breadcrumb">
					<li class="active">
						<?=anchor('webmaster-panel/actions/profile','Мой профиль');?>
					</li>
				</ul>
				<?php $this->load->view('alert_messages/alert-error');?>
				<?php $this->load->view('alert_messages/alert-success');?>
			<?php if($this->session->userdata('wmid')):?>
				<div class="alert alert-info" id="msginfoalert">
					Чтобы пользоваться системой Быстропост необходимо указать идентификатор участника WebMoney (WMID)<br/>
					Внимание! WMID - уникальное поле, повторы запрещены. Вы должны производить оплату только с указанного Вами WMID.
				</div>
			<?php endif;?>
				<?php $this->load->view('forms/frmuserprofile');?>
				
			</div>
			<?php $this->load->view("clients_interface/includes/rightbar");?>
		</div>
	</div>
	<?php $this->load->view('clients_interface/includes/scripts');?>
	<script type="text/javascript">
		$(document).ready(function(){
			$("#submit").click(function(event){
				var err = false;
				$(".control-group").removeClass('error');
				$(".help-inline").hide();
				if($("#fio").val()==''){
					$("#fio").parents(".control-group").addClass('error');
					$("#fio").siblings(".help-inline").html("Поле не может быть пустым").show();
					event.preventDefault();
				}
				if($("#wmid").val()==''){
					$("#wmid").parents(".control-group").addClass('error');
					$("#wmid").siblings(".help-inline").html("Поле не может быть пустым").show();
					event.preventDefault();
				}else if($("#wmid").val().length != 12){
					$("#wmid").parents(".control-group").addClass('error');
					$("#wmid").siblings(".help-inline").html("Должно быть 12 цифр").show();
					event.preventDefault();
				}
				if($("#password").val() != ''){
					if($("#oldpas").val() == ''){
						$("#oldpas").parents(".control-group").addClass('error');
						$("#oldpas").siblings(".help-inline").html("Поле не может быть пустым").show();
						event.preventDefault();
					}else if($("#confpass").val() == ''){
						$("#confpass").parents(".control-group").addClass('error');
						$("#confpass").siblings(".help-inline").html("Поле не может быть пустым").show();
						event.preventDefault();
					}else{
						if($("#password").val() != $("#confpass").val()){
							$("#password").parents(".control-group").addClass('error');
							$("#confpass").parents(".control-group").addClass('error');
							$("#password").siblings(".help-inline").html("Пароли не совпадают").show();
							event.preventDefault();
						}
					}
				}
			});
			$("#reset").click(function(){window.location="<?=$baseurl;?>webmaster-panel/actions/control"});
		});
	</script>
</body>
</html>
