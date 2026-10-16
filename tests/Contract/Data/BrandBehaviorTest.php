<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{Client, Configuration, DataBrandsService, DataAccountService};
use Automattic\Wistia\Brands\{BrandAttributes, Gradient, GradientStop, LogoInput, LogoDimensionsInput};
use Automattic\Wistia\Exception\DecodeException;
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Result\Data as D;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class BrandBehaviorTest extends TestCase
{
    private function client(array $bodies, int $status = 200): array
    {
        $transport = new class($bodies, $status) implements Transport {
            public array $requests = [];
            public function __construct(private array $bodies, private int $status) {}
            public function send(TransportRequest $request): TransportResponse {
                $this->requests[] = $request;
                if ($this->bodies === []) { throw new \LogicException('Unexpected extra request.'); }
                return new TransportResponse($this->status,['Content-Type'=>['application/json']],array_shift($this->bodies));
            }
        };
        return [new Client(new Configuration('synthetic-token'),$transport),$transport];
    }
    #[DataProvider('invalidInputs')]
    public function testInvalidInputsDoNotSend(callable $build): void
    {
        [$client,$transport]=$this->client([]);
        try { $build($client); self::fail('Expected local rejection.'); }
        catch (\InvalidArgumentException|\TypeError $e) {
            self::assertCount(0,$transport->requests);
            self::assertStringNotContainsString('private', $e->getMessage());
        }
    }
    public static function invalidInputs(): array
    {
        return [
            [fn()=>new R\CreateBrandRequest(new BrandAttributes())],
            [fn()=>new R\CreateBrandRequest(new BrandAttributes(name:' '))],
            [fn()=>new R\ShowBrandRequest('')], [fn()=>new R\DeleteBrandRequest(' ')],
            [fn()=>new R\UpdateBrandRequest('')], [fn()=>new R\ApplyBrandRequest('','media','a')],
            [fn()=>new R\ApplyBrandRequest('b','webinar','a')], [fn()=>new R\ApplyBrandRequest('b','folder','')],
            [fn()=>new BrandAttributes(primary_color:'private')],
            [fn()=>new BrandAttributes(contrast_icons:'bad')], [fn()=>new BrandAttributes(opaque_controls:'bad')],
            [fn()=>new BrandAttributes(name:null)],
            [fn()=>new GradientStop('#abc',INF)], [fn()=>new GradientStop('private',0)],
            [fn()=>new Gradient(['bad'])], [fn()=>new Gradient(['key'=>new GradientStop('#abc',0)])],
            [fn()=>new LogoInput('https://example.test/private.png')],
            [fn()=>new LogoInput('https://embed-ssl.wistia.com.evil.test/deliveries/a.png')],
            [fn()=>new LogoInput('https://embed-ssl.wistia.com/deliveries/a.png?private=1')],
            [fn()=>new LogoInput(size:NAN)],
            [fn()=>new R\UpdateBrandPreloadRequest(selected_player_color:'#abc')],
            [fn()=>new R\UpdateBrandPreloadRequest(selected_player_color:'112233\n')],
            [fn()=>new R\ListBrandsRequest(page:0)], [fn()=>new R\ListBrandsRequest(per_page:0)],
            [fn()=>new R\ListBrandsRequest(page:1,cursor:new R\Cursor(enabled:1))],
            [fn()=>new R\ListBrandsRequest(cursor:new R\Cursor(enabled:1),sort_by:'name')],
            [fn()=>new R\ListBrandsRequest(sort_by:'bad')], [fn()=>new R\ListBrandsRequest(sort_direction:2)],
        ];
    }
    public function testOmissionResetsAndExactWireValues(): void
    {
        self::assertSame('{}',(new R\UpdateBrandRequest('b'))->body()->contents());
        self::assertSame('{"body_font_family":"","page_logo":null}',(new R\UpdateBrandRequest('b',new BrandAttributes(body_font_family:'',page_logo:null)))->body()->contents());
        self::assertSame('{"page_logo":{"dimensions":null,"size":null}}',(new R\UpdateBrandRequest('b',new BrandAttributes(page_logo:new LogoInput(dimensions:null,size:null))))->body()->contents());
        self::assertSame('{}',(new R\UpdateBrandPreloadRequest())->body()->contents());
        self::assertSame('{"selected_player_color":""}',(new R\UpdateBrandPreloadRequest(selected_player_color:''))->body()->contents());
        self::assertSame('{"selected_logo_hashed_id":""}',(new R\UpdateBrandPreloadRequest(selected_logo_hashed_id:''))->body()->contents());
        self::assertSame('{"selected_player_color":"aAbBcC"}',(new R\UpdateBrandPreloadRequest(selected_player_color:'aAbBcC'))->body()->contents());
        self::assertSame('sync_to_customizations=true',(new R\DeleteBrandRequest('b',true))->query()->encode());
        self::assertSame('',(new R\DeleteBrandRequest('b'))->query()->encode());
        self::assertSame('{"resource_type":"folder","resource_id":"f"}',(new R\ApplyBrandRequest('b','folder','f'))->body()->contents());
        self::assertSame('{"resource_type":"channel","resource_id":"c","clear_overrides":true}',(new R\ApplyBrandRequest('b','channel','c',true))->body()->contents());
    }
    public function testTypedNestedResultsAndAbsentVersusNull(): void
    {
        $brand=D\ShowBrandResult::fromWire(json_decode(BrandCases::brand()),200)->data;
        self::assertSame('brand1',$brand->id); self::assertSame('#aabbcc',$brand->primary_color);
        self::assertInstanceOf(Gradient::class,$brand->page_background_color);
        self::assertSame('#112233',$brand->page_background_color->stops[0]->color);
        self::assertSame(100.0,$brand->page_background_color->stops[1]->position);
        self::assertSame(200,$brand->page_logo->dimensions->width); self::assertSame(1.0,$brand->page_logo->size);
        self::assertNull($brand->player_logo); self::assertContains('player_logo',$brand->presentFields);
        $empty=D\ShowBrandResult::fromWire((object)[],200)->data;
        self::assertNull($empty->player_logo); self::assertNotContains('player_logo',$empty->presentFields);
        $preload=D\GetBrandPreloadResult::fromWire(json_decode(BrandCases::preload()),200)->data;
        self::assertFalse($preload->has_brand_kits); self::assertNull($preload->brandfetch_brand->domain);
        self::assertSame('svg',$preload->brandfetch_brand->logos[0]->formats[0]->format);
        self::assertSame(['primary'],$preload->brandfetch_brand->logos[0]->tags);
        self::assertSame(0,$preload->brandfetch_brand->logos[0]->formats[0]->size);
        $colors=D\GetBrandKitColorsResult::fromWire(json_decode(BrandCases::colors()),200)->data;
        self::assertSame('#112233',$colors->colors[0]->value);
        self::assertSame(100.0,$colors->brand_gradients[0]->stops[1]->position);
        self::assertSame([],D\GetBrandKitColorsResult::fromWire(json_decode('{"colors":[],"brand_gradients":[]}'),200)->data->colors);
        self::assertStringNotContainsString('Studio',print_r($brand,true));
    }
    public function testDefaultBrandAndEffectiveBrandAreRemoteTruthNotLocalSimulation(): void
    {
        // Applying default detaches; the effective response may be a folder brand instead.
        [$client,$transport]=$this->client([BrandCases::brand('inherited-folder'),str_replace('"is_default":false','"is_default":true',BrandCases::brand('default'))]);
        $service=new DataBrandsService($client);
        self::assertSame('inherited-folder',$service->applyBrand(new R\ApplyBrandRequest('default','media','m'))->data->id);
        // Wistia ignores a default-brand rename; return its actual response without synthesizing a rename.
        self::assertTrue($service->updateBrand(new R\UpdateBrandRequest('default',new BrandAttributes(name:'Attempted rename')))->data->is_default);
        self::assertCount(2,$transport->requests);
    }
    #[DataProvider('invalidResponses')]
    public function testMalformedNestedResultsBecomeDecodeFailures(string $operation,string $body): void
    {
        [$class,$method,$request,,,,$status]=BrandCases::all()[$operation];
        [$client]=$this->client([$body],$status);
        $this->expectException(DecodeException::class); (new $class($client))->$method($request);
    }
    public static function invalidResponses(): array
    {
        return [
            ['ShowBrand','[]'], ['ListBrands','{}'], ['ListBrands','[null]'],
            ['ShowBrand','{"is_default":1}'], ['ShowBrand','{"border_radius":"0"}'],
            ['ShowBrand','{"contrast_icons":"unknown"}'], ['ShowBrand','{"name":null}'],
            ['ShowBrand','{"primary_color":[["#112233","bad"]]}'],
            ['ShowBrand','{"primary_color":[["#112233",0,100]]}'],
            ['ShowBrand','{"page_logo":{"dimensions":{"width":"100"}}}'],
            ['ShowBrand','{"player_logo":{"extra":1}}'], ['ShowBrand','{"extra":1}'],
            ['GetBrandPreload','{}'], ['GetBrandPreload','{"has_brand_kits":false,"brandfetch_brand":null}'],
            ['GetBrandPreload','{"has_brand_kits":false,"brandfetch_brand":{"domain":null,"logo":null}}'],
            ['GetBrandPreload','{"has_brand_kits":false,"brandfetch_brand":{"domain":null,"logo":null,"primary_color":null,"logos":[{"formats":[{"height":"bad"}]}]}}'],
            ['GetBrandKitColors','{"colors":[],"brand_gradients":[{"name":"G","stops":[{"color":"#112233"}]}]}'],
            ['GetBrandKitColors','{"colors":[{"name":"C"}],"brand_gradients":[]}'],
        ];
    }
    public function testLazyOffsetTraversalPreservesOrder(): void
    {
        [$client,$transport]=$this->client(['['.BrandCases::brand('a').']','[]']);
        $pages=(new DataBrandsService($client))->paginateBrands(new R\ListBrandsRequest(page:2,per_page:1,sort_by:'name',sort_direction:0));
        self::assertCount(0,$transport->requests);
        $items=iterator_to_array($pages->items()); self::assertSame('a',$items[0]->id);
        self::assertSame('https://api.wistia.com/modern/brands?sort_by=name&sort_direction=0&per_page=1&page=3',$transport->requests[1]->url);
    }
    #[DataProvider('cursorDirections')]
    public function testCursorTraversalPreservesUpdatedSort(bool $before): void
    {
        [$client,$transport]=$this->client(['['.BrandCases::brand('a','next').']','[]']);
        $cursor=$before ? new R\Cursor(before:'start') : new R\Cursor(enabled:1);
        $p=(new DataBrandsService($client))->paginateBrands(new R\ListBrandsRequest(cursor:$cursor,per_page:1,sort_by:'updated',sort_direction:0));
        self::assertCount(1,iterator_to_array($p->items()));
        self::assertStringContainsString('sort_by=updated&sort_direction=0',$transport->requests[1]->url);
        self::assertStringContainsString('cursor%5B'.($before?'before':'after').'%5D=next',$transport->requests[1]->url);
    }
    public static function cursorDirections(): array { return [[true],[false]]; }
    public function testRepeatedCursorStopsTraversal(): void
    {
        [$client]=$this->client(['['.BrandCases::brand('a','same').']','['.BrandCases::brand('b','same').']']);
        $p=(new DataBrandsService($client))->paginateBrands(new R\ListBrandsRequest(cursor:new R\Cursor(enabled:1),per_page:1));
        $this->expectException(\UnexpectedValueException::class); iterator_to_array($p->items());
    }
    public function testFiniteBoundIsNotRemoteCancellation(): void
    {
        [$client,$transport]=$this->client(['['.BrandCases::brand().']']);
        $p=(new DataBrandsService($client))->paginateBrands(new R\ListBrandsRequest(per_page:1),maxPages:1);
        try { iterator_to_array($p->items()); self::fail('Expected exhaustion.'); }
        catch (\OverflowException) { self::assertCount(1,$transport->requests); }
    }
}
