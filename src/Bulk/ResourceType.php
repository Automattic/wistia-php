<?php
declare(strict_types=1);

namespace Automattic\Wistia\Bulk;

enum ResourceType: string
{
    case Media = 'media';
    case Folder = 'folder';
    case Subfolder = 'subfolder';
    case Channel = 'channel';
    case ChannelEpisode = 'channel_episode';
    case Captions = 'captions';
    case CustomizationAccess = 'customization_access';
    case CustomizationAccessibility = 'customization_accessibility';
    case CustomizationAppearance = 'customization_appearance';
    case CustomizationChapters = 'customization_chapters';
    case CustomizationEngagement = 'customization_engagement';
    case CustomizationLeadCapture = 'customization_lead_capture';
    case CustomizationPlayback = 'customization_playback';
    case CustomizationRelatedMedia = 'customization_related_media';
    case CustomizationSharing = 'customization_sharing';
    case CustomizationThumbnail = 'customization_thumbnail';
}
