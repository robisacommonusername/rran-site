<script>
	$(document).ready(function(e){
		$('#accordion').accordion({
			header: 'h4',
			collapsible: true
		});
	});
</script>
<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('New Tag'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List files by filename'), ['controller' => 'Uploadedfiles', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('Upload new file'), ['controller' => 'Uploadedfiles', 'action' => 'add']) ?></li>
    </ul>
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks([], $userData); ?>
</div>
<div class="tags index large-10 medium-9 columns">
	<?= $this->Search->searchBox(['label' => 'Search for tag']); ?>
	<div id="accordion">
	<?php foreach ($tags as $tag) { ?>
		<?php if (!empty($tag->uploadedfiles)) { ?>
			<h4><?= h($tag->label) ?></h4>
			<div>
		    <table cellpadding="0" cellspacing="0">
		    <thead>
		        <tr>
		            <th><?= $this->Paginator->sort('file_name') ?></th>
		            <th><?= $this->Paginator->sort('file_size') ?></th>
		            <th><?= $this->Paginator->sort('private') ?></th>
		            <th><?= $this->Paginator->sort('modified') ?></th>
		            <th class="actions"><?= __('Actions') ?></th>
		        </tr>
		    </thead>
			<tbody>
			<?php foreach ($tag->uploadedfiles as $uploadedfile) { ?>
				<tr>
					<td><?= $this->Html->link(h($uploadedfile->file_name),
					['controller' => 'Uploadedfiles','action' => 'view', $uploadedfile->id]) ?></td>
		            <td><?= $this->Number->toReadableSize($uploadedfile->file_size) ?></td>
		            <td><?= $uploadedfile->private ? 'Private' : 'Public' ?></td>
		            <td><?= h($uploadedfile->modified ? $uploadedfile->modified : $uploadedfile->created) ?></td>
		            <td class="actions">
						<?= $uploadedfile->private ? '' : $this->Html->link(__('Public link'), ['action' => 'display', $uploadedfile->id]); ?>
		                <?= $this->Html->link(__('Edit'), ['action' => 'edit', $uploadedfile->id]) ?>
		                <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $uploadedfile->id], ['confirm' => __('Are you sure you want to delete # {0}?', $uploadedfile->id)]) ?>
		            </td>
				</tr>
			<?php } ?>
		    </tbody>
		    </table>
			</div>
		<?php } ?>
	<?php } ?>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        </ul>
        <p><?= $this->Paginator->counter() ?></p>
    </div>
</div>
