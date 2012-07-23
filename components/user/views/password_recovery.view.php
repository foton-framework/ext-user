<? $this->seo->title('Восстановление пароля') ?>

<div class="rounded">
		
		<?=$this->form->form_errors() ?>
		<?=h_form::open('', 'post', 'name="login"') ?>
			<div style="float:left; margin-right:10px">E-mail: <?=$this->form->field('email') ?></div>
			<a href="javascript:void()/" onclick="document.forms['login'].submit()" class="btn_red"><span>Восстановить</span></a>
		<?=h_form::close() ?>
		
		<div class="clr"></div>
</div>