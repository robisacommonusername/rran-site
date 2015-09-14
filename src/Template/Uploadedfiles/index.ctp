<?php $this->assign('title', 'Uploaded files'); ?>
<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('Upload New file'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List files by tag'), ['controller' => 'Tags', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Tag'), ['controller' => 'Tags', 'action' => 'add']) ?></li>
    </ul>
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks(['uploadedfiles/'], $userData); ?>
</div>
<div class="uploadedfiles index large-10 medium-9 columns">
	<?= $this->Search->searchBox(); ?>
    <table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th><?= $this->Paginator->sort('file_name') ?></th>
            <th><?= $this->Paginator->sort('file_size') ?></th>
            <th><?= $this->Paginator->sort('private') ?></th>
            <th><?= $this->Paginator->sort('modified') ?></th>
            <th class="tags"><?= __('Tags') ?></th>
            <th class="actions"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($uploadedfiles as $uploadedfile): ?>
        <tr>
            <td><?= $this->Html->link(h($uploadedfile->file_name),
				['action' => 'view', $uploadedfile->id]) ?></td>
            <td><?= $this->Number->toReadableSize($uploadedfile->file_size) ?></td>
            <td><?= $uploadedfile->private ? 'Private' : 'Public' ?></td>
            <td><?= h($uploadedfile->modified ? $uploadedfile->modified : $uploadedfile->created) ?></td>
            <td class="tags">
				<?= $this->Tag->tagLinks($uploadedfile->tags); ?>
            </td>
            <td class="actions">
				<?= $uploadedfile->private ? '' : $this->Html->link(__('Public link'), ['action' => 'display', $uploadedfile->id]); ?>
                <?= $this->Html->link(__('Edit'), ['action' => 'edit', $uploadedfile->id]) ?>
                <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $uploadedfile->id], ['confirm' => __('Are you sure you want to delete # {0}?', $uploadedfile->id)]) ?>
            </td>
        </tr>

    <?php endforeach; ?>
    </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        </ul>
        <p><?= $this->Paginator->counter() ?></p>
    </div>
</div>
