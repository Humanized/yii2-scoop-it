<?php

namespace humanized\scoopit\components;

class TagHelper
{

    public static function isRemoved($post)
    {
        return in_array('#rm', $post->tags);
    }

    public static function readRemovalTag($post)
    {
        foreach ($post->tags as $tag) {
            //   echo $tag;
            if (strpos($tag, '#rm_at:') === 0) {
                return substr($tag, 7);
            }
        }
        return null;
    }

    public static function implodeRemovalTag($tag)
    {
        $separator = strpos($tag, '|');
        return ['timestamp' => substr($tag, 1, $separator), 'lifetime' => substr($tag, $separator + 1)];
    }

    public static function createRemovalTag($lifetime, $time = null)
    {
        $timestamp = (isset($time) ? $time : time());
        return "#rm_at:$timestamp|$lifetime";
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
