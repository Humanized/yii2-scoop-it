<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace humanized\scoopit\cli;

use Yii;
use yii\console\Controller;
use humanized\scoopit\Client;
use humanized\scoopit\models\Source;
use humanized\scoopit\models\Scoop;
use humanized\scoopit\models\Tag;
use humanized\scoopit\models\Topic;
use yii\helpers\Console;

/**
 * Provides an interface to locally synchronise remote scoop.it topic data
 *
 * @name Scoop.it CLI Data Synchronisation Tool
 * @version 0.1
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
     * Acquire content for all topics available through remote Scoop.it account. 
     * Runs the synchronise actions on all topics fetched.
     * Requires local copy of topic to exist (see setup action).
     * 
     * @param type $lastUpdate
     * @return int
     */
    public function actionIndex($lastUpdate)
    {
        $this->_client = new Client();
        $topics = $this->_client->getTopics(TRUE);
        foreach ($topics as $topic) {
            $this->actionSynchronise($topic['id'], $lastUpdate);
        }
        return 0;
    }

    /**
     * 
     * Acquire content for specified topics.
     *  
     * 
     * @param string|int $topic
     * @param type $lastUpdate
     * @return int
     */
    public function actionSynchronise($topicId, $lastUpdate)
    {
        $this->_initSync($topicId);
        if ($this->_autoScoop) {
            $this->_autoScoop($lastUpdate);
        }

        if ($this->_saveSuggestions) {
            $this->_importSuggestions($lastUpdate);
        }

        $this->_importScoops($lastUpdate);

        return 0;
    }

    /*
     * ************************************************************************
     *             Private helper functions for synchronise action
     * ************************************************************************
     */

    /**
     * Private function to initialise run-time (private) variables
     * along with appropriate per-topic output
     * 
     * @param type $topicId
     * @return int
     */
    private function _initSync($topicId)
    {
        if (!isset($this->_client)) {
            $this->_client = new Client();
        }
        $this->_topic = Topic::resolve($topicId);
        if (NULL === $this->_topic) {
            if ($this->verbose) {
                $this->stdout("No Such Topic \n");
            }
            return 1;
        }
        //Configuration parameters
        $this->_autoScoop = (isset($this->module->params['autoScoopTopicCondition']) &&
                call_user_func($this->module->params['autoScoopTopicCondition'], $this->_topic));
        $this->_saveSuggestions = (isset($this->module->params['saveSuggestions']) &&
                $this->module->params['saveSuggestions']);

        if ($this->verbose) {
            //Print output
            $this->stdout('processing data for topic: ');
            $this->stdout($this->_topic->name . "--autoscoop: " .
                    ($this->_autoScoop ? "en" : "dis") .
                    "abled, --savesuggestions" .
                    ($this->_autoScoop ? "en" : "dis") .
                    "abled\n", Console::FG_GREEN, Console::BOLD);
        }
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
        if ($this->verbose) {
            $this->stdout("auto-scoop condition satisfied \n");
        }
        $this->_client->autoScoop($this->_topic->id, $lastUpdate);
    }

    /**
     * Private method for locally importing topiccal content suggested
     * When saveSuggestions is enabled as specified by config
     * @param type $lastUpdate
     */
    private function _importSuggestions($lastUpdate)
    {
        $this->stdout("Saving suggestions to local storage \n");
        foreach ($this->_client->curablePosts($this->_topic->id, $lastUpdate) as $data) {
            if ($this->verbose) {
                $this->stdout("\n Importing suggestion: ");
                $this->stdout($data->url . "\n", Console::FG_GREEN, Console::BOLD);
            }
            $this->_importSuggestion($data);
        }
    }

    /**
     * Private method for locally synchronising topical content published
     * @param type $lastUpdate
     */
    private function _importScoops($lastUpdate)
    {
        if ($this->verbose) {
            $this->stdout("Saving scoops to local storage \n");
        }
        foreach ($this->_client->curatedPosts($this->_topic->id, $lastUpdate)as $data) {
            $tags = $data->tags;
            $rm = in_array('!rm', $data->tags);
            if ($this->verbose) {
                $this->stdout("\n" . ($rm ? 'removing' : 'importing') . " scoop: ");
                $this->stdout($data->url . "\n", Console::FG_GREEN, Console::BOLD);
            }
            if (!$rm) {
                $this->_synchroniseScoop($data);
            }
            if ($rm) {
                //Remove Local Scoop Topic Link
                $source = Source::findItem($data);
                if (isset($source)) {
                    \humanized\scoopit\models\SourceTopic::deleteAll(['topic_id' => $this->_topic->id, 'source_id' => $source->id]);
                }
                //Remove Remote Scoop (notice we remove by id of remote)
                //  $this->_client->deleteScoop($data->id);
            }
        }
    }

    /*
     * ************************************************************************
     *       Private helper functions for individual post synchronisation
     * ************************************************************************
     */

    /**
     * Private method for locally importing suggested content meta-data for a single post
     * Is called both when importing suggested content, and synchronising publications.
     * 
     * This process attaches following user-defined functions.
     *
     * <table>
     * <tr><td>function-name</td><td>parameters</td><td>Comment</td></tr>
     * 
     * <tr><td>afterSourceLink</td><td>source</td>Function is run after successful import of a suggestion</tr>
     * <tr><td>afterTopicLink</td><td>source</td>Function is run after successful link of to topic</tr>
     * </table>
     * 
     * 
     * These functions should be defined at the location specified through 'postProcessorClass' module configuration parameter.  
     * 
     * @param type $lastUpdate
     */
    private function _importSuggestion($data)
    {


        //Get local copy of suggestion (using it's id or url)
        $local = Source::findItem($data);
        //Create it if it does not yet exit
        if (!isset($local)) {
            $local = Source::create($data);
        }
        //Setup topic postprocessor
        $local->topicPostProcessor = $this->_getPostProcessor('afterTopicLink');
        //Link Suggestion to topic and force remote flag
        $local->linkTopic($this->_topic->id, $data->id);

        if (isset($this->module->params['postProcessorClass']) && method_exists($this->module->params['postProcessorClass'], 'afterCurableSynchronised')) {
            call_user_func([$this->module->params['postProcessorClass'], 'afterCurableSynchronised'], $this->_topic, $local);
        }

        return $local;
    }

    private function _synchroniseScoop($data)
    {
        //create-or-retrieve local record storing suggestion meta-data
        $source = $this->_importSuggestion($data);
        if (!isset($source)) {
            $this->stderr('Unhandled Exception: Source could not be created or retrieved');
            return 1;
        }
        //create-or-retrieve updated local record storing publication meta-data and tags


        $scoop = Scoop::sync($data, $this->_getPostProcessor('afterScoop'), $this->_getPostProcessor('afterScoopTag'));
        if (!isset($scoop)) {
            $this->stderr('Unhandled Exception: Scoop could not be created or retrieved');
            return 1;
        }

        if (isset($this->module->params['postProcessorClass']) && method_exists($this->module->params['postProcessorClass'], 'afterCuratedSynchronised')) {
            call_user_func([$this->module->params['postProcessorClass'], 'afterCuratedSynchronised'], $this->_topic, $scoop);
        }
        return $scoop;
    }

    /*
     * ************************************************************************
     *             Private function for post-processor loading
     * ************************************************************************
     */

    private function _getPostProcessor($fnName)
    {
        if (isset($this->module->params['postProcessorClass']) &&
                method_exists($this->module->params['postProcessorClass'], $fnName)) {
            return [$this->module->params['postProcessorClass'], $fnName];
        }
        return null;
    }

}
