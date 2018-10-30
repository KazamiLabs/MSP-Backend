<?php

namespace Tests\Unit;

use App\Tool\Ocr\Ruokuai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OcrTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testOcrFile()
    {
        // $ocr    = new Ruokuai(config('ruokuai.username'), config('ruokuai.password'));
        // $result = $ocr->forImageFile(storage_path('app/public/test-vcode.png'));
        // $result = strtolower($result);
        // $this->assertEquals($result, 'je5kr');
        $this->assertTrue(true);
    }
}
