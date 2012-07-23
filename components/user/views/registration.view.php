<? $this->seo->title('Регистрация нового пользователя') ?>
<? $this->seo->h1('Регистрация нового пользователя') ?>


<div class="box">
<?=h_form::open_multipart() ?>

	<?=$this->form->form_errors() ?>
	
	<table class="wrapper">
		<? foreach ($fields as $field): ?>
		<tr>
			<td><?=$this->form->label($field) ?>:</td>
			<td><?=$this->form->field($field) ?></td>
		</tr>
		<? endforeach ?>
		<tr>
			<td><?=$this->form->label('captcha') ?>:</td>
			<td>
				<?=$this->form->field('captcha') ?><br />
				<img src="/captcha/" alt="" title="" />
			</td>
		</tr>
	</table>
	
	<?=h_form::submit('Продолжить') ?>
	
<?=h_form::close() ?>
</div>