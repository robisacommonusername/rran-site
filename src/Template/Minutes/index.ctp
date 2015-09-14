<?php $this->assign('title', 'Minutes'); ?>
<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('New Minute'), ['action' => 'add']) ?></li>
    </ul>
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks(['minutes/'], $userData) ?>
</div>
<div class="minutes index large-10 medium-9 columns">
	<?= $this->Search->searchBox(['label' => 'Search for text in minutes:']); ?>
	<table>
    <thead>
        <tr>
            <th><?= $this->Paginator->sort('meeting_date') ?></th>
            <th class="actions"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($minutes as $minute): ?>
        <tr>
            <td><?= $minute->meeting_date->i18nFormat('YYYY-MM-dd'); ?></td>
            <td class="actions">
                <?= $this->Html->link(__('View'), ['action' => 'view', $minute->id]) ?>
                <?= $this->Html->link(__('Edit'), ['action' => 'edit', $minute->id]) ?>
                <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $minute->id], ['confirm' => __('Are you sure you want to delete # {0}?', $minute->id)]) ?>
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
