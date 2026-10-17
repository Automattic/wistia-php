<?php
declare(strict_types=1);

namespace Automattic\Wistia\Bulk;

enum ScopeType: string
{
    case Account = 'account';
    case Folder = 'folder';
    case Subfolder = 'subfolder';
    case Channel = 'channel';
    case Media = 'media';
}
