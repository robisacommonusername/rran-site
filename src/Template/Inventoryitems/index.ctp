<?php $this->assign('title', 'Inventory'); ?>
<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('New item'), ['action' => 'add']) ?></li>
    </ul>
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks(['inventoryitems/'], $userData) ?>
</div>
<div class="inventoryitems index large-10 medium-9 columns">
	<?= $this->Search->searchBox(['label' => 'Search for item by description:']); ?>
    <table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
			<th><?= $this->Paginator->sort('description', 'Item description') ?></th>
			<th><?= $this->Paginator->sort('user_id', 'Who has this item?') ?></th>
            <th class="actions"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($inventoryitems as $inventoryitem): ?>
        <tr>
			<td><?= h($inventoryitem->description) ?></td>
            <td>
				<?php
				if ($inventoryitem->has('user')){
					$user = $inventoryitem->user;
					$name = $user->real_name ? $user->real_name : $user->username;
					echo $this->Html->link(h($name), ['controller' => 'Users', 'action' => 'view', $user->id]);
				}
				?>
            </td>
            <td class="actions">
                <?= $this->Html->link(__('View'), ['action' => 'view', $inventoryitem->id]) ?>
                <?= $this->Html->link(__('Edit'), ['action' => 'edit', $inventoryitem->id]) ?>
                <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $inventoryitem->id], ['confirm' => __('Are you sure you want to delete # {0}?', $inventoryitem->id)]) ?>
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
