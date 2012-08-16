<div class="sub_tabs">
	<? $sel = ' class="selected"' ?>
	<a href="<?=$link ?>"<?=$sub_a=='index'?$sel:NULL ?>>Статистика</a>
	<a href="<?=$link ?>edit/"<?=$sub_a=='edit'?$sel:NULL ?>>Редактирование</a>
	<a href="<?=$link ?>mail/"<?=$sub_a=='mail'?$sel:NULL ?>>Отправить письмо</a>
</div>

<h1>
	<img src="<?=$doctor->photo_m ?>" alt="" style="vertical-align:middle; margin-right:10px">
	<?=$doctor->full_name ?>
</h1>

<? require( dirname(__FILE__) . '/sub_' . $sub_a . VIEW_EXT ) ?>