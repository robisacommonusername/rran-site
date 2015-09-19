<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $user->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $user->id)]
            )
        ?></li>
    </ul>
    <h3><?= __('Places') ?></h3>
    <?= $this->Sidebar->placeLinks(['users/edit/:id'], $userData) ?>
</div>
<div class="users form large-10 medium-9 columns">
    <?= $this->Form->create($user) ?>
    <fieldset>
        <legend><?= __('Edit User') ?></legend>
        <?php
            echo $this->Form->input('username');
            echo $this->Form->input('password', ['type' => 'password', 'required' => false, 'error' => false, 'label' => 'Password (leave blank if you do not want to change your password)', 'value' => '']);
            echo $this->Form->input('password2', ['type' => 'password', 'label' => 'Confirm Password (leave blank if you do not want to change your password)', 'value' => '']);
            echo $this->Form->input('real_name');
            echo $this->Form->input('email');
            echo $this->Form->input('phone');
            if ($userData['is_admin']){
				echo $this->Form->checkbox('is_admin');
				echo $this->Form->label('is_admin', 'Administrator?');
			}
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
