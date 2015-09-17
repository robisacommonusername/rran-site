<?php $this->assign('title', 'RRAN Members Area'); ?>
<div class="actions columns large-2 medium-3">
    <h3><?= __('Home') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('Inventory'), ['controller' => 'Inventoryitems', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('Meeting Minutes'), ['controller' => 'Minutes', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('Uploaded files (shared drive)'), ['controller' => 'Uploadedfiles', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('Edit your contact information'), ['controller' => 'Users', 'action' => 'edit', $userData['id']]) ?></li>
        <?php if ($userData['is_admin']) { ?>
			<li><?= $this->Html->link(__('Manage users'), ['controller' => 'Users', 'action' => 'index']); ?></li>
        <?php } ?>
    </ul>
</div>

