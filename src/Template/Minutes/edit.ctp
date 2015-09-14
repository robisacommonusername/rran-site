<script>
	$(document).ready(function(e){
		$('#meeting_date_text').datepicker({
			dateFormat: 'yy-mm-dd'
		});
	});
</script>
<?php $this->assign('title', 'Minutes'); ?>
<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $minute->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $minute->id)]
            )
        ?></li>
    </ul>
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks([], $userData); ?>
</div>
<div class="minutes form large-10 medium-9 columns">
    <?= $this->Form->create($minute) ?>
    <fieldset>
        <legend><?= __('Edit Minute') ?></legend>
        <?php
			echo $this->Form->label('meeting_date','Enter date of meeting');
            echo $this->Form->text('meeting_date', 
				['id' => 'meeting_date_text',
				'value' => $minute->meeting_date->i18nFormat('YYYY-MM-dd')]);
            echo $this->Form->input('content');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
