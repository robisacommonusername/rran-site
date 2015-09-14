<script>
	$(document).ready(function(e){
		$('#meeting_date_text').datepicker({
			dateFormat: 'yy-mm-dd'
		});
	});
</script>
<?php $this->assign('title', 'Minutes'); ?>
<div class="actions columns large-2 medium-3">
    <h3><?= __('Places') ?></h3>
   <?= $this->Sidebar->placeLinks([], $userData) ?>
</div>
<div class="minutes form large-10 medium-9 columns">
    <?= $this->Form->create($minute, ['type' => 'file']) ?>
    <fieldset>
        <legend><?= __('Upload meeting minutes') ?></legend>
        <?php
			echo $this->Form->label('meeting_date','Enter date of meeting');
            echo $this->Form->text('meeting_date', 
				['id' => 'meeting_date_text']);
            echo $this->Form->label('uploaded_file', 'Click browse to upload minutes');
            echo $this->Form->file('uploaded_file');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
