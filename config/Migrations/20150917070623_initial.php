<?php
use Phinx\Migration\AbstractMigration;

class Initial extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('inventoryitems');
        $table
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => '0000-00-00 00:00:00',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $table = $this->table('minutes');
        $table
            ->addColumn('meeting_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('file_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('mime_type', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('file_size', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('content', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('content_key', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'meeting_date',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'meeting_date',
                ],
                ['unique' => true]
            )
            ->create();

        $table = $this->table('tags');
        $table
            ->addColumn('label', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'label',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'label',
                ],
                ['unique' => true]
            )
            ->create();

        $table = $this->table('uploadedfiles');
        $table
            ->addColumn('file_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('mime_type', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('file_size', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('private', 'boolean', [
                'default' => 0,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => '0000-00-00 00:00:00',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('content_key', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->create();

        $table = $this->table('uploadedfiles_tags', ['id' => false, 'primary_key' => ['uploadedfile_id', 'tag_id']]);
        $table
            ->addColumn('uploadedfile_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('tag_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->create();

        $table = $this->table('users');
        $table
            ->addColumn('username', 'string', [
                'default' => '',
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'default' => '',
                'limit' => 60,
                'null' => false,
            ])
            ->addColumn('real_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('phone', 'string', [
                'default' => null,
                'limit' => 31,
                'null' => true,
            ])
            ->addColumn('is_admin', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => '0000-00-00 00:00:00',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'username',
                ],
                ['unique' => true]
            )
            ->create();

    }

    public function down()
    {
        $this->dropTable('inventoryitems');
        $this->dropTable('minutes');
        $this->dropTable('tags');
        $this->dropTable('uploadedfiles');
        $this->dropTable('uploadedfiles_tags');
        $this->dropTable('users');
    }
}
