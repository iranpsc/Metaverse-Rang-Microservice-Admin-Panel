<?php

namespace Tests\Unit\Ci;

use PHPUnit\Framework\TestCase;
use TestResultsSummarizer;

require_once dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'TestResultsSummarizer.php';

class TestResultsSummarizerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'summarize-tests-'.bin2hex(random_bytes(4));
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tempDir.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->tempDir);

        parent::tearDown();
    }

    public function test_groups_feature_and_service_tests_and_lists_failures(): void
    {
        $junit = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="Feature" tests="3" failures="1" errors="0" skipped="0" time="1.5">
    <testcase name="index_returns_lands" classname="Tests\Feature\Lands\LandsApiTest" file="tests/Feature/Lands/LandsApiTest.php" time="0.40"/>
    <testcase name="transfer_fails_for_owned_land" classname="Tests\Feature\Lands\LandsApiTest" file="tests/Feature/Lands/LandsApiTest.php" time="0.50">
      <failure type="PHPUnit\Framework\ExpectationFailedException">Failed asserting that 403 matches expected 200.
</failure>
    </testcase>
    <testcase name="admins_can_list_roles" classname="Tests\Feature\AccessManagement\RolesControllerTest" file="tests/Feature/AccessManagement/RolesControllerTest.php" time="0.60"/>
  </testsuite>
  <testsuite name="Unit" tests="3" failures="0" errors="0" skipped="1" time="0.9">
    <testcase name="it_translates_fields" classname="Tests\Unit\Translations\TranslationServiceTest" file="tests/Unit/Translations/TranslationServiceTest.php" time="0.30"/>
    <testcase name="it_transfers_owner" classname="Tests\Unit\Lands\LandOwnerTransferServiceTest" file="tests/Unit/Lands/LandOwnerTransferServiceTest.php" time="0.40"/>
    <testcase name="user_has_email" classname="Tests\Unit\Models\UserModelTest" file="tests/Unit/Models/UserModelTest.php" time="0.20">
      <skipped/>
    </testcase>
  </testsuite>
</testsuites>
XML;

        $path = $this->tempDir.DIRECTORY_SEPARATOR.'junit.xml';
        file_put_contents($path, $junit);

        $markdown = implode("\n", (new TestResultsSummarizer)->summarizeJunit($path));

        $this->assertStringContainsString('## Test results', $markdown);
        $this->assertStringContainsString('| Controllers | 3 | 2 | 1 | 0 | 1.5s |', $markdown);
        $this->assertStringContainsString('| Services | 2 | 2 | 0 | 0 | 0.7s |', $markdown);
        $this->assertStringContainsString('| Other unit | 1 | 0 | 0 | 1 | 0.2s |', $markdown);
        $this->assertStringContainsString('| Lands | 2 | 1 | 1 | 0 | 0.9s |', $markdown);
        $this->assertStringContainsString('| AccessManagement | 1 | 1 | 0 | 0 | 0.6s |', $markdown);
        $this->assertStringContainsString('| TranslationService | 1 | 1 | 0 | 0 | 0.3s |', $markdown);
        $this->assertStringContainsString('| LandOwnerTransferService | 1 | 1 | 0 | 0 | 0.4s |', $markdown);
        $this->assertStringContainsString('| Models | 1 | 0 | 0 | 1 | 0.2s |', $markdown);
        $this->assertStringContainsString('### Failures', $markdown);
        $this->assertStringContainsString('Tests\\Feature\\Lands\\LandsApiTest::transfer_fails_for_owned_land', $markdown);
        $this->assertStringContainsString('Failed asserting that 403 matches expected 200.', $markdown);
    }

    public function test_clover_rollup_lists_controllers_and_services_lowest_coverage_first(): void
    {
        $clover = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<coverage>
  <project>
    <file name="/repo/app/Http/Controllers/Api/LandsController.php">
      <metrics statements="10" coveredstatements="10"/>
    </file>
    <file name="/repo/app/Http/Controllers/Api/WalletController.php">
      <metrics statements="8" coveredstatements="6"/>
    </file>
    <file name="/repo/app/Services/Translations/TranslationService.php">
      <metrics statements="20" coveredstatements="20"/>
    </file>
    <file name="/repo/app/Models/User.php">
      <metrics statements="5" coveredstatements="5"/>
    </file>
  </project>
</coverage>
XML;

        $path = $this->tempDir.DIRECTORY_SEPARATOR.'clover.xml';
        file_put_contents($path, $clover);

        $markdown = implode("\n", (new TestResultsSummarizer)->summarizeClover($path));

        $this->assertStringContainsString('## Coverage by layer', $markdown);
        $this->assertStringContainsString('**Total: 88.9%** (16/18 statements)', $markdown);
        $this->assertStringContainsString('| Api/WalletController | 6 | 8 | 75.0% |', $markdown);
        $this->assertStringContainsString('| Api/LandsController | 10 | 10 | 100.0% |', $markdown);
        $this->assertStringContainsString('| Translations/TranslationService | 20 | 20 | 100.0% |', $markdown);
        $this->assertStringNotContainsString('User.php', $markdown);

        $walletPos = strpos($markdown, 'Api/WalletController');
        $landsPos = strpos($markdown, 'Api/LandsController');
        $this->assertNotFalse($walletPos);
        $this->assertNotFalse($landsPos);
        $this->assertLessThan($landsPos, $walletPos);
    }

    public function test_summarize_notes_missing_junit_and_skips_missing_clover(): void
    {
        $markdown = implode("\n", (new TestResultsSummarizer)->summarize(
            $this->tempDir.DIRECTORY_SEPARATOR.'missing-junit.xml',
            $this->tempDir.DIRECTORY_SEPARATOR.'missing-clover.xml'
        ));

        $this->assertStringContainsString('JUnit report not found', $markdown);
        $this->assertStringNotContainsString('## Coverage by layer', $markdown);
    }
}
