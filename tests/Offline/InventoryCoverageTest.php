<?php
declare(strict_types=1);

namespace Automattic\Wistia\Tests\Offline;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/tools/check-delivery.php';

final class InventoryCoverageTest extends TestCase
{
    private static function inventory(): array
    {
        return json_decode(file_get_contents(dirname(__DIR__, 2) . '/docs/rewrite/operations.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    public function testEveryIncludedOperationHasRealTypedMethodsDocumentationAndContractTests(): void
    {
        self::assertSame([], \deliveryErrors(self::inventory(), dirname(__DIR__, 2)));
    }

    public static function invalidMappings(): iterable
    {
        yield 'missing operation' => ['missing-operation', 'expected 166'];
        yield 'missing marker' => ['marker', 'missing offline implementation marker'];
        yield 'missing method' => ['method', 'missing service method'];
        yield 'wrong request' => ['request', 'method signature does not match'];
        yield 'wrong result' => ['result', 'method signature does not match'];
        yield 'missing docs' => ['docs', 'missing or unsafe documentation_target'];
        yield 'missing test' => ['test', 'missing or unsafe contract_test_target'];
        yield 'hidden resolution' => ['resolution', 'missing implementation-resolution evidence'];
        yield 'missing contract' => ['contract', 'missing approved implementation contract'];
        yield 'wrong alias' => ['alias', 'root Upload alias does not match'];
    }

    #[DataProvider('invalidMappings')]
    public function testIncompleteMappingsCannotPass(string $case, string $expected): void
    {
        $data = self::inventory();
        // Upload records have no unrelated catalog ordering assumptions.
        switch ($case) {
            case 'missing-operation': array_pop($data['upload_workflows']); break;
            case 'marker': unset($data['upload_workflows'][0]['delivery']); break;
            case 'method': $data['upload_workflows'][0]['planned']['method'] = 'UploadService::missing'; break;
            case 'request': $data['upload_workflows'][0]['planned']['request_type'] = \stdClass::class; break;
            case 'result': $data['upload_workflows'][0]['planned']['result_type'] = \stdClass::class; break;
            case 'docs': $data['upload_workflows'][0]['planned']['documentation_target'] = 'docs/reference/missing.md'; break;
            case 'test': $data['upload_workflows'][0]['planned']['contract_test_target'] = 'tests/Contract/missing.php'; break;
            case 'resolution': unset($data['contract_gaps'][0]['implementation_resolution']); break;
            case 'contract':
                foreach ($data['upload_workflows'] as &$row) { if ($row['id'] === 's3-copy') { unset($row['implementation_contract']); } }
                unset($row); break;
            case 'alias':
                foreach ($data['operations'] as &$row) { if ($row['id'] === 'post_') { $row['planned']['additional_method'] = 'UploadService::missing'; } }
                unset($row); break;
        }
        self::assertStringContainsString($expected, implode("\n", \deliveryErrors($data, dirname(__DIR__, 2))));
    }
}
