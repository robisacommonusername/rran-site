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
    <?php foreach ($uploadedfiles as $uploadedfile):
		echo $this->Uploadedfile->renderRow($uploadedfile, 
			['file_name', 'file_size', 'is_private', 'modified', 'tags',
			'actions']);
    endforeach; ?>
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
