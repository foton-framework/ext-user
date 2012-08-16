<table class="data_table">
<tr>
	<th>ID</th>
	<th>Статус</th>
	<th>Фото</th>
	<th>Имя</th>
	<th>Последний визит</th>
	<th>Зарегистрирован</th>
</tr>
<? foreach ($data as $index => $doc): ?>
<tr class="<?=$index++%2==0 ? 'a' : 'b' ?>">
	<td><?=$doc->id ?></td>
	<td>
		<? if ($doc->status == 1): ?>
			<img src="/extensions/admin/ico/status.png" alt="Включен" />
		<? elseif ($doc->status == -1): ?>
			<img src="/extensions/admin/ico/status-away.png" alt="На премодерации" />
		<? else: ?>
			<img src="/extensions/admin/ico/status-busy.png" alt="Выключен" />
		<? endif ?>
	</td>
	<td>
		<img src="<?=$doc->avatar ?>" alt="">
	</td>
	<td>
		<!-- <a href="/admin/admin_doctors/<?=$doc->id ?>/"></a><br /> -->
		<?=$doc->full_name ?>
		<?=$doc->work ?>
	</td>
	<td>
		<?=$doc->last_visit ?>
	</td>
	<td>
		<?=$doc->regdate ?>
	</td>
</tr>
<? endforeach ?>
</table>