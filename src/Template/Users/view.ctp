<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('Edit User'), ['action' => 'edit', $user->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete User'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete # {0}?', $user->id)]) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['action' => 'add']) ?> </li>
    </ul>
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks(['users/'], $userData) ?>
</div>
<div class="users view large-10 medium-9 columns">
	<h2><?= h($user->username) ?></h2>
    <div class="row">
        <div class="large-3 columns strings">
            <h6 class="subheader"><?= __('Username') ?></h6>
            <p><?= h($user->username) ?></p>
            <h6 class="subheader"><?= __('Real Name') ?></h6>
            <p><?= h($user->real_name) ?></p>
            <h6 class="subheader"><?= __('Email') ?></h6>
            <p><?= h($user->email) ?></p>
            <h6 class="subheader"><?= __('Phone') ?></h6>
            <p><?= h($user->phone) ?></p>
        </div>
        <div class="large-8 columns end">
            <div class="related row">
		    <div class="column large-12">
			<?php if (!empty($user->inventoryitems)): ?>
		    <h4 class="subheader"><?= __('Has the following items') ?></h4>
		    <table cellpadding="0" cellspacing="0">
		        <tr>
		            <th><?= __('Description') ?></th>
		            <th class="actions"><?= __('Actions') ?></th>
		        </tr>
		        <?php foreach ($user->inventoryitems as $inventoryitems): ?>
		        <tr>
		            <td><?= h($inventoryitems->description) ?></td>
		
		            <td class="actions">
		                <?= $this->Html->link(__('View'), ['controller' => 'Inventoryitems', 'action' => 'view', $inventoryitems->id]) ?>
		
		                <?= $this->Html->link(__('Edit'), ['controller' => 'Inventoryitems', 'action' => 'edit', $inventoryitems->id]) ?>
		
		                <?= $this->Form->postLink(__('Delete'), ['controller' => 'Inventoryitems', 'action' => 'delete', $inventoryitems->id], ['confirm' => __('Are you sure you want to delete # {0}?', $inventoryitems->id)]) ?>
		
		            </td>
		        </tr>
		
		        <?php endforeach; ?>
		    </table>
		    <?php endif; ?>
		    </div>
		</div>
        </div>
    </div>
</div>
