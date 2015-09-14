<?php $this->assign('title', 'Inventory'); ?>
<div class="actions columns large-2 medium-3">
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks([], $userData) ?>
</div>
<div class="inventoryitems form large-10 medium-9 columns">
    <?= $this->Form->create($inventoryitem) ?>
    <fieldset>
        <legend><?= __('Add Item to Inventory') ?></legend>
        <?php
            echo $this->Form->input('description', ['label' => 'Item Description']);
            echo $this->Form->input('user_id', ['options' => array_map('h', $usermap), 'value' => $userData['id'], 'label'=>'Who has this item?']);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
