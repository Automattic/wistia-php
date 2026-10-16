<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{DataBrandsService, DataAccountService};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Brands\{BrandAttributes, Gradient, GradientStop, LogoInput, LogoDimensionsInput};

/** Hand-authored synthetic wire shapes, never copied from a Wistia account. */
final class BrandCases
{
    public static function brand(string $id = 'brand1', ?string $cursor = 'next'): string
    {
        return json_encode(['id'=>$id,'name'=>'Studio','is_default'=>false,'created'=>'2026-10-01T00:00:00Z','updated'=>'2026-10-01T00:00:00Z','primary_color'=>'#aabbcc','page_background_color'=>[['#112233',0],['#ffffff',100]],'body_font_family'=>null,'headline_font_family'=>'Arial','button_font_family'=>'Arial','border_radius'=>0,'contrast_icons'=>'unset','opaque_controls'=>'disabled','page_logo'=>['url'=>'https://embed-ssl.wistia.com/deliveries/logo123.png','dimensions'=>['width'=>200,'height'=>100],'size'=>1],'player_logo'=>null,'cursor'=>$cursor],JSON_THROW_ON_ERROR);
    }
    public static function preload(): string
    {
        return '{"has_brand_kits":false,"brandfetch_brand":{"domain":null,"primary_color":null,"logo":null,"font":null,"colors":["#112233"],"logos":[{"theme":"dark","type":"logo","tags":["primary"],"formats":[{"src":"https://example.test/logo.svg","format":"svg","background":null,"height":null,"width":100,"size":0}]}],"status":204}}';
    }
    public static function colors(): string
    {
        return '{"colors":[{"name":"Studio primary color","value":"#112233"}],"brand_gradients":[{"name":"Studio page background color","stops":[{"color":"#112233","position":0},{"color":"#ffffff","position":100}]}]}';
    }
    public static function all(): array
    {
        $b=self::brand(); $p=self::preload();
        return [
            'ListBrands'=>[DataBrandsService::class,'listBrands',new R\ListBrandsRequest(page:2,per_page:10,sort_by:'name',sort_direction:0),'GET','/brands?page=2&per_page=10&sort_by=name&sort_direction=0',null,200,'['.$b.']'],
            'CreateBrand'=>[DataBrandsService::class,'createBrand',new R\CreateBrandRequest(new BrandAttributes(name:'Studio',primary_color:'#abc',page_background_color:new Gradient([new GradientStop('#123',0),new GradientStop('#fff',100)]),page_logo:new LogoInput('https://embed-ssl.wistia.com/deliveries/logo123.png',new LogoDimensionsInput(200,100),1))),'POST','/brands','{"name":"Studio","primary_color":"#abc","page_background_color":[["#123",0],["#fff",100]],"page_logo":{"url":"https://embed-ssl.wistia.com/deliveries/logo123.png","dimensions":{"width":200,"height":100},"size":1}}',201,$b],
            'ShowBrand'=>[DataBrandsService::class,'showBrand',new R\ShowBrandRequest('a/b'),'GET','/brands/a%2Fb',null,200,$b],
            'UpdateBrand'=>[DataBrandsService::class,'updateBrand',new R\UpdateBrandRequest('a/b',new BrandAttributes(primary_color:null,body_font_family:null,border_radius:0,contrast_icons:'unset',opaque_controls:'disabled',player_logo:null)),'PUT','/brands/a%2Fb','{"primary_color":null,"body_font_family":null,"border_radius":0,"contrast_icons":"unset","opaque_controls":"disabled","player_logo":null}',200,$b],
            'DeleteBrand'=>[DataBrandsService::class,'deleteBrand',new R\DeleteBrandRequest('a/b',sync_to_customizations:false),'DELETE','/brands/a%2Fb?sync_to_customizations=false',null,200,$b],
            'ApplyBrand'=>[DataBrandsService::class,'applyBrand',new R\ApplyBrandRequest('a/b','media','media1',clear_overrides:false),'POST','/brands/a%2Fb/apply','{"resource_type":"media","resource_id":"media1","clear_overrides":false}',200,$b],
            'GetBrandPreload'=>[DataAccountService::class,'getBrandPreload',new R\GetBrandPreloadRequest(),'GET','/brand_preload',null,200,$p],
            'UpdateBrandPreload'=>[DataAccountService::class,'updateBrandPreload',new R\UpdateBrandPreloadRequest(selected_player_color:'#123456',selected_logo_hashed_id:''),'PUT','/brand_preload','{"selected_player_color":"#123456","selected_logo_hashed_id":""}',200,$p],
            'GetBrandKitColors'=>[DataAccountService::class,'getBrandKitColors',new R\GetBrandKitColorsRequest(),'GET','/brand_kit_colors',null,200,self::colors()],
        ];
    }
}
