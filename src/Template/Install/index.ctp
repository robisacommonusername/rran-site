
<div>
    <?= $this->Form->create(null, ['action' => 'install']) ?>
    <fieldset>
        <legend><?= __('Create admin user') ?></legend>
        <?php
			echo $this->Form->label('username','Admin username');
            echo $this->Form->text('username');
            echo $this->Form->label('password', 'Admin password');
            echo $this->Form->password('password');
            echo $this->Form->label('password2', 'Confirm password');
            echo $this->Form->password('password2');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Install')) ?>
    <?= $this->Form->end() ?>
</div>
