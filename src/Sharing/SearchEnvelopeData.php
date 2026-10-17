<?php
declare(strict_types=1);

namespace Automattic\Wistia\Sharing;

use Automattic\Wistia\Media\Wire;

final readonly class SearchEnvelopeData
{
    public function __construct(
        /** @var list<SearchEnvelopeDataFoldersItem>|null */
        #[\SensitiveParameter] public array $folders,
        /** @var list<SearchEnvelopeDataSubfoldersItem>|null */
        #[\SensitiveParameter] public array $subfolders,
        /** @var list<SearchEnvelopeDataMediasItem>|null */
        #[\SensitiveParameter] public array $medias,
        /** @var list<SearchEnvelopeDataChannelsItem>|null */
        #[\SensitiveParameter] public array $channels,
        /** @var list<SearchEnvelopeDataChannelEpisodesItem>|null */
        #[\SensitiveParameter] public array $channel_episodes,
        /** @var list<SearchEnvelopeDataWebinarsItem>|null */
        #[\SensitiveParameter] public array $webinars,
        /** @var list<string> */ public array $presentFields,
    ) {}
    public static function fromWire(#[\SensitiveParameter] mixed $value): self
    {
        Wire::object($value, ['folders', 'subfolders', 'medias', 'channels', 'channel_episodes', 'webinars'], ['folders', 'subfolders', 'medias', 'channels', 'channel_episodes', 'webinars']);
        return new self(
            Wire::items($value->folders, static fn($item) => SearchEnvelopeDataFoldersItem::fromWire($item)),
            Wire::items($value->subfolders, static fn($item) => SearchEnvelopeDataSubfoldersItem::fromWire($item)),
            Wire::items($value->medias, static fn($item) => SearchEnvelopeDataMediasItem::fromWire($item)),
            Wire::items($value->channels, static fn($item) => SearchEnvelopeDataChannelsItem::fromWire($item)),
            Wire::items($value->channel_episodes, static fn($item) => SearchEnvelopeDataChannelEpisodesItem::fromWire($item)),
            Wire::items($value->webinars, static fn($item) => SearchEnvelopeDataWebinarsItem::fromWire($item)),
            array_keys(get_object_vars($value)),
        );
    }
    public function __debugInfo(): array { return []; }
}
