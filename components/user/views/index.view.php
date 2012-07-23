<a href='<?=$this->com_url ?>/registration'>регистрация</a> |
<a href='<?=$this->com_url ?>/login'>вход</a>

<table>
<? foreach ($users as $user): ?>
	<tr>
		<td>
			<a href='<?=$this->com_url . '/' . $user->id ?>'><img src='<?=$user->photo_s ?>' alt=''></a>
		</td>
		<td><?=$user->email ?></td>
		<td><?=$user->group_name ?></td>
		<td><?=$user->status_name ?></td>
	</tr>
<? endforeach ?>
</table>