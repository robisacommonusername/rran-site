<?php $this->assign('title', 'Home'); ?>
<div class="actions columns large-2 medium-3">
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks(array(), $userData); ?>
</div>
<div class="medium-9 large-10 columns">
	<h2>Recent events</h2>
	
	<?php if (!$minutes->isEmpty()) { ?>
		<h3>Minutes</h3>
		<table>
			<thead>
				<tr><th>Meeting date</th></tr>
			</thead>
			<tbody>
				<?php foreach($minutes as $minute) { ?>
					<tr>
						<td><?= $this->Html->link($minute->meeting_date->i18nFormat('YYYY-MM-dd'), 
							['controller' => 'Minutes', 'action' => 'view', $minute->id]); ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php } ?>
	
	<?php if (!$items->isEmpty()) { ?>
		<h3>New and changed inventory items</h3>
		<table>
			<thead>
				<tr>
				<th>Item description</th>
				<th>Who has this item?</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($items as $item) { ?>
					<tr>
						<td><?= h($item->description) ?></td>
						<td><?= $item->has('user') ? $this->Html->link(h($item->user->displayName()), 
							['controller' => 'Users', 'action' => 'view', $item->user->id]) : 'No owner'; ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php } ?>
	
	<?php if (!$files->isEmpty()) { ?>
		<h3>Files uploaded</h3>
		<table>
			<thead>
				<tr>
					<th>File name</th>
					<th>File size</th>
					<th>Tags</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($files as $file) { ?>
					<tr>
						<td>
							<?= $this->Html->link(h($file->file_name),
							['controller' => 'Uploadedfiles', 'action' => 'view', $file->id]) ?>
						</td>
						<td>
							<?= $this->Number->toReadableSize($file->file_size) ?>
						</td>
						<td class="tags">
							<?= $this->Tag->tagLinks($file->tags) ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php } ?>
	
	<?php if (!$users->isEmpty()) { ?>
		<h3>New users</h3>
		<table>
			<thead>
				<tr>
					<th>Username</th>
					<th>Real name</th>
					<th>email</th>
					<th>Phone number</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($users as $user) { ?>
					<tr>
						<td><?= $this->Html->link(h($user->username),
							['controller' => 'Users', 'action' => 'view', $user->id])
						?></td>
						<td><?= h($user->real_name) ?></td>
						<td><?= h($user->email) ?></td>
						<td><?= h($user->phone) ?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php } ?>
</div>

