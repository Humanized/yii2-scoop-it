<?php

namespace humanized\scoopit\components;

class TagHelper
{

    public static function isTagSkipped($post)
    {
        return in_array('#rm', $post->tags) || in_array('#cp', $post->tags);
    }

    public static function readTag($post, $prefix)
    {
        foreach ($post->tags as $tag) {
            //   echo $tag;
            if (strpos($tag, $prefix . '_at:') === 0) {
                return substr($tag, 7);
            }
        }
        return null;
    }

    public static function implodeTimestampTag($tag)
    {
        $separator = strpos($tag, '|');
        return ['timestamp' => substr($tag, 0, $separator), 'lifetime' => substr($tag, $separator + 1)];
    }

    public static function createTimestampTag($prefix, $lifetime, $time = null)
    {
        $timestamp = (isset($time) ? $time : time());
        return $prefix . "_at:$timestamp|$lifetime";
    }

    public static function shortcuts($post)
    {
        $out = [];
        foreach ($post->tags as $tag) {
            if (substr($tag, 0, 1) == '@') {
                $out[] = substr($tag, 1);
            }
        }
        return $out;
    }

    public static function duplicates($post)
    {
        $out = [];
        foreach ($post->tags as $tag) {
            if (substr($tag, 0, 1) == '#' && false !== strpos($tag, '|')) {
                $out[] = self::implodeDuplicateTag($tag);
            }
        }
        return $out;
    }

    public static function implodeDuplicateTag($tag)
    {
        $separator = strpos($tag, '|');
        return ['topicId' => substr($tag, 1, $separator), 'postId' => substr($tag, $separator + 1)];
    }

    public static function createDuplicateTag($topicId, $postId)
    {
        return '#' . $topicId . '|' . $postId;
    }

}
