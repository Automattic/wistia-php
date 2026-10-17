<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{DataSearchService, DataResourceURLsService, DataReviewBundlesService, DataShareLinksService};
use Automattic\Wistia\Request\Data as R;

final class SharingCases
{
    public static function fixture(string $operation): string
    {
        return file_get_contents(__DIR__ . '/../../Offline/fixtures/sharing/' . $operation . '.json');
    }
    public static function all(): array
    {
        $cases = [
            'Search' => [DataSearchService::class, 'search', new R\SearchRequest('', tags:['a b','c'],resource_type:['media','folder']), 'GET', '/search?q=&tags%5B%5D=a%20b&tags%5B%5D=c&resource_type%5B%5D=media&resource_type%5B%5D=folder', null, 200],
            'ResolveResourceURLs' => [DataResourceURLsService::class, 'resolveResourceURLs', new R\ResolveResourceURLsRequest('media','m/a'), 'GET', '/resource_urls?type=media&hashed_id=m%2Fa', null, 200],
            'ListReviewBundles' => [DataReviewBundlesService::class, 'listReviewBundles', new R\ListReviewBundlesRequest(hashed_ids:['one','two'],name:'a b',sort_direction:0,page:2,per_page:5), 'GET', '/review_bundles?hashed_ids%5B%5D=one&hashed_ids%5B%5D=two&name=a%20b&sort_direction=0&page=2&per_page=5', null, 200],
            'CreateReviewBundle' => [DataReviewBundlesService::class, 'createReviewBundle', new R\CreateReviewBundleRequest(['one','two'],'Review',false), 'POST', '/review_bundles', '{"media_hashed_ids":["one","two"],"name":"Review","allow_downloads":false}', 201],
            'DeleteReviewBundle' => [DataReviewBundlesService::class, 'deleteReviewBundle', new R\DeleteReviewBundleRequest('b/a'), 'DELETE', '/review_bundles/b%2Fa', null, 200],
            'ResolveShareLink' => [DataShareLinksService::class, 'resolveShareLink', new R\ResolveShareLinkRequest('old/slug'), 'GET', '/share_links/old%2Fslug', null, 200],
            'ShowShareLink' => [DataShareLinksService::class, 'showShareLink', new R\ShowShareLinkRequest('m/a'), 'GET', '/medias/m%2Fa/share_link', null, 200],
            'UpdateShareLink' => [DataShareLinksService::class, 'updateShareLink', new R\UpdateShareLinkRequest('m/a','locked'), 'PUT', '/medias/m%2Fa/share_link', '{"visibility":"locked"}', 200],
            'DeleteShareLink' => [DataShareLinksService::class, 'deleteShareLink', new R\DeleteShareLinkRequest('m/a'), 'DELETE', '/medias/m%2Fa/share_link', null, 200],
        ];
        foreach ($cases as $name => &$case) { $case[] = $name === 'DeleteShareLink' ? '' : self::fixture($name); }
        return $cases;
    }
}
