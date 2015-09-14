<div class="actions columns large-2 medium-3">
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks([], $userData); ?>
</div>
<div class="users form large-10 medium-9 columns">
    <?= $this->Form->create($user) ?>
    <fieldset>
        <legend><?= __('Add User') ?></legend>
        <?php
            echo $this->Form->input('username');
            echo $this->Form->input('password');
            echo $this->Form->input('password2', ['type' => 'password', 'label' => 'Confirm password']);
            echo $this->Form->input('real_name');
            echo $this->Form->input('email');
            echo $this->Form->input('phone');
            if ($userData['is_admin']) {
				echo $this->Form->input('is_admin');
			}
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
