<?php

use yii\db\Migration;

class m160314_205688_scoopit extends Migration
{

    protected $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';

    public function safeUp()
    {
        $this->_createBaseTables();
        $this->_createLinkageTables();
    }

    private function _createBaseTables()
    {

        $this->createTable('scoopit_keyword', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->unique(),
                ], $this->tableOptions);

        $this->createTable('scoopit_topic_map', [
            'topic_id' => 'BIGINT NOT NULL UNIQUE',
            'label' => $this->string(255)->unique(),
            'position' => $this->integer()->notNull(),
                ], $this->tableOptions);

        $this->addForeignKey('fk_topic_map', 'scoopit_topic_map', 'topic_id', 'scoopit_topic', 'id');

        //Main repository for all obtained scoopit results (as-per requirement)
        $this->createTable('scoopit_source', [
            'id' => $this->primaryKey(), //Provided by scoop.it for deduplication check,
            'url' => $this->string(1000)->unique()->notNull(),
            'title' => 'VARCHAR(400)',
            'description_raw' => 'TEXT',
            'description_html' => 'TEXT',
            'date_retrieved' => 'INT(11) NOT NULL',
            'image_source' => 'VARCHAR(2083) NULL',
            'image_height' => 'INT NULL',
            'image_width' => 'INT NULL',
            'image_small' => 'VARCHAR(2083) NULL',
            'image_medium' => 'VARCHAR(2083) NULL',
            'image_large' => 'VARCHAR(2083)  NULL',
            'language_id' => 'VARCHAR(2)',
                ], $this->tableOptions);


        $this->createTable('scoopit_tag', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->unique()->notNull(),
                ], $this->tableOptions);
        $this->createTable('scoopit_scoop', [
            'id' => $this->integer()->notNull(), //Provided by scoop.it for deduplication check,
            'date_published' => 'INT(11) NOT NULL',
                ], $this->tableOptions);

        $this->addPrimaryKey('pk_source', 'scoopit_scoop', 'id');
        $this->addForeignKey('fk_source', 'scoopit_scoop', 'id', 'scoopit_source', 'id', 'CASCADE', 'CASCADE');
    }

    private function _createLinkageTables()
    {

        //Linkage table between topic and keyword tags 

        $this->createTable('scoopit_topic_keyword', ['topic_id' => 'BIGINT NOT NULL', 'keyword_id' => 'INT (11) NOT NULL'], $this->tableOptions);
        $this->addForeignKey('fk_scoopit_scoop_keyword_scoop', 'scoopit_topic_keyword', 'topic_id', 'scoopit_topic', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_scoopit_scoop_keyword_keyword', 'scoopit_topic_keyword', 'keyword_id', 'scoopit_keyword', 'id', 'CASCADE', 'CASCADE');
        $this->addPrimaryKey('pk_scoopit_scoop_keyword', 'scoopit_topic_keyword', ['topic_id', 'keyword_id']);


        //Linkage table between topic and a tag (scoop.it style)
        $this->createTable('scoopit_scoop_tag', ['scoop_id' => 'INT (11) NOT NULL', 'tag_id' => 'INT (11) NOT NULL'], $this->tableOptions);

        $this->addForeignKey('fk_scoop_tag_scoop', 'scoopit_scoop_tag', 'scoop_id', 'scoopit_scoop', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_scoop_tag_tag', 'scoopit_scoop_tag', 'tag_id', 'scoopit_tag', 'id', 'CASCADE', 'CASCADE');
        $this->addPrimaryKey('pk_scoopit_scoop_tag', 'scoopit_scoop_tag', [ 'tag_id', 'scoop_id']);


        //Linkage table between topic and a tag (scoop.it style)
        $this->createTable('scoopit_source_topic', ['source_id' => 'INT (11) NOT NULL', 'topic_id' => 'BIGINT NOT NULL'], $this->tableOptions);
        $this->addForeignKey('fk_source_topic_source', 'scoopit_source_topic', 'source_id', 'scoopit_source', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_source_topic_tag', 'scoopit_source_topic', 'topic_id', 'scoopit_topic', 'id', 'CASCADE', 'CASCADE');
        $this->addPrimaryKey('pk_scoopit_source_tag', 'scoopit_source_topic', [ 'topic_id', 'source_id']);

        //Linkage table between source and keyword tags (highest content refinement forseen)
        //Unable to populate as of 20/3/2016...
        $this->createTable('scoopit_source_keyword', ['source_id' => 'INT (11) NOT NULL', 'keyword_id' => 'INT (11) NOT NULL'], $this->tableOptions);
        $this->addForeignKey('fk_source_keyword_source', 'scoopit_source_keyword', 'source_id', 'scoopit_source', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_source_keyword_keyword', 'scoopit_source_keyword', 'keyword_id', 'scoopit_keyword', 'id', 'CASCADE', 'CASCADE');
        $this->addPrimaryKey('pk_source_keyword', 'scoopit_source_keyword', ['source_id', 'keyword_id']);

        //Linkage table between topic and sector
        /*
          $this->createTable('sector_scoopit_topic', ['topic_id' => 'BIGINT', 'sector_id' => 'VARCHAR(2)'], $this->tableOptions);
          $this->addForeignKey('fk_sector_scoopit_topic_topic', 'sector_scoopit_topic', 'topic_id', 'scoopit_topic', 'id', 'CASCADE', 'CASCADE');
          $this->addForeignKey('fk_sector_scoopit_topic_sector', 'sector_scoopit_topic', 'sector_id', 'sector', 'id', 'CASCADE', 'CASCADE');
          $this->addPrimaryKey('pk_sector_scoopit_topic', 'sector_scoopit_topic', ['topic_id', 'sector_id']);
         * 
         */
    }

    public function safeDown()
    {

        $this->dropTable('scoopit_source_keyword');
        $this->dropTable('scoopit_source_topic');
        $this->dropTable('scoopit_scoop_tag');
        $this->dropTable('scoopit_topic_keyword');
        $this->dropTable('scoopit_scoop');
        $this->dropTable('scoopit_tag');
        $this->dropTable('scoopit_source');
        $this->dropTable('scoopit_topic_map');
        $this->dropTable('scoopit_keyword');
        $this->dropTable('scoopit_topic');

        return true;
    }

    /*
      // Use safeUp/safeDown to run migration code within a transaction
      public function safeUp()
      {
      }

      public function safeDown()
      {
      }
     */
}
