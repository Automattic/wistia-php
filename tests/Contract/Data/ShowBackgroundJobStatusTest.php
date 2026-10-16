<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Contract\Data;

use Automattic\Wistia\{Client, Configuration, DataBackgroundJobStatusService};
use Automattic\Wistia\Exception\{AuthenticationException, ApiException, DecodeException, RateLimitException, TransportException};
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Jobs\{BulkOperation, JobStatus};
use Automattic\Wistia\Request\Data\ShowBackgroundJobStatusRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ShowBackgroundJobStatusTest extends TestCase
{
    private function service(string $body, int $status = 200, string $type = 'application/json'): array
    {
        $transport = new class($body, $status, $type) implements Transport {
            public array $requests = [];
            public function __construct(private string $body, private int $status, private string $type) {}
            public function send(TransportRequest $request): TransportResponse {
                $this->requests[] = $request;
                return new TransportResponse($this->status, ['Content-Type' => [$this->type], 'Retry-After' => ['5']], $this->body);
            }
        };
        return [new DataBackgroundJobStatusService(new Client(new Configuration('synthetic-token'), $transport)), $transport];
    }
    public function testExactWireAndTypedBulkPartialFailure(): void
    {
        [$service, $transport] = $this->service('{"background_job_status":{"id":42,"hashed_id":"job-hash","status":"finished","object":{"type":"bulk_operation","summary":{"total":2,"succeeded":1,"failed":1},"results":[{"position":0,"operation":"create","resource_type":"media","id":"record","status":"succeeded"},{"position":1,"status":"failed","error":"synthetic-private-detail"}]}}}');
        $job = $service->showBackgroundJobStatus(new ShowBackgroundJobStatusRequest('hash/ +?'))->backgroundJobStatus;
        self::assertSame(42, $job->id); self::assertSame('job-hash', $job->hashedId); self::assertSame(JobStatus::Finished, $job->status);
        self::assertTrue($job->hasFailures()); self::assertSame(2, $job->object->summary->total);
        self::assertSame(BulkOperation::Create, $job->object->results[0]->operation);
        self::assertNull($job->object->results[1]->id); self::assertSame('synthetic-private-detail', $job->object->results[1]->error);
        self::assertStringNotContainsString('synthetic-private-detail', print_r($job, true));
        $request = $transport->requests[0];
        self::assertSame('GET', $request->method);
        self::assertSame('https://api.wistia.com/modern/background_job_status/hash%2F%20%2B%3F', $request->url);
        self::assertSame('Bearer synthetic-token', $request->headers['Authorization']);
        self::assertSame('2026-09', $request->headers['X-Wistia-API-Version']); self::assertNull($request->body);
        self::assertCount(1, $transport->requests);
    }
    #[DataProvider('states')]
    public function testAllDocumentedStates(string $state): void
    {
        [$service] = $this->service('{"background_job_status":{"id":0,"hashed_id":"x","status":"'.$state.'"}}');
        $job = $service->showBackgroundJobStatus(new ShowBackgroundJobStatusRequest('0'))->backgroundJobStatus;
        self::assertSame($state, $job->status->value); self::assertNull($job->object);
    }
    public static function states(): array { return array_map(fn($s) => [$s], ['queued','started','finished','failed']); }
    #[DataProvider('invalidBodies')]
    public function testInvalidDocumentedShapeIsSanitized(string $body): void
    {
        [$service, $transport] = $this->service($body);
        try { $service->showBackgroundJobStatus(new ShowBackgroundJobStatusRequest('x')); self::fail('Expected decode failure'); }
        catch (DecodeException $e) {
            self::assertSame(200, $e->status); self::assertFalse($e->remoteOutcomeUncertain); self::assertNull($e->getPrevious());
            self::assertStringNotContainsString('private', $e->getMessage()); self::assertCount(1, $transport->requests);
        }
    }
    public static function invalidBodies(): array
    {
        $base=['background_job_status'=>['id'=>1,'hashed_id'=>'private','status'=>'queued']]; $cases=['null','[]','{','{"background_job_status":null}'];
        foreach (['id'=>'1','hashed_id'=>null,'status'=>'unknown','object'=>null,'extra'=>'private'] as $k=>$v) { $x=$base; $x['background_job_status'][$k]=$v; $cases[]=json_encode($x); }
        $cases[]='{"background_job_status":{"id":1,"hashed_id":"private","status":"finished","object":{"type":"unknown","summary":{},"results":[]}}}';
        $cases[]='{"background_job_status":{"id":1,"hashed_id":"private","status":"finished","object":{"type":"bulk_operation","summary":{"total":1,"succeeded":0,"failed":1},"results":[{"position":0,"status":"failed","error":null}]}}}';
        $bulk=['type'=>'bulk_operation','summary'=>['total'=>1,'succeeded'=>0,'failed'=>1],'results'=>[['position'=>0,'status'=>'failed']]];
        $nested=[];
        $x=$bulk; $x['results']=(object)[]; $nested[]=$x;
        $x=$bulk; $x['summary']['total']=1.5; $nested[]=$x;
        $x=$bulk; unset($x['summary']['succeeded']); $nested[]=$x;
        $x=$bulk; $x['summary']['extra']='private'; $nested[]=$x;
        $x=$bulk; unset($x['results'][0]['position']); $nested[]=$x;
        foreach (['position'=>'0','status'=>'private-unknown','operation'=>'private-unknown','resource_type'=>false,'id'=>0,'error'=>[],'extra'=>'private'] as $key=>$value) {
            $x=$bulk; $x['results'][0][$key]=$value; $nested[]=$x;
        }
        foreach ($nested as $content) { $x=$base; $x['background_job_status']['object']=$content; $cases[]=json_encode($x); }
        $x=$base; unset($x['background_job_status']['hashed_id']); $cases[]=json_encode($x);
        $x=$base; $x['extra']='private'; $cases[]=json_encode($x);
        $cases[]='{"background_job_status":{"id":99999999999999999999999,"hashed_id":"private","status":"queued"}}';
        return array_map(fn($x)=>[$x],$cases);
    }
    #[DataProvider('errors')]
    public function testSharedFailureContract(int $status, string $class): void
    {
        [$service, $transport] = $this->service('private', $status);
        try { $service->showBackgroundJobStatus(new ShowBackgroundJobStatusRequest('x')); self::fail('Expected error'); }
        catch (\Automattic\Wistia\Exception\WistiaException $e) {
            self::assertInstanceOf($class,$e); self::assertFalse($e->remoteOutcomeUncertain); self::assertSame('5',$e->rateLimit->retryAfter);
            self::assertStringNotContainsString('private',$e->getMessage()); self::assertCount(1,$transport->requests);
        }
    }
    public static function errors(): array { return [[401,AuthenticationException::class],[404,ApiException::class],[429,RateLimitException::class],[500,ApiException::class],[201,DecodeException::class]]; }
    public function testReadOnlyPurchaseLabelAndEmptyBulkResultsAreDecodedWithoutSubmittingActions():void
    {
        [$service,$transport]=$this->service('{"background_job_status":{"id":1,"hashed_id":"x","status":"started","object":{"type":"bulk_operation","summary":{"total":1,"succeeded":0,"failed":0},"results":[]}}}');
        $job=$service->showBackgroundJobStatus(new ShowBackgroundJobStatusRequest('x'))->backgroundJobStatus;
        self::assertSame([],$job->object->results);self::assertFalse($job->hasFailures());
        [$service,$transport]=$this->service('{"background_job_status":{"id":1,"hashed_id":"x","status":"finished","object":{"type":"bulk_operation","summary":{"total":1,"succeeded":1,"failed":0},"results":[{"position":0,"operation":"purchase","status":"succeeded"}]}}}');
        $job=$service->showBackgroundJobStatus(new ShowBackgroundJobStatusRequest('x'))->backgroundJobStatus;
        self::assertSame(BulkOperation::Purchase,$job->object->results[0]->operation);self::assertSame('GET',$transport->requests[0]->method);self::assertNull($transport->requests[0]->body);
    }
    public function testEmptyIdentifierRejected(): void { $this->expectException(\InvalidArgumentException::class); new ShowBackgroundJobStatusRequest(''); }
    public function testContentTypeValidated(): void { [$s]=$this->service('{}',200,'text/html'); $this->expectException(DecodeException::class); $s->showBackgroundJobStatus(new ShowBackgroundJobStatusRequest('x')); }
}
