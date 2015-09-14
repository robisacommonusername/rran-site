<?php $this->assign('title', 'Minutes'); ?>
<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('Edit Minute'), ['action' => 'edit', $minute->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Minute'), ['action' => 'delete', $minute->id], ['confirm' => __('Are you sure you want to delete # {0}?', $minute->id)]) ?> </li>
        <li><?= $this->Html->link(__('New Minute'), ['action' => 'add']) ?> </li>
    </ul>
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks([],$userData); ?>
</div>
<div class="minutes view large-10 medium-9 columns">
    <h2><?= h($minute->id) ?></h2>
    <div class="row">
        <div class="large-5 columns strings">
            <h6 class="subheader"><?= __('File Name') ?></h6>
            <p><?= h($minute->file_name) ?></p>
            <h6 class="subheader"><?= __('Mime Type') ?></h6>
            <p><?= h($minute->mime_type) ?></p>
        </div>
        <div class="large-2 columns numbers end">
            <h6 class="subheader"><?= __('Id') ?></h6>
            <p><?= $this->Number->format($minute->id) ?></p>
            <h6 class="subheader"><?= __('File Size') ?></h6>
            <p><?= $this->Number->format($minute->file_size) ?></p>
        </div>
        <div class="large-2 columns dates end">
            <h6 class="subheader"><?= __('Meeting Date') ?></h6>
            <p><?= h($minute->meeting_date) ?></p>
        </div>
    </div>
    <div class="row texts">
        <div class="columns large-9">
            <h6 class="subheader"><?= __('Content') ?></h6>
            <?= $this->Text->autoParagraph(h($minute->content)) ?>
        </div>
    </div>
</div>
