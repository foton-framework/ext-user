<? $this->template->title('Вход для пользователей') ?>
<? $this->template->h1('Вход для пользователей') ?>

<?=$this->form->form_errors() ?>

<div class="box">

	<?=h_form::open() ?>

		<?=h_form::hidden($this->user->option('autologin_field'), 1) ?>

		<?=h_form::hidden('back_link', hlp::back_link()) ?>

		<table class="wrapper">
			<tr>
				<td>
					Логин:<br />
					<?=$this->form->field($this->user->option('login_field')) ?>
				</td>
				<td>
					Пароль:<br />
					<?=$this->form->field($this->user->option('password_field')) ?>
				</td>
				<td>
					<br />
					<button type="submit">Войти</button>
				</td>
			</tr>
		</table>

	<?=h_form::close() ?>

	<hr>

	<a href="/users/registration/">Регистрация</a> |
	<a href="/users/password_recovery/">Забыли пароль?</a>
</div>