<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Form->postLink(
                __('Delete tag'),
                ['action' => 'delete', $tag->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $tag->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List files by tag'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List files by filename'), ['controller' => 'Uploadedfiles', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('Upload new file'), ['controller' => 'Uploadedfiles', 'action' => 'add']) ?></li>
    </ul>
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks([], $userData) ?>
</div>
<div class="tags form large-10 medium-9 columns">
    <?= $this->Form->create($tag) ?>
    <fieldset>
        <legend><?= __('Edit Tag') ?></legend>
        <?php
            echo $this->Form->input('label');
            echo $this->Form->input('description');
            echo $this->Form->input('uploadedfiles._ids', 
				['options' => $this->Tag->assosciatedFiles($uploadedfiles),
				'label' => 'Files tagged '.h($tag->label)]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
