<?php
use Phinx\Migration\AbstractMigration;

class AddDescriptionToUploadedfiles extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('uploadedfiles');
        $table->addColumn('description', 'text', [
            'default' => null,
            'null' => false,
        ]);
        $table->update();
    }
}
