<?php
declare(strict_types=1);
namespace Automattic\Wistia\Tests\Offline\Jobs;
use Automattic\Wistia\{Client, Configuration, DataBackgroundJobStatusService};
use Automattic\Wistia\Http\{Transport, TransportRequest, TransportResponse};
use Automattic\Wistia\Jobs\{BackgroundJobPoller, PollingOptions, PollingRuntime, PollingTermination};
use Automattic\Wistia\Request\Data\ShowBackgroundJobStatusRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BackgroundJobPollerTest extends TestCase
{
    private function scenario(array $states): array
    {
        $transport=new class($states) implements Transport {
            public int $calls=0; public array $methods=[];
            public function __construct(private array $states){}
            public function send(TransportRequest $r):TransportResponse {
                $this->methods[]=$r->method;$state=$this->states[$this->calls++];
                if ($state==='transport-error') {throw new \RuntimeException('private-remote-detail');}
                $job=['id'=>42,'hashed_id'=>'hash','status'=>$state==='partial'?'finished':$state];
                if($state==='partial'){$job['object']=['type'=>'bulk_operation','summary'=>['total'=>1,'succeeded'=>0,'failed'=>1],'results'=>[['position'=>0,'status'=>'failed','error'=>'private-remote-detail']]];}
                return new TransportResponse(200,['Content-Type'=>['application/json']],json_encode(['background_job_status'=>$job]));
            }
        };
        $runtime=new class implements PollingRuntime {
            public float $time=100; public array $waits=[];
            public function now():float{return $this->time;}
            public function wait(float $seconds):void{$this->waits[]=$seconds;$this->time+=$seconds;}
        };
        return [new BackgroundJobPoller(new DataBackgroundJobStatusService(new Client(new Configuration('synthetic'),$transport)),$runtime),$transport,$runtime];
    }
    public function testQueuedStartedFinishedWithFakeClockAndWait():void
    {
        [$p,$t,$clock]=$this->scenario(['queued','started','finished']);
        $r=$p->poll(new ShowBackgroundJobStatusRequest('hash'),new PollingOptions(4,0.25));
        self::assertSame(PollingTermination::Completed,$r->termination);self::assertSame(3,$r->attempts);self::assertSame(0.5,$r->elapsedSeconds);
        self::assertSame([0.25,0.25],$clock->waits);self::assertSame(['GET','GET','GET'],$t->methods);self::assertSame('hash',$r->last->backgroundJobStatus->hashedId);
    }
    #[DataProvider('failures')]
    public function testReportedJobAndFinishedPartialFailuresAreNotSuccessful(string $state):void
    {
        [$p,$t,$clock]=$this->scenario([$state]);$r=$p->poll(new ShowBackgroundJobStatusRequest('hash'));
        self::assertSame(PollingTermination::ReportedFailure,$r->termination);self::assertTrue($r->last->backgroundJobStatus->hasFailures());self::assertSame(1,$t->calls);self::assertSame([],$clock->waits);
        self::assertStringNotContainsString('private-remote-detail',print_r($r,true));
    }
    public static function failures():array{return [['failed'],['partial']];}
    public function testExhaustionIsLocalNotRemoteFailureOrCancellation():void
    {
        [$p,$t,$clock]=$this->scenario(['queued','started']);$r=$p->poll(new ShowBackgroundJobStatusRequest('hash'),new PollingOptions(2,0));
        self::assertSame(PollingTermination::Exhausted,$r->termination);self::assertSame('started',$r->last->backgroundJobStatus->status->value);self::assertFalse($r->last->backgroundJobStatus->hasFailures());self::assertSame(['GET','GET'],$t->methods);self::assertSame([0.0],$clock->waits);
    }
    public function testOneAttemptNoWaitAndCompletionWinsAtBound():void
    {
        [$p,$t,$clock]=$this->scenario(['finished']);$r=$p->poll(new ShowBackgroundJobStatusRequest('hash'),new PollingOptions(1));
        self::assertSame(PollingTermination::Completed,$r->termination);self::assertSame([],$clock->waits);
    }
    public function testReadFailurePropagatesWithoutRetryOrSleep():void
    {
        [$p,$t,$clock]=$this->scenario(['transport-error']);
        try{$p->poll(new ShowBackgroundJobStatusRequest('hash'));self::fail();}
        catch(\Automattic\Wistia\Exception\TransportException $e){self::assertFalse($e->remoteOutcomeUncertain);self::assertNull($e->getPrevious());self::assertSame(1,$t->calls);self::assertSame([],$clock->waits);self::assertStringNotContainsString('private',$e->getMessage());}
    }
    #[DataProvider('invalidOptions')]
    public function testFiniteValidatedOptions(int $attempts,float $interval):void{$this->expectException(\InvalidArgumentException::class);new PollingOptions($attempts,$interval);}
    public static function invalidOptions():array{return [[0,1],[-1,1],[1,-0.1],[1,INF],[1,NAN],[1,86401]];}
}
