<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace humanized\scoopit\cli;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use humanized\scoopit\Client;
use humanized\scoopit\components\TagHelper;
use humanized\scoopit\models\Source;
use humanized\scoopit\models\SourceTopic;
use humanized\scoopit\models\Scoop;
use humanized\scoopit\models\Topic;

/**
 * Provides an interface to synchronise remote and local scoop.it topic content
 *
 *
 * @name Scoop.it CLI Data Synchronisation Tool
 * @version 1.0
 * @author Jeffrey Geyssens <jeffrey@humanized.be>
 * @package yii2-scoopit
 *
 */
class DataController extends Controller
{

    public $verbose = false;

    /**
     * ************************************************************************
     *                      Private run-time variables
     * ************************************************************************
     */

    /**
     *
     * @var string 
     */
    private $_postprocessorClass = null;

    /**
     *
     * @var Client 
     */
    private $_client = null;

    /**
     *
     * @var Topic 
     */
    private $_topic = null;

    /**
     *
     * @var boolean 
     */
    private $_autoScoop = false;

    /**
     *
     * @var boolean 
     */
    private $_saveSuggestions = false;

    /**
     *
     * @var boolean 
     */
    private $_enableRmTag = true;

    /**
     *
     * @var integer 
     */
    private $_rmLifetime = 0;

    /**
     *
     * @var boolean 
     */
    private $_enableDoublePostTags = false;

    /**
     *
     * @var boolean 
     */
    private $_saveDuplicateTagState = false;

    /**
     *
     * @var integer[][] 
     */
    private $_duplicates = [];
    protected $switches = ['autoscoop', 'saveSuggestions', 'enableRmTag', 'enableDoublePostTags', 'saveDuplicateTagState'];

    public function options()
    {
        return ['verbose'];
    }

    public function optionAliases()
    {
        return ['v' => 'verbose'];
    }

    /**
     * ************************************************************************
     *                              Actions
     * ************************************************************************
     */

    /**
     * Acquire remote Scoop.it content for all locally stored topics. 
     * 
     * @param type $lastUpdate
     * @return int
     */
    public function actionIndex($lastUpdate = 1)
    {
        $this->_client = new Client();
        $topics = Topic::find()->all();
        foreach ($topics as $topic) {
            $this->actionSynchronise($topic->id, $lastUpdate);
        }
        return 0;
    }

    /**
     * 
     * Acquire remote Scoop.it content for specified locally stored topic. 
     *  
     * 
     * @param string|int $topic
     * @param type $lastUpdate
     * @return int
     */
    public function actionSynchronise($topicId, $lastUpdate = 1)
    {
        if (false == $this->_initSynchronise($topicId)) {
            return 1;
        }
        if ($this->_autoScoop) {
            $this->_autoScoop($lastUpdate);
        }
        if ($this->_saveSuggestions) {
            $this->_importCurable($lastUpdate);
        }

        if ($this->_enableRmTag) {
            $this->_processRemoved();
        }
        $this->_synchroniseCurated($lastUpdate);

        return 0;
    }

    /*
     * ************************************************************************
     *             Private helper functions for synchronise action
     * ************************************************************************
     */

    /**
     * Private function to initialise run-time (private) variables
     * 
     * @param type $topicId
     * @return int
     */
    private function _initSynchronise($topicId)
    {
        if (!$this->_initSynchroniseVars($topicId)) {
            return false;
        }
        if ($this->verbose) {
            $this->_initSynchroniseOut();
        }
        return true;
    }

    private function _initSynchroniseVars($topicId)
    {
        if (!isset($this->_postprocessorClass) && isset($this->module->params['postprocessorClass'])) {
            $this->_postprocessorClass = $this->module->params['postprocessorClass'];
        }
        if (!isset($this->_client)) {
            $this->_client = new Client();
        }
        $this->_topic = Topic::resolve($topicId);

        if (!isset($this->_topic)) {
            if ($this->verbose) {
                $this->stderr("Topic " . (is_integer($topicId) ? "#" : "" . $topicId) . " not found in local database \n", Console::FG_YELLOW, Console::BOLD);
            }
            return false;
        }
        //Enchancement configuration parameters
        $this->_autoScoop = (isset($this->module->params['autoScoopTopicCondition']) &&
                call_user_func($this->module->params['autoScoopTopicCondition'], $this->_topic));

        foreach ($this->switches as $switch) {
            if ($switch != "autoscoop") {
                $switchVar = '_' . $switch;
                $this->$switchVar = (isset($this->module->params[$switch]) &&
                        $this->module->params[$switch]);
            }
        }
        if ($this->_enableRmTag) {
            $this->_rmLifetime = isset($this->module->params['rmLifetime']) ? $this->module->params['rmLifetime'] : 0;
        }
        return true;
    }

    private function _initSynchroniseOut()
    {
        //Print output
        $this->stdout("Processing data for topic: \t");
        $this->stdout($this->_topic->name . "\n", Console::FG_CYAN, Console::BOLD);

        $this->stdout("Postprocessor Class: \t\t");
        !isset($this->_postprocessorClass) ? $this->stdout("not set", Console::FG_RED, Console::BOLD) : $this->stdout($this->_postprocessorClass, Console::FG_CYAN, Console::BOLD);
        $this->stdout("\n");

        $this->stdout("Configuration Options:\t\t");
        foreach ($this->switches as $switch) {
            $this->stdout("--$switch=");
            $switchVar = '_' . $switch;
            $isEnabled = isset($this->$switchVar) && $this->$switchVar ? true : false;
            $this->stdout(($isEnabled ? "en" : "dis") . "abled ", ($isEnabled ? Console::FG_GREEN : Console::FG_RED), Console::BOLD);
            if (($switch == 'enableRmTag') && $isEnabled) {
                $this->stdout("(keep-alive=$this->_rmLifetime)");
            }
        }
        $this->stdout("\n");
    }

    /**
     * 
     * Private method wrapping call to client auto-scoop method,
     * when auto-scoop condition satisfied for topic as specified by config
     * 
     * @param type $lastUpdate
     */
    private function _autoscoop($lastUpdate)
    {
        !$this->verbose ? '' : $this->stdout("Curating unpublished posts on remote system", Console::FG_CYAN, Console::BOLD);
        $this->_client->autoScoop($this->_topic->id, $lastUpdate);
        !$this->verbose ? '' : $this->stdout("\n");
    }

    /**
     * Private method for local import of topical posts that are curable (unpublished)
     * When saveSuggestions is enabled as specified by config
     * @param type $lastUpdate
     */
    private function _importCurable($lastUpdate)
    {
        !$this->verbose ? '' : $this->stdout("Importing curable posts", Console::FG_CYAN, Console::BOLD);
        foreach ($this->_client->curablePosts($this->_topic->id, $lastUpdate) as $post) {
            !$this->verbose ? '' : $this->_processPostOut($post, "Importing curable post");
            Source::importPost($post, $this->_postprocessorClass);
        }
        !$this->verbose ? '' : $this->stdout("\n");
    }

    /**
     * 
     */
    private function _processRemoved()
    {
        !$this->verbose ? '' : $this->stdout('Processing removed posts', Console::FG_CYAN, Console::BOLD);
        foreach ($this->_client->taggedPosts($this->_topic->id, '#rm') as $post) {
            !$this->verbose ? '' : $this->_processPostOut($post, "Processing removed post");
            $this->_processRemovedLocal($post);

            $this->_processRemovedRemote($post);
        }
        !$this->verbose ? '' : $this->stdout("\n");
    }

    private function _processRemovedLocal($post)
    {
        $source = Source::resolve($post);
        !$this->verbose ? '' : $this->stdout("\n--> Updating Local System:\t");
        if (!isset($source)) {
            !$this->verbose ? '' : $this->stdout('Post not found: nothing to do', Console::FG_YELLOW, Console::BOLD);
            return;
        }
        if (isset($source)) {
            !$this->verbose ? '' : $this->stdout('Post found', Console::FG_YELLOW, Console::BOLD);
            $rmSourceTopicCount = SourceTopic::deleteAll(['topic_id' => $this->_topic->id, 'source_id' => $source->id]);

            if ($rmSourceTopicCount > 0) {
                !$this->verbose ? '' : $this->stdout('Removed publication from topic');
            }
            $rmScoopCount = 0;
            if (empty(SourceTopic::findAll(['source_id' => $source->id]))) {
             
                $rmScoopCount = Scoop::deleteAll(['id' => $source->id]);
                if ($rmScoopCount > 0) {
                    !$this->verbose ? '' : $this->stdout("\n\t\t\t\tRemoving publication from local storage");
                }
            }
            if ($rmSourceTopicCount == 0 && $rmScoopCount == 0) {
                !$this->verbose ? '' : $this->stdout(": nothing to do", Console::FG_YELLOW, Console::BOLD);
            }
        }
    }

    private function _processRemovedRemote($post)
    {
        !$this->verbose ? '' : $this->stdout("\n--> Updating Remote System:\t");
        $rmTag = TagHelper::readRemovalTag($post);

        if (!isset($rmTag)) {
            //!$this->verbose ? '' : $this->stdout("\n\t\t\t\tFlagging curated post for deletion on remote storage ", Console::FG_RED, Console::BOLD);
            $rmTag = TagHelper::createRemovalTag($this->_rmLifetime);
            if ($this->_rmLifetime != 0) {
                $this->_client->addTag($post->id, $rmTag);
                return;
            }
        }
        $rmTagData = TagHelper::implodeRemovalTag($rmTag);
        if ($rmTagData['lifetime'] < $this->_rmLifetime) {
            !$this->verbose ? '' : $this->stdout("\n\t\t\t\tUpdating deletion flag on remote storage", Console::FG_RED, Console::BOLD);
        }
        !$this->verbose ? '' : $this->stdout("\n");
    }

    /**
     * Private method for synchronising of topical posts that are curated (published)
     * @param type $lastUpdate
     */
    private function _synchroniseCurated($lastUpdate)
    {
        !$this->verbose ? '' : $this->stdout('Synchronising curated posts', Console::FG_CYAN, Console::BOLD);
        foreach ($this->_client->curatedPosts($this->_topic->id, $lastUpdate)as $post) {
            //Skip when post is pending removal
            if (TagHelper::isRemoved($post)) {
                continue;
            }
            !$this->verbose ? '' : $this->_processPostOut($post, "Synchronising curated post");
            Scoop::synchronisePost($post, $this->_postprocessorClass);
        }
        !$this->verbose ? '' : $this->stdout("\n");
    }

    /**
     * 
     * @param type $post
     */
    private function _preprocessDoublePostTags($post)
    {
        $duplicates[] = TagHelper::duplicates($post);
    }

    private function _synchroniseDuplicates()
    {
        foreach ($this->_duplicates as $key => $values) {
            //Tag Double Posts
            //Maintain Tag State
        }
    }

    private function _processPostOut($post, $msg, $color = Console::FG_GREEN)
    {
        $this->stdout("\n$msg: \t");
        $this->stdout($post->url, $color, Console::BOLD);
    }

}
