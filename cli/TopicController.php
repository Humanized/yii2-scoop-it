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
 * @name Scoop.it CLI Topic Synchronisation Tool
 * @version 1.0
 * @author Jeffrey Geyssens <jeffrey@humanized.be>
 * @package yii2-scoopit
 *
 */
class TopicController extends Controller
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
     */
    private $_topicsAvailable = [];

    /**
     *
     * @var Topic 
     */
    private $_topic = null;

    /**
     *
     * @var integer|boolean 
     */
    private $_autoscoop = false;

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
     * @var boolean 
     */
    private $_enableCpTag = true;

    /**
     *
     * @var integer 
     */
    private $_remoteLifetime = 0;

    /**
     *
     * @var string[] 
     */
    protected $switches = ['autoscoop', 'saveSuggestions', 'enableRmTag', 'enableCpTag'];

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
    public function beforeAction($action)
    {
        if (!isset($this->_client)) {
            $this->_client = new Client();
            $this->_client->autoscoopSuffix = (isset($this->module->params['autoscoopSuffix']) ? $this->module->params['autoscoopSuffix'] : null);
            $this->_client->topicFilterPrefix = (isset($this->module->params['topicFilterPrefix']) ? $this->module->params['topicFilterPrefix'] : null);
            $this->_client->initAvailableTopics();
        }
        if (!isset($this->_postprocessorClass) && isset($this->module->params['postprocessorClass'])) {
            $this->_postprocessorClass = $this->module->params['postprocessorClass'];
        }

        return parent::beforeAction($action);
    }

    /**
     * Acquire remote Scoop.it content for all curated topics. 
     * 
     * @param type $lastUpdate
     * @return int
     */
    public function actionIndex($lastUpdate = 1)
    {
        foreach ($this->_client->availableTopics as $availableTopic) {
            $this->actionSynchronise($availableTopic['id']);
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
    public function actionSynchronise($topic, $lastUpdate = 1)
    {
        if (false == $this->_initSynchronise($topic)) {
            return 1;
        }
        if ($this->_saveSuggestions) {
            $this->_importCurable($lastUpdate);
        }


        /*

          $this->_synchroniseCurated($lastUpdate);


          if ($this->_autoScoop) {
          $this->_autoScoop($lastUpdate);
          }

         *
         */

        return 0;
    }

    public function actionMap($topic, $category, $label, $position = 0)
    {
        
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
    private function _initSynchronise($topic)
    {
        if (!$this->_initSynchroniseVars($topic)) {
            return false;
        }
        if ($this->verbose) {
            $this->_initSynchroniseOut();
        }
        return true;
    }

    private function _initSynchroniseVars($topic)
    {

        $this->_topic = $this->_client->availableTopics[(!is_string($topic) ?
                        $topic :
                        (isset($this->topicLabels[$topic]) ?
                                $this->topicLabels[$topic] :
                                false))];

        if (!$this->_topic) {
            if ($this->verbose) {
                $this->stderr("Topic " . (is_integer($topicId) ? "#" : "" . $topicId) . " not found in local database \n", Console::FG_YELLOW, Console::BOLD);
            }
            return false;
        }
        //Enchancement configuration parameters
        /*
          $this->_autoScoop = isset($this->module->params['autoscoopSuffix']);
         * 
         */


        foreach ($this->switches as $switch) {
            if ($switch != "autoscoop") {
                $switchVar = '_' . $switch;
                $this->$switchVar = (isset($this->module->params[$switch]) &&
                        $this->module->params[$switch]);
            }
        }
        $this->_autoscoop = (isset($this->_client->availableTopics[$this->_topic['id']]['auto']) ? $this->_client->availableTopics[$this->_topic['id']]['auto'] : false);
        $this->_remoteLifetime = isset($this->module->params['remoteLifetime']) ? $this->module->params['remoteLifetime'] : 0;

        return true;
    }

    private function _initSynchroniseOut()
    {
        //Print output
        $this->stdout("Processing data for topic: \t");
        $this->stdout($this->_topic['name'] . "\n", Console::FG_CYAN, Console::BOLD);

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
                $this->stdout("(keep-alive=$this->_remoteLifetime)");
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
        $this->_client->autoScoop($this->_autoscoop, $lastUpdate, [$this->_topic['id']]);
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
     * Private method for synchronising of topical posts that are curated (published)
     * @param type $lastUpdate
     */
    private function _synchroniseCurated($lastUpdate)
    {
        if ($this->_enableRmTag) {
            $this->_processTag('#rm', 'removal');
        }
        if ($this->_enableCpTag) {
            $this->_processTag('#cp', 'updated');
        }
        !$this->verbose ? '' : $this->stdout('Synchronising curated posts', Console::FG_CYAN, Console::BOLD);
        foreach ($this->_client->curatedPosts($this->_topic->id, $lastUpdate)as $post) {
            //Skip when post is pending removal
            if (TagHelper::isTagSkipped($post)) {
                continue;
            }
            !$this->verbose ? '' : $this->_processPostOut($post, "Synchronising curated post");
            Scoop::synchronisePost($post, $this->_postprocessorClass);
        }
        !$this->verbose ? '' : $this->stdout("\n");
    }

    private function _processTag($tag, $label)
    {
        !$this->verbose ? '' : $this->stdout('Processing ' . $label . ' posts', Console::FG_CYAN, Console::BOLD);
        foreach ($this->_client->taggedPosts($this->_topic->id, $tag) as $post) {
            !$this->verbose ? '' : $this->_processPostOut($post, "Processing $label post");
            $this->_processLocalTag($post, $tag, $label);
            $this->_processRemoteTag($post, $tag, $label);
        }
        !$this->verbose ? '' : $this->stdout("\n");
    }

    private function _processLocalTag($post, $tag, $label)
    {
        switch ($tag) {
            case'#rm': {
                    $this->_processRemovedLocal($post);
                    break;
                }

            case'#cp': {
                    Scoop::synchronisePost($post, $this->_postprocessorClass);
                    break;
                }
        }
    }

    private function _processRemovedLocal($post)
    {
        $source = Source::resolve($post);
        !$this->verbose ? '' : $this->stdout("\n--> Updating Local System:\t");
        if (!isset($source)) {
            !$this->verbose ? '' : $this->stdout('Post not found - nothing to do', Console::FG_YELLOW, Console::BOLD);
            return;
        }
        if (isset($source)) {
            !$this->verbose ? '' : $this->stdout('Post found', Console::FG_YELLOW, Console::BOLD);
            $rmSourceTopicCount = SourceTopic::deleteAll(['topic_id' => $this->_topic->id, 'source_id' => $source->id]);
            if ($rmSourceTopicCount > 0) {
                !$this->verbose ? '' : $this->stdout('\n\t\t\t\tRemoved publication from topic');
            }
            $rmScoopCount = 0;
            if (empty(SourceTopic::findAll(['source_id' => $source->id]))) {
                $rmScoopCount = Scoop::deleteAll(['id' => $source->id]);
                if ($rmScoopCount > 0) {
                    !$this->verbose ? '' : $this->stdout("\n\t\t\t\tRemoving publication from local storage");
                }
            }
            if ($rmSourceTopicCount == 0 && $rmScoopCount == 0) {
                !$this->verbose ? '' : $this->stdout(" - nothing to do", Console::FG_YELLOW, Console::BOLD);
            }
        }
    }

    private function _processRemoteTag($post, $tag, $label)
    {
        !$this->verbose ? '' : $this->stdout("\n--> Updating Remote System:\t");
        $remoteTag = TagHelper::readTag($post, $tag);
        $init = false;
        if (!isset($remoteTag)) {
            $init = true;
            !$this->verbose ? '' : $this->stdout(ucfirst($label) . ' timestamp tag not found', Console::FG_YELLOW, Console::BOLD);
            $remoteTag = TagHelper::createTimestampTag($tag, $this->_remoteLifetime);
            if ($this->_remoteLifetime != 0) {
                !$this->verbose ? '' : $this->stdout('- creating', Console::FG_YELLOW, Console::BOLD);
                $this->_client->addTag($post->id, $remoteTag);
                return;
            }
        }
        !$this->verbose ? '' : $this->stdout(($init ? '- forcing' : ucfirst($label) . ' timestamp tag found'), Console::FG_YELLOW, Console::BOLD);
        $data = TagHelper::implodeTimestampTag($remoteTag);
        if ($this->_processTimestampTag($post, $tag, $data, $label)) {
            $this->_executeTimestampTag($post, $tag, $data);
        }
        !$this->verbose ? '' : $this->stdout("\n");
    }

    private function _processTimestampTag($post, $tag, $data, $label)
    {
        //Update lifetime on remote, when local system has a higher lifetime specified
        $lifetime = $data['lifetime'];

        if ($this->_remoteLifetime > $lifetime) {
            !$this->verbose ? '' : $this->stdout("\n\t\t\t\tUpdating $label timestamp tag lifetime");
            $lifetime = $this->_remoteLifetime;
            $this->_client->removeTag($post->id, TagHelper::createTimestampTag($tag, $data['lifetime'], $data['timestamp']));
            $this->_client->addTag($post->id, TagHelper::createTimestampTag($tag, $lifetime, $data['timestamp']));
        }
        if (time() > ( $data['timestamp'] + ($lifetime * 60 * 60))) {
            !$this->verbose ? '' : $this->stdout("\n\t\t\t\tProcessing $label timestamp tag");
            return true;
        }
        return false;
    }

    private function _executeTimestampTag($post, $tag, $data)
    {
        switch ($tag) {
            case'#rm': {
                    $this->_client->deletePost($post->id);
                    break;
                }

            case'#cp': {
                    $this->_client->removeTag($post->id, $tag);
                    $this->_client->removeTag($post->id, TagHelper::createTimestampTag($tag, $data['lifetime'], $data['timestamp']));
                    break;
                }
        }
    }

    private function _processPostOut($post, $msg, $color = Console::FG_GREEN)
    {
        $this->stdout("\n$msg: \t");
        $this->stdout($post->url, $color, Console::BOLD);
    }

}
