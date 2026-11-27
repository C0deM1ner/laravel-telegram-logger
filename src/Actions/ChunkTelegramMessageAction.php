<?php

namespace C0deM1ner\LaravelTelegramLogger\Actions;

final class ChunkTelegramMessageAction
{
    public function execute(string $text, int $limit = 4096): array
    {
        $chunks = [];

        while (mb_strlen($text) > $limit) {
            $head = mb_substr($text, 0, $limit);
            $breakpoint = max((int) mb_strrpos($head, ' '), (int) mb_strrpos($head, "\n")) ?: $limit;

            $chunk = $this->withoutPartial(mb_substr($text, 0, $breakpoint));

            // no usable space: cut at the limit, still outside tags and entities
            if ($chunk === '') {
                $chunk = $this->withoutPartial($head);
            }

            // only tags fit in the limit: cut raw instead of looping forever on reopened tags
            if (trim(strip_tags($chunk)) === '') {
                $chunks[] = $head;
                $text = mb_substr($text, $limit);

                continue;
            }

            $text = ltrim(mb_substr($text, mb_strlen($chunk)));

            // closing tags right after the cut belong to this chunk; Telegram doesn't count tags toward the limit
            while (preg_match('/^<\/[a-z][a-z0-9-]*>\s*/i', $text, $closing)) {
                $chunk .= trim($closing[0]);
                $text = mb_substr($text, mb_strlen($closing[0]));
            }

            $open = [];
            preg_match_all('/<(\/?)([a-z][a-z0-9-]*)[^>]*>/i', $chunk, $tags, PREG_SET_ORDER);

            foreach ($tags as [$tag, $closing, $name]) {
                if ($closing) {
                    array_pop($open);
                } else {
                    $open[] = [$name, $tag];
                }
            }

            $chunks[] = rtrim($chunk).implode('', array_map(fn (array $tag) => "</{$tag[0]}>", array_reverse($open)));
            $text = implode('', array_column($open, 1)).$text;
        }

        if ($text !== '' || $chunks === []) {
            $chunks[] = $text;
        }

        return $chunks;
    }

    // cuts off a trailing "<tag ..." or "&entity" that has no closing character yet
    private function withoutPartial(string $chunk): string
    {
        foreach (['<' => '>', '&' => ';'] as $start => $end) {
            $startAt = mb_strrpos($chunk, $start);
            $endAt = mb_strrpos($chunk, $end);

            if ($startAt !== false && ($endAt === false || $endAt < $startAt)) {
                $chunk = mb_substr($chunk, 0, $startAt);
            }
        }

        return $chunk;
    }
}
