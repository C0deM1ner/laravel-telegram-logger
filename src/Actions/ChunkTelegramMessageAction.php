<?php

namespace C0deM1ner\LaravelTelegramLogger\Actions;

final class ChunkTelegramMessageAction
{
    function execute(string $text, int $limit = 4096): array
    {
        $chunks = [];

        while (mb_strlen($text) > $limit) {
            $breakpoint = mb_strrpos(mb_substr($text, 0, $limit), ' ');
            $breakpoint = $breakpoint ?: $limit;

            $chunk = mb_substr($text, 0, $breakpoint);
            $text = mb_substr($text, $breakpoint);

            preg_match_all('/<(s?\/?[a-z0-9]+)>/i', $chunk, $matches);
            $tags = $matches[1];

            $stack = [];
            foreach ($tags as $tag) {
                if (strpos($tag, '/') !== 0) {
                    array_push($stack, $tag);
                } else {
                    array_pop($stack);
                }
            }

            $closingTags = '';
            $openingTagsForNext = '';

            while (!empty($stack)) {
                $tag = array_pop($stack);
                $closingTags .= "</$tag>";
                $openingTagsForNext = "<$tag>" . $openingTagsForNext;
            }

            $chunks[] = $chunk . $closingTags;
            $text = $openingTagsForNext . ltrim($text);
        }

        $chunks[] = $text;

        return $chunks;
    }
}
