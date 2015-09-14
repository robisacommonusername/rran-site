<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('Edit Tag'), ['action' => 'edit', $tag->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Tag'), ['action' => 'delete', $tag->id], ['confirm' => __('Are you sure you want to delete # {0}?', $tag->id)]) ?> </li>
        <li><?= $this->Html->link(__('New Tag'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List files by tag'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('List files by filename'), ['controller' => 'Uploadedfiles', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('Upload new file'), ['controller' => 'Uploadedfiles', 'action' => 'add']) ?> </li>
    </ul>
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks([], $userData) ?>
</div>
<div class="tags view large-10 medium-9 columns">
    <h2><?= h($tag->label) ?></h2>
    <div class="row">
        <div class="large-5 columns strings">
            <h6 class="subheader"><?= __('Label') ?></h6>
            <p><?= h($tag->label) ?></p>
        </div>
        
    </div>
    <div class="row texts">
        <div class="columns large-9">
            <h6 class="subheader"><?= __('Description') ?></h6>
            <?= $this->Text->autoParagraph(h($tag->description)) ?>
        </div>
    </div>
    <div class="related row">
	    <div class="column large-12">
	    <h4 class="subheader"><?= __('Related Uploadedfiles') ?></h4>
	    <?php if (!empty($tag->uploadedfiles)): ?>
	    <table cellpadding="0" cellspacing="0">
	        <tr>
	            <th><?= __('File Name') ?></th>
	            <th><?= __('File Size') ?></th>
	            <th><?= __('Private') ?></th>
	            <th><?= __('Modified') ?></th>
	            <th class="actions"><?= __('Actions') ?></th>
	        </tr>
	        <?php foreach ($tag->uploadedfiles as $uploadedfiles): ?>
	        <tr>
	            <td><?= $this->Html->link(h($uploadedfiles->file_name),
	             ['controller' => 'Uploadedfiles', 'action' => 'view', $uploadedfiles->id]) ?></td>
	            <td><?= $this->Number->toReadableSize($uploadedfiles->file_size) ?></td>
	            <td><?= h($uploadedfiles->private ? 'Private' : 'Public') ?></td>
	            <td><?= h($uploadedfiles->modified ? $uploadedfiles->modified : $uploadedfiles->created) ?></td>
	
	            <td class="actions">
	
	                <?= $this->Html->link(__('Edit'), ['controller' => 'Uploadedfiles', 'action' => 'edit', $uploadedfiles->id]) ?>
	
	                <?= $this->Form->postLink(__('Delete'), ['controller' => 'Uploadedfiles', 'action' => 'delete', $uploadedfiles->id], ['confirm' => __('Are you sure you want to delete # {0}?', $uploadedfiles->id)]) ?>
	
	            </td>
	        </tr>
	
	        <?php endforeach; ?>
	    </table>
	    <?php endif; ?>
	    </div>
	</div>
</div>
