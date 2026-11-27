<?php

use C0deM1ner\LaravelTelegramLogger\Actions\ChunkTelegramMessageAction;

it('returns a single chunk when the text fits the limit', function () {
    expect((new ChunkTelegramMessageAction)->execute('short message'))
        ->toBe(['short message']);
});

it('splits on whitespace and keeps html tags balanced across chunks', function () {
    expect((new ChunkTelegramMessageAction)->execute('<b>hello world foo</b>', 10))
        ->toBe(['<b>hello</b>', '<b>world</b>', '<b>foo</b>']);
});

it('never splits a tag with attributes and reopens it with its attributes', function () {
    expect((new ChunkTelegramMessageAction)->execute('one two <blockquote expandable>x y</blockquote>', 30))
        ->toBe(['one two', '<blockquote expandable>x</blockquote>', '<blockquote expandable>y</blockquote>']);

    expect((new ChunkTelegramMessageAction)->execute('<b>bold <i>both</i> tail</b>', 15))
        ->toBe(['<b>bold</b>', '<b><i>both</i></b>', '<b>tail</b>']);
});

it('does not cut through a tag or an entity when there is no space', function () {
    expect((new ChunkTelegramMessageAction)->execute('aaaaaaa<b>x</b>', 9))
        ->toBe(['aaaaaaa', '<b>x</b>']);

    expect((new ChunkTelegramMessageAction)->execute('aaaaaa&amp;b', 9))
        ->toBe(['aaaaaa', '&amp;b']);
});

it('splits on newlines too', function () {
    expect((new ChunkTelegramMessageAction)->execute("line one\nline two", 12))
        ->toBe(['line one', 'line two']);
});

it('counts multibyte characters, not bytes', function () {
    expect((new ChunkTelegramMessageAction)->execute('ббб ббб', 4))
        ->toBe(['ббб', 'ббб']);
});
