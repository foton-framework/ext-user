<? $this->seo->title('Редактирование анкеты пользователя') ?>
<? $this->seo->h1('Редактирование анкеты пользователя') ?>

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
	</table>
	
	<hr>
	
	<?=h_form::submit('Сохранить') ?>
	
<?=h_form::close() ?>
</div>


