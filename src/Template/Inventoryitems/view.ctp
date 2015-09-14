<?php $this->assign('title', 'Inventory'); ?>
<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('Edit this item'), ['action' => 'edit', $inventoryitem->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete this item'), ['action' => 'delete', $inventoryitem->id], ['confirm' => __('Are you sure you want to delete # {0}?', $inventoryitem->id)]) ?> </li>
        <li><?= $this->Html->link(__('New item'), ['action' => 'add']) ?> </li>
    </ul>
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks([], $userData) ?>
</div>
<div class="inventoryitems view large-10 medium-9 columns">
    <h2><?= h($inventoryitem->id) ?></h2>
    <div class="row">
        <div class="large-5 columns strings">
            <h6 class="subheader"><?= __('Currently in possession of') ?></h6>
            <p><?= $inventoryitem->has('user') ? $this->Html->link($inventoryitem->user->displayName(), ['controller' => 'Users', 'action' => 'view', $inventoryitem->user->id]) : '' ?></p>
        </div>
        <div class="large-2 columns dates end">
            <h6 class="subheader"><?= __('Created') ?></h6>
            <p><?= h($inventoryitem->created) ?></p>
            <h6 class="subheader"><?= __('Modified') ?></h6>
            <p><?= h($inventoryitem->modified) ?></p>
        </div>
    </div>
    <div class="row texts">
        <div class="columns large-9">
            <h6 class="subheader"><?= __('Description') ?></h6>
            <?= $this->Text->autoParagraph(h($inventoryitem->description)) ?>
        </div>
    </div>
</div>
