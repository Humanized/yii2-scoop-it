<?php

use yii\db\Migration;

class m161214_134402_clean_remote extends Migration
{

    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        $this->addColumn('scoopit_source_topic', 'remote_id', 'BIGINT');
        //Linkage table between topic and a tag (scoop.it style)
        $this->createTable('scoopit_import', ['source_id' => 'BIGINT', 'topic_id' => 'BIGINT'], $tableOptions);
        $this->addForeignKey('fk_scoopit_import_source', 'scoopit_import', 'source_id', 'scoopit_source', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_scoopit_import_topic', 'scoopit_import', 'topic_id', 'scoopit_topic', 'id', 'CASCADE', 'CASCADE');
        $this->addPrimaryKey('pk_scoopit_import_source_topic', 'scoopit_import', [ 'topic_id', 'source_id']);
        return true;
    }

    public function safeDown()
    {
        $this->dropColumn('scoopit_source_topic', 'remote_id');
        $this->dropTable('scoopit_import');
        return true;
    }

}
