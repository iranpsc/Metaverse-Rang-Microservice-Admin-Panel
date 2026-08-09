<?php

namespace Tests\Unit\KycVerifyText;

use App\Models\KycVerifyText;
use Tests\Concerns\CreatesKycVideoTextApiSchema;
use Tests\TestCase;

class KycVerifyTextModelTest extends TestCase
{
    use CreatesKycVideoTextApiSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpKycVideoTextApiSchema();
    }

    // -------------------------------------------------------------------------
    // Factory & persistence
    // -------------------------------------------------------------------------

    public function test_factory_creates_valid_record(): void
    {
        $videoText = KycVerifyText::factory()->create();

        $this->assertInstanceOf(KycVerifyText::class, $videoText);
        $this->assertNotNull($videoText->id);
        $this->assertNotEmpty($videoText->text);
        $this->assertNotNull($videoText->created_at);
        $this->assertNotNull($videoText->updated_at);
    }

    public function test_model_uses_kyc_verify_texts_table(): void
    {
        $videoText = new KycVerifyText;

        $this->assertSame('kyc_verify_texts', $videoText->getTable());
    }

    public function test_mass_assignment_allows_text_field(): void
    {
        $videoText = KycVerifyText::create([
            'text' => 'Mass assigned verification text',
        ]);

        $this->assertDatabaseHas('kyc_verify_texts', [
            'id' => $videoText->id,
            'text' => 'Mass assigned verification text',
        ]);
    }

    public function test_text_persists_unicode_characters(): void
    {
        $unicodeText = 'متن فارسی با emoji 🎥';

        $videoText = KycVerifyText::factory()->create(['text' => $unicodeText]);

        $this->assertSame($unicodeText, $videoText->fresh()->text);
    }

    public function test_update_changes_text_and_updated_at(): void
    {
        $videoText = KycVerifyText::factory()->create(['text' => 'Before']);

        $originalUpdatedAt = $videoText->updated_at;

        $videoText->update(['text' => 'After']);

        $fresh = $videoText->fresh();

        $this->assertSame('After', $fresh->text);
        $this->assertTrue($fresh->updated_at->greaterThanOrEqualTo($originalUpdatedAt));
    }

    public function test_delete_removes_record_from_database(): void
    {
        $videoText = KycVerifyText::factory()->create();
        $id = $videoText->id;

        $videoText->delete();

        $this->assertDatabaseMissing('kyc_verify_texts', ['id' => $id]);
    }
}
